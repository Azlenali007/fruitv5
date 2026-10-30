<?php
/**
 * Product Model
 * N.A Fresh Fruits & Coconuts
 */

class Product {
    public static function getPrimary(): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM `products` WHERE `slug` = 'fresh-coconut-water' LIMIT 1");
        $prod = $stmt->fetch();
        if (!$prod) {
            // Fallback to first product
            $stmt2 = $pdo->query("SELECT * FROM `products` ORDER BY `id` ASC LIMIT 1");
            $prod = $stmt2->fetch();
        }
        if ($prod && !empty($prod['gallery_json'])) {
            $prod['gallery'] = json_decode($prod['gallery_json'], true) ?: [];
        } else if ($prod) {
            $prod['gallery'] = [$prod['image_url']];
        }
        return $prod ?: null;
    }

    public static function getById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $prod = $stmt->fetch();
        if ($prod && !empty($prod['gallery_json'])) {
            $prod['gallery'] = json_decode($prod['gallery_json'], true) ?: [];
        } else if ($prod) {
            $prod['gallery'] = [$prod['image_url']];
        }
        return $prod ?: null;
    }

    public static function getAll(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM `products` ORDER BY `id` ASC");
        $products = $stmt->fetchAll();
        foreach ($products as &$p) {
            $p['gallery'] = !empty($p['gallery_json']) ? json_decode($p['gallery_json'], true) : [$p['image_url']];
        }
        return $products;
    }

    public static function update(int $id, array $data): bool {
        $pdo = Database::getConnection();
        $fields = [];
        $values = [];

        $allowedFields = ['name', 'tagline', 'description', 'price', 'stock_quantity', 'is_available', 'image_url', 'gallery_json'];
        foreach ($allowedFields as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "`$f` = ?";
                $values[] = $data[$f];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE `products` SET " . implode(', ', $fields) . ", `updated_at` = NOW() WHERE `id` = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($values);
    }
}
