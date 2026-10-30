<?php
/**
 * Setting Model
 * N.A Fresh Fruits & Coconuts
 */

class Setting {
    private static ?array $cache = null;

    public static function getAll(): array {
        if (self::$cache === null) {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT `key_name`, `value` FROM `settings`");
            $rows = $stmt->fetchAll();
            self::$cache = [];
            foreach ($rows as $r) {
                self::$cache[$r['key_name']] = $r['value'];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, ?string $default = null): ?string {
        $all = self::getAll();
        return $all[$key] ?? $default;
    }

    public static function set(string $key, string $value, ?string $description = null): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO `settings` (`key_name`, `value`, `description`) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()
        ");
        $res = $stmt->execute([$key, $value, $description]);
        if (self::$cache !== null) {
            self::$cache[$key] = $value;
        }
        return $res;
    }

    public static function setMany(array $settings): bool {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO `settings` (`key_name`, `value`) 
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()
            ");
            foreach ($settings as $k => $v) {
                $stmt->execute([$k, (string)$v]);
            }
            $pdo->commit();
            self::$cache = null; // Clear cache
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Failed to save settings: " . $e->getMessage());
            return false;
        }
    }
}
