<?php
/**
 * Area Service
 * N.A Fresh Fruits & Coconuts
 * Handles delivery area polygon boundary parsing, ray-casting point-in-polygon
 * spatial detection, active area lookups, and delivery charge calculations.
 */

class AreaService {
    /**
     * Parse stored boundary GeoJSON or JSON points into normalized array of [lat, lng] points
     *
     * @param string|null $rawJson
     * @return array<array{lat: float, lng: float}>|null
     */
    public static function parsePolygonPoints(?string $rawJson): ?array {
        if (empty($rawJson)) {
            return null;
        }

        $decoded = json_decode($rawJson, true);
        if (!is_array($decoded)) {
            return null;
        }

        // 1. Standard GeoJSON FeatureCollection or Feature
        if (isset($decoded['type']) && $decoded['type'] === 'FeatureCollection' && !empty($decoded['features'][0]['geometry'])) {
            $decoded = $decoded['features'][0]['geometry'];
        } else if (isset($decoded['type']) && $decoded['type'] === 'Feature' && !empty($decoded['geometry'])) {
            $decoded = $decoded['geometry'];
        }

        // 2. Standard GeoJSON Polygon: coordinates = [ [ [lng, lat], [lng, lat], ... ] ]
        if (isset($decoded['type']) && strtolower($decoded['type']) === 'polygon' && !empty($decoded['coordinates'][0])) {
            $ring = $decoded['coordinates'][0];
            $points = [];
            foreach ($ring as $pt) {
                if (is_array($pt) && count($pt) >= 2) {
                    // GeoJSON format is [longitude, latitude]
                    $points[] = [
                        'lat' => (float)$pt[1],
                        'lng' => (float)$pt[0]
                    ];
                }
            }
            return count($points) >= 3 ? $points : null;
        }

        // 3. MultiPolygon: use primary outer ring of first polygon
        if (isset($decoded['type']) && strtolower($decoded['type']) === 'multipolygon' && !empty($decoded['coordinates'][0][0])) {
            $ring = $decoded['coordinates'][0][0];
            $points = [];
            foreach ($ring as $pt) {
                if (is_array($pt) && count($pt) >= 2) {
                    $points[] = [
                        'lat' => (float)$pt[1],
                        'lng' => (float)$pt[0]
                    ];
                }
            }
            return count($points) >= 3 ? $points : null;
        }

        // 4. Raw array of objects: [ {"lat": 23.89, "lng": 75.13}, ... ]
        if (isset($decoded[0]['lat']) && isset($decoded[0]['lng'])) {
            $points = [];
            foreach ($decoded as $pt) {
                $points[] = [
                    'lat' => (float)$pt['lat'],
                    'lng' => (float)$pt['lng']
                ];
            }
            return count($points) >= 3 ? $points : null;
        }

        // 5. Raw array of coordinate pairs: [ [23.89, 75.13], ... ]
        if (isset($decoded[0]) && is_array($decoded[0]) && count($decoded[0]) >= 2) {
            $points = [];
            foreach ($decoded as $pt) {
                // Determine whether [lat, lng] or [lng, lat].
                // In India, lat is ~8-37 and lng is ~68-97.
                $v0 = (float)$pt[0];
                $v1 = (float)$pt[1];
                if ($v0 > 60 && $v1 < 40) {
                    // [lng, lat]
                    $points[] = ['lat' => $v1, 'lng' => $v0];
                } else {
                    // [lat, lng]
                    $points[] = ['lat' => $v0, 'lng' => $v1];
                }
            }
            return count($points) >= 3 ? $points : null;
        }

        return null;
    }

