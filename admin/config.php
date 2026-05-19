<?php
session_start();

require_once __DIR__ . '/../config/database.php';

define('ADMIN_VERSION', '1.0');

// ── Authentification admin ────────────────────────────────────
function adminIsLoggedIn(): bool {
    return !empty($_SESSION['admin_id']);
}

function adminRequireLogin(): void {
    if (!adminIsLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function adminGetCurrent(): ?array {
    if (!adminIsLoggedIn()) return null;
    global $pdo;
    $stmt = $pdo->prepare('SELECT au.*, ar.name AS role_name FROM admin_users au JOIN admin_roles ar ON au.role_id=ar.id WHERE au.id=? LIMIT 1');
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ?: null;
}

function adminLog(string $action, string $resource, string $desc = '', ?string $resourceId = null): void {
    if (!adminIsLoggedIn()) return;
    global $pdo;
    try {
        $pdo->prepare('INSERT INTO admin_audit_log (admin_id,action,resource,resource_id,description,ip_address) VALUES (?,?,?,?,?,?)')
            ->execute([$_SESSION['admin_id'], $action, $resource, $resourceId, $desc, $_SERVER['REMOTE_ADDR'] ?? '']);
    } catch(Exception $e) {}
}

// ── Stats dashboard ───────────────────────────────────────────
function getDashboardStats(): array {
    global $pdo;
    $row = $pdo->query('SELECT * FROM vw_dashboard_stats')->fetch();
    return $row ?: [];
}
