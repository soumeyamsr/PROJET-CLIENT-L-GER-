<?php

function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function validateSiret(string $siret): bool {
    $siret = preg_replace('/\s/', '', $siret);
    if (!preg_match('/^\d{14}$/', $siret)) return false;
    // Algorithme de Luhn pour le SIRET
    $sum = 0;
    for ($i = 0; $i < 14; $i++) {
        $digit = (int)$siret[$i];
        if ($i % 2 === 0) {
            $digit *= 2;
            if ($digit > 9) $digit -= 9;
        }
        $sum += $digit;
    }
    return $sum % 10 === 0;
}

function uploadProductImage(array $file): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > MAX_FILE_SIZE) return null;

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed)) return null;

    $ext      = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime];
    $filename = uniqid('prod_', true) . '.' . $ext;
    $dest     = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $dest)) return $filename;
    return null;
}

function getProductImageUrl(?string $filename): string {
    if (!$filename) return SITE_URL . '/assets/img/product-placeholder.png';
    return UPLOAD_URL . $filename;
}

function formatPrice(float $price): string {
    return number_format($price, 2, ',', ' ') . ' €';
}

function formatDate(string $date): string {
    return date('d/m/Y', strtotime($date));
}

function formatDateTime(string $dt): string {
    return date('d/m/Y H:i', strtotime($dt));
}

function timeRemaining(string $expires): array {
    $diff = max(0, strtotime($expires) - time());
    return [
        'h' => floor($diff / 3600),
        'm' => floor(($diff % 3600) / 60),
        's' => $diff % 60,
        'total' => $diff,
    ];
}

function discountPercent(float $original, float $flash): int {
    if ($original <= 0) return 0;
    return (int)round((1 - $flash / $original) * 100);
}

function addNotification(PDO $pdo, int $userId, string $title, string $message, string $type = 'info', ?string $link = null): void {
    $stmt = $pdo->prepare('INSERT INTO notifications (user_id, title, message, type, link) VALUES (?,?,?,?,?)');
    $stmt->execute([$userId, $title, $message, $type, $link]);
}

function parseUserAgent(?string $ua): string {
    if (!$ua) return 'Appareil inconnu';

    $os = 'OS inconnu';
    if (preg_match('/Windows/i', $ua))        $os = 'Windows';
    elseif (preg_match('/Android/i', $ua))    $os = 'Android';
    elseif (preg_match('/iPhone|iPad/i', $ua)) $os = 'iOS';
    elseif (preg_match('/Mac OS X/i', $ua))   $os = 'macOS';
    elseif (preg_match('/Linux/i', $ua))      $os = 'Linux';

    $browser = 'Navigateur inconnu';
    if (preg_match('/Edg\//i', $ua))         $browser = 'Edge';
    elseif (preg_match('/OPR\/|Opera/i', $ua)) $browser = 'Opera';
    elseif (preg_match('/Chrome\//i', $ua))  $browser = 'Chrome';
    elseif (preg_match('/Firefox\//i', $ua)) $browser = 'Firefox';
    elseif (preg_match('/Safari\//i', $ua))  $browser = 'Safari';

    return "$browser sur $os";
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$PRODUCT_CATEGORIES = [
    'Viandes & Charcuterie',
    'Poissons & Fruits de mer',
    'Légumes & Fruits',
    'Produits laitiers',
    'Épicerie sèche',
    'Boulangerie & Pâtisserie',
    'Boissons',
    'Surgelés',
    'Condiments & Sauces',
    'Autres',
];

$PRODUCT_UNITS = ['kg', 'g', 'L', 'cL', 'unité', 'barquette', 'carton', 'pièce', 'lot'];
