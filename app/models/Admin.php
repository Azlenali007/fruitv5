<?php
/**
 * Admin Model & Auth Verification
 * N.A Fresh Fruits & Coconuts
 */

class Admin {
    public static function findByUsername(string $username): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `admins` WHERE `username` = ? LIMIT 1");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM `admins` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function isIpThrottled(string $ip): bool {
        $pdo = Database::getConnection();
        // Allow max 5 failed attempts per IP in the last 15 minutes
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM `login_attempts` 
            WHERE `ip_address` = ? AND `success` = 0 AND `attempted_at` > (NOW() - INTERVAL 15 MINUTE)
        ");
        $stmt->execute([$ip]);
        return (int)$stmt->fetchColumn() >= 5;
    }

    public static function clearLoginAttempts(string $ip): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM `login_attempts` WHERE `ip_address` = ?");
        $stmt->execute([$ip]);
    }

    public static function recordLoginAttempt(string $ip, bool $success): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO `login_attempts` (`ip_address`, `success`, `attempted_at`) VALUES (?, ?, NOW())");
        $stmt->execute([$ip, $success ? 1 : 0]);
    }

    public static function updateLastLogin(int $id): void {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE `admins` SET `last_login_at` = NOW() WHERE `id` = ?");
        $stmt->execute([$id]);
    }

    public static function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE `admins` SET `password_hash` = ?, `updated_at` = NOW() WHERE `id` = ?");
        return $stmt->execute([$hash, $id]);
    }
}
