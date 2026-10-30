<?php
/**
 * Admin Delivery Area Create / Edit Form with Interactive Leaflet Polygon Editor
 * N.A Fresh Fruits & Coconuts
 */
$isEdit = !empty($area['id']);
$adminTitle = ($isEdit ? 'Edit Area: ' . e($area['name']) : 'Add Delivery Area') . ' - N.A Fresh Fruits & Coconuts';
$activeAdminTab = 'areas';
require_once __DIR__ . '/layout-header.php';

$actionUrl = $isEdit ? '/admin/areas/update' : '/admin/areas/store';
$initialGeoJson = !empty($area['boundary_geojson']) ? $area['boundary_geojson'] : '';
?>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    #areaMap {
        height: 480px;
        width: 100%;
        border-radius: 1rem;
        z-index: 10;
    }
    .leaflet-container {
        font-family: inherit;
    }
</style>

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header with Back Link -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="/admin/areas" class="p-2 text-gray-500 hover:text-gray-900 bg-white rounded-xl border border-gray-200 transition" aria-label="Back to areas">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">
                    <?= $isEdit ? 'Edit Delivery Area & Boundary' : 'Add New Delivery Area' ?>
                </h1>
                <p class="text-xs text-gray-500">Center: Jaora, Madhya Pradesh (23.8924, 75.1325)</p>
            </div>
        </div>
    </div>

    <form action="<?= $actionUrl ?>" method="POST" id="areaForm" class="space-y-6">
        <input type="hidden" name="<?= CSRF_TOKEN_KEY ?>" value="<?= csrf_token() ?>">
        <?php if ($isEdit): ?>
        <input type="hidden" name="area_id" value="<?= $area['id'] ?>">
        <?php endif; ?>

        <!-- Basic Details Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-5">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Locality Details & Charges</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Area Name -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        Locality / Area Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        value="<?= e($area['name'] ?? '') ?>" 
                        placeholder="e.g. Shastri Colony, Station Road, Topkhana" 
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-xs text-gray-900 font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none"
                    >
                    <span class="text-[10px] text-gray-400 mt-1 block">Displayed to customers at checkout and on order invoices</span>
                </div>

                <!-- Delivery Charge -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                        Delivery Charge (₹) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 font-bold text-gray-400 text-xs">₹</span>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="delivery_charge" 
                            required 
                            min="0"
                            value="<?= number_format((float)($area['delivery_charge'] ?? 40.00), 2, '.', '') ?>" 
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-8 pr-3 py-2.5 text-xs text-gray-900 font-bold focus:ring-2 focus:ring-brand-500 focus:outline-none"
                        >
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">Fixed fee charged for this specific delivery zone</span>
                </div>
            </div>

            <!-- Status Toggle -->
            <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-900 block">Area Activation Status</span>
                    <span class="text-[11px] text-gray-500">When active, customers in this zone can place delivery orders.</span>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="status" 
                        value="active" 
                        <?= ($area['status'] ?? 'inactive') === 'active' ? 'checked' : '' ?> 
                        class="w-4 h-4 text-brand-600 rounded-sm focus:ring-brand-500"
                    >
                    <span class="text-xs font-bold text-gray-700">Active for Customer Delivery</span>
                </label>
            </div>
        </div>

        <!-- Interactive Map Boundary Editor Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <span>Interactive Delivery Boundary Editor</span>
                        <span id="pointCountBadge" class="text-[10px] bg-brand-50 text-brand-700 font-bold px-2 py-0.5 rounded-full border border-brand-200">0 Points</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Click on the map to place polygon boundary points enclosing this delivery zone</p>
                </div>

                <!-- Action Controls -->
                <div class="flex flex-wrap items-center gap-2">
                    <button 
                        type="button" 
                        id="btnPresetCircle"
                        class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold py-2 px-3 rounded-xl transition flex items-center gap-1.5"
                    >
                        <span>⭕ Circle Preset (1 km)</span>
                    </button>
                    <button 
                        type="button" 
                        id="btnClearPoints" 
                        class="bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold py-2 px-3 rounded-xl transition flex items-center gap-1.5"
                    >
                        <span>🗑️ Reset Polygon</span>
                    </button>
                </div>
            </div>

            <!-- Hint instructions bar -->
            <div class="bg-emerald-50/70 border border-emerald-200/60 rounded-2xl p-3 text-xs text-emerald-900 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="text-[11px] leading-relaxed">
                    <strong>How to draw:</strong> Click anywhere on the map around the locality in Jaora to add vertex points. Click at least 3 points to create a coverage polygon. You can drag markers or use the quick circle preset.
                </div>
            </div>

            <!-- Leaflet Map Container -->
            <div id="areaMap" class="shadow-inner border border-gray-200"></div>

            <!-- Hidden input for raw GeoJSON coordinates -->
            <input type="hidden" name="boundary_geojson" id="boundaryGeoJsonInput" value="<?= e($initialGeoJson) ?>">

            <!-- Real-time coordinate details drawer (collapsible) -->
            <div class="pt-2 text-[11px] text-gray-500 flex items-center justify-between">
                <span id="statusCoordinates">Boundary: Not set yet</span>
                <span class="text-gray-400 font-mono">Center: 23.8924° N, 75.1325° E</span>
            </div>
        </div>

        <!-- Submit & Cancel Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="/admin/areas" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold py-3 px-5 rounded-xl transition">
                Cancel
            </a>
            <button 
                type="submit" 
                class="bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold py-3 px-6 rounded-xl shadow-md transition"
            >
                <?= $isEdit ? 'Save Changes & Boundary' : 'Create Delivery Area' ?>
            </button>
        </div>
    </form>