    /**
     * Ray-casting Point-In-Polygon Algorithm
     *
     * @param float $lat
     * @param float $lng
     * @param array<array{lat: float, lng: float}> $polygonPoints
     * @return bool
     */
    public static function isPointInPolygon(float $lat, float $lng, array $polygonPoints): bool {
        $count = count($polygonPoints);
        if ($count < 3) {
            return false;
        }

        $inside = false;
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $xi = $polygonPoints[$i]['lat'];
            $yi = $polygonPoints[$i]['lng'];
            $xj = $polygonPoints[$j]['lat'];
            $yj = $polygonPoints[$j]['lng'];

            $denom = ($yj - $yi);
            if (abs($denom) < 0.000000001) {
                $denom = ($denom < 0 ? -0.000000001 : 0.000000001);
            }

            $intersect = (($yi > $lng) !== ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / $denom + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Find active area containing given coordinates
     * Handles potential boundary overlaps by choosing the smallest enclosing boundary
     *
     * @param float $lat
     * @param float $lng
     * @return array|null
     */
    public static function findAreaForCoordinates(float $lat, float $lng): ?array {
        $activeAreas = DeliveryArea::getActiveAreas();
        $matches = [];

        foreach ($activeAreas as $area) {
            if (empty($area['boundary_geojson'])) {
                continue;
            }

            $points = self::parsePolygonPoints($area['boundary_geojson']);
            if (!$points) {
                continue;
            }

            if (self::isPointInPolygon($lat, $lng, $points)) {
                // Calculate bounding box area as a tie-breaker (more specific area wins)
                $lats = array_column($points, 'lat');
                $lngs = array_column($points, 'lng');
                $bboxArea = (max($lats) - min($lats)) * (max($lngs) - min($lngs));
                
                $matches[] = [
                    'area' => $area,
                    'bbox_size' => $bboxArea
                ];
            }
        }

        if (empty($matches)) {
            return null;
        }

        // Sort by smallest bounding box first
        usort($matches, function ($a, $b) {
            return $a['bbox_size'] <=> $b['bbox_size'];
        });

        return $matches[0]['area'];
    }

    /**
     * Compute delivery charge and verify service availability for given location or manual area fallback
     *
     * @param float|null $lat
     * @param float|null $lng
     * @param int|null $areaId
     * @return array
     */
    public static function checkDeliveryEligibility(?float $lat, ?float $lng, ?int $areaId = null): array {
        $defaultCharge = (float)Setting::get('delivery_charge', '40.00');

        // Check if any active areas exist
        $activeAreas = DeliveryArea::getActiveAreas();
        if (empty($activeAreas)) {
            // When no active areas exist or configured yet, fall back to global delivery setting
            return [
                'deliverable' => true,
                'area_id' => null,
                'area_name' => 'General Delivery Service',
                'delivery_charge' => $defaultCharge,
                'message' => 'Delivery is available at standard rate.'
            ];
        }

        // 1. If GPS coordinates provided, perform spatial matching
        if ($lat !== null && $lng !== null && $lat != 0 && $lng != 0) {
            $matchedArea = self::findAreaForCoordinates($lat, $lng);
            if ($matchedArea) {
                return [
                    'deliverable' => true,
                    'area_id' => (int)$matchedArea['id'],
                    'area_name' => $matchedArea['name'],
                    'delivery_charge' => (float)$matchedArea['delivery_charge'],
                    'message' => 'Delivery available in ' . $matchedArea['name'] . '!'
                ];
            } else {
                return [
                    'deliverable' => false,
                    'area_id' => null,
                    'area_name' => null,
                    'delivery_charge' => $defaultCharge,
                    'message' => 'Sorry, delivery is not available at your location.'
                ];
            }
        }

        // 2. Fallback: manual active area selection
        if ($areaId) {
            $area = DeliveryArea::getById($areaId);
            if ($area && $area['status'] === 'active') {
                return [
                    'deliverable' => true,
                    'area_id' => (int)$area['id'],
                    'area_name' => $area['name'],
                    'delivery_charge' => (float)$area['delivery_charge'],
                    'message' => 'Delivery available in ' . $area['name'] . '!'
                ];
            } else {
                return [
                    'deliverable' => false,
                    'area_id' => null,
                    'area_name' => null,
                    'delivery_charge' => $defaultCharge,
                    'message' => 'The selected delivery locality is currently inactive or not available.'
                ];
            }
        }

        return [
            'deliverable' => false,
            'area_id' => null,
            'area_name' => null,
            'delivery_charge' => $defaultCharge,
            'message' => 'Please select your location on the map or choose your locality from the list.'
        ];
    }
}
