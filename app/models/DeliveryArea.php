<?php
/**
 * DeliveryArea Model
 * N.A Fresh Fruits & Coconuts
 */

class DeliveryArea {
    public static function getAll(?string $status = null, string $search = ''): array {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM `delivery_areas` WHERE 1=1";
        $params = [];

        if ($status !== null && $status !== 'all' && $status !== '') {
            $sql .= " AND `status` = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND `name` LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $sql .= " ORDER BY `name` ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getActiveAreas(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `delivery_areas` WHERE `status` = 'active' ORDER BY `name` ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `delivery_areas` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function getBySlug(string $slug): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `delivery_areas` WHERE `slug` = ? LIMIT 1");
        $stmt->execute([$slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function generateSlug(string $name, ?int $ignoreId = null): string {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        if (empty($base)) {
            $base = 'area-' . time();
        }
        $slug = $base;
        $counter = 1;
        $pdo = Database::getConnection();

        while (true) {
            $sql = "SELECT COUNT(*) FROM `delivery_areas` WHERE `slug` = ?";
            $params = [$slug];
            if ($ignoreId !== null) {
                $sql .= " AND `id` != ?";
                $params[] = $ignoreId;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if ((int)$stmt->fetchColumn() === 0) {
                break;
            }
            $slug = $base . '-' . (++$counter);
        }

        return $slug;
    }

    public static function create(array $data): array {
        $name = trim($data['name'] ?? '');
        if (empty($name) || mb_strlen($name) < 2) {
            return ['success' => false, 'error' => 'Area name is required (minimum 2 characters).'];
        }

        $charge = isset($data['delivery_charge']) ? max(0.0, (float)$data['delivery_charge']) : 40.0;
        $status = in_array($data['status'] ?? '', ['active', 'inactive'], true) ? $data['status'] : 'inactive';
        $boundary = !empty($data['boundary_geojson']) ? trim($data['boundary_geojson']) : null;

        // If boundary string provided, validate it's valid JSON
        if ($boundary !== null) {
            $decoded = json_decode($boundary, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                return ['success' => false, 'error' => 'Invalid boundary GeoJSON format.'];
            }
        }

        $slug = self::generateSlug($name);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO `delivery_areas` (`name`, `slug`, `delivery_charge`, `status`, `boundary_geojson`, `created_at`, `updated_at`)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$name, $slug, $charge, $status, $boundary]);
        $newId = (int)$pdo->lastInsertId();

        return ['success' => true, 'id' => $newId, 'slug' => $slug];
    }

    public static function update(int $id, array $data): array {
        $existing = self::getById($id);
        if (!$existing) {
            return ['success' => false, 'error' => 'Delivery area not found.'];
        }

        $name = trim($data['name'] ?? $existing['name']);
        if (empty($name) || mb_strlen($name) < 2) {
            return ['success' => false, 'error' => 'Area name is required.'];
        }

        $charge = isset($data['delivery_charge']) ? max(0.0, (float)$data['delivery_charge']) : (float)$existing['delivery_charge'];
        $status = in_array($data['status'] ?? '', ['active', 'inactive'], true) ? $data['status'] : $existing['status'];
        
        $boundary = array_key_exists('boundary_geojson', $data) 
            ? (!empty(trim((string)$data['boundary_geojson'])) ? trim((string)$data['boundary_geojson']) : null)
            : $existing['boundary_geojson'];

        if ($boundary !== null) {
            $decoded = json_decode($boundary, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                return ['success' => false, 'error' => 'Invalid boundary GeoJSON format.'];
            }
        }

        $slug = self::generateSlug($name, $id);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE `delivery_areas` 
            SET `name` = ?, `slug` = ?, `delivery_charge` = ?, `status` = ?, `boundary_geojson` = ?, `updated_at` = NOW()
            WHERE `id` = ?
        ");
        $stmt->execute([$name, $slug, $charge, $status, $boundary, $id]);

        return ['success' => true, 'id' => $id, 'slug' => $slug];
    }

    public static function toggleStatus(int $id): bool {
        $existing = self::getById($id);
        if (!$existing) {
            return false;
        }
        $newStatus = $existing['status'] === 'active' ? 'inactive' : 'active';
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE `delivery_areas` SET `status` = ?, `updated_at` = NOW() WHERE `id` = ?");
        return $stmt->execute([$newStatus, $id]);
    }

    public static function delete(int $id): array {
        $pdo = Database::getConnection();
        // Check if any orders have been placed in this area
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `orders` WHERE `delivery_area_id` = ?");
        $stmtCheck->execute([$id]);
        $ordersCount = (int)$stmtCheck->fetchColumn();

        if ($ordersCount > 0) {
            // Safe deactivation: do not destroy order history referential records
            $stmtDeactivate = $pdo->prepare("UPDATE `delivery_areas` SET `status` = 'inactive', `updated_at` = NOW() WHERE `id` = ?");
            $stmtDeactivate->execute([$id]);
            return [
                'success' => true,
                'action' => 'deactivated',
                'message' => "Area has {$ordersCount} existing order(s) associated with it, so it was safely deactivated instead of deleted to protect order history."
            ];
        }

        $stmtDel = $pdo->prepare("DELETE FROM `delivery_areas` WHERE `id` = ?");
        $stmtDel->execute([$id]);
        return [
            'success' => true,
            'action' => 'deleted',
            'message' => 'Delivery area successfully deleted.'
        ];
    }
}