</div>

<script>
(function() {
    // Default Map Center: Jaora, Madhya Pradesh
    var JAORA_LAT = 23.8924;
    var JAORA_LNG = 75.1325;

    var map = L.map('areaMap').setView([JAORA_LAT, JAORA_LNG], 14);

    // High quality OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var polygonLayer = null;
    var markersLayer = L.layerGroup().addTo(map);
    var points = []; // Array of [lat, lng]

    var geojsonInput = document.getElementById('boundaryGeoJsonInput');
    var pointCountBadge = document.getElementById('pointCountBadge');
    var statusCoordinates = document.getElementById('statusCoordinates');

    function updateGeoJsonInput() {
        if (points.length < 3) {
            geojsonInput.value = '';
            pointCountBadge.innerText = points.length + ' Points (Min 3)';
            pointCountBadge.className = 'text-[10px] bg-amber-50 text-amber-700 font-bold px-2 py-0.5 rounded-full border border-amber-200';
            statusCoordinates.innerText = 'Boundary: Minimum 3 points required (currently ' + points.length + ')';
            return;
        }

        // Create standard GeoJSON Polygon format: [[lng, lat], ...] closed ring
        var coordinatesRing = points.map(function(pt) {
            return [parseFloat(pt[1].toFixed(6)), parseFloat(pt[0].toFixed(6))];
        });
        // Ensure ring is closed
        if (coordinatesRing.length > 0) {
            coordinatesRing.push(coordinatesRing[0]);
        }

        var geojsonObj = {
            type: "Polygon",
            coordinates: [coordinatesRing]
        };

        geojsonInput.value = JSON.stringify(geojsonObj);
        pointCountBadge.innerText = points.length + ' Polygon Vertices';
        pointCountBadge.className = 'text-[10px] bg-emerald-50 text-emerald-700 font-bold px-2 py-0.5 rounded-full border border-emerald-200';
        statusCoordinates.innerText = 'Boundary: Valid ' + points.length + '-point polygon saved in GeoJSON';
    }

    function redrawPolygon() {
        markersLayer.clearLayers();

        if (polygonLayer) {
            map.removeLayer(polygonLayer);
            polygonLayer = null;
        }

        if (points.length > 0) {
            points.forEach(function(pt, idx) {
                var marker = L.circleMarker(pt, {
                    radius: 6,
                    fillColor: '#235d3a',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(markersLayer);

                marker.bindTooltip('Point ' + (idx + 1), { permanent: false, direction: 'top' });
            });
        }

        if (points.length >= 3) {
            polygonLayer = L.polygon(points, {
                color: '#235d3a',
                fillColor: '#235d3a',
                fillOpacity: 0.25,
                weight: 2.5
            }).addTo(map);
        }

        updateGeoJsonInput();
    }

    // Map click handler to add points
    map.on('click', function(e) {
        points.push([e.latlng.lat, e.latlng.lng]);
        redrawPolygon();
    });

    // Reset button
    document.getElementById('btnClearPoints').addEventListener('click', function() {
        if (confirm('Clear all polygon boundary points for this area?')) {
            points = [];
            redrawPolygon();
        }
    });

    // Circular preset generator around Jaora center or first point
    document.getElementById('btnPresetCircle').addEventListener('click', function() {
        var centerLat = points.length > 0 ? points[0][0] : JAORA_LAT;
        var centerLng = points.length > 0 ? points[0][1] : JAORA_LNG;
        var radiusKm = 1.0;
        var numPoints = 12;
        var newPoints = [];

        var dLat = radiusKm / 111.32;
        var dLng = radiusKm / (111.32 * Math.cos(centerLat * (Math.PI / 180)));

        for (var i = 0; i < numPoints; i++) {
            var angle = (i / numPoints) * 2 * Math.PI;
            var pLat = centerLat + (dLat * Math.sin(angle));
            var pLng = centerLng + (dLng * Math.cos(angle));
            newPoints.push([pLat, pLng]);
        }

        points = newPoints;
        redrawPolygon();
        if (polygonLayer) {
            map.fitBounds(polygonLayer.getBounds(), { padding: [20, 20] });
        }
    });

    // Load initial boundary if available
    var initialVal = geojsonInput.value.trim();
    if (initialVal) {
        try {
            var parsed = JSON.parse(initialVal);
            if (parsed && parsed.coordinates && parsed.coordinates[0]) {
                var ring = parsed.coordinates[0];
                // Strip duplicate closing vertex if present
                if (ring.length > 3 && ring[0][0] === ring[ring.length - 1][0] && ring[0][1] === ring[ring.length - 1][1]) {
                    ring = ring.slice(0, ring.length - 1);
                }
                points = ring.map(function(pt) {
                    return [pt[1], pt[0]]; // convert [lng, lat] to [lat, lng]
                });
                redrawPolygon();
                if (polygonLayer) {
                    map.fitBounds(polygonLayer.getBounds(), { padding: [30, 30] });
                }
            }
        } catch (err) {
            console.error('Failed to parse initial GeoJSON', err);
        }
    }
})();
</script>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
