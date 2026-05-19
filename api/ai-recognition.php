<?php
/**
 * RUSHIFY – Endpoint de reconnaissance IA des produits alimentaires
 *
 * Stratégie :
 *  1. Tente d'appeler le microservice Java (Spring Boot) sur localhost:8080
 *  2. En cas d'échec, utilise le moteur de fallback PHP intégré
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Méthode non autorisée.'], 405);
}
if (!isLoggedIn()) {
    jsonResponse(['error' => 'Authentification requise.'], 401);
}
if (empty($_FILES['image'])) {
    jsonResponse(['error' => 'Aucune image fournie.'], 400);
}

$file = $_FILES['image'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(['error' => 'Erreur lors de l\'upload.'], 400);
}

$finfo   = new finfo(FILEINFO_MIME_TYPE);
$mime    = $finfo->file($file['tmp_name']);
$allowed = ['image/jpeg','image/png','image/webp'];
if (!in_array($mime, $allowed)) {
    jsonResponse(['error' => 'Format non supporté.'], 415);
}

// ── 1. Tentative microservice Java ──────────────────────────
$javaSuggestions = callJavaService($file['tmp_name'], $mime);
if ($javaSuggestions !== null) {
    jsonResponse(['source' => 'java-ai', 'suggestions' => $javaSuggestions]);
}

// ── 2. Fallback PHP ──────────────────────────────────────────
$suggestions = phpFallbackRecognition($file['tmp_name'], $mime, $file['name']);
jsonResponse(['source' => 'php-fallback', 'suggestions' => $suggestions]);


/* ─────────────────────────────────────────────────────────── */

function callJavaService(string $path, string $mime): ?array {
    if (!defined('AI_SERVICE') || !filter_var(AI_SERVICE, FILTER_VALIDATE_URL)) return null;
    try {
        $boundary = '----RushifyBoundary' . uniqid();
        $body     = "--{$boundary}\r\n"
                  . "Content-Disposition: form-data; name=\"image\"; filename=\"product.jpg\"\r\n"
                  . "Content-Type: {$mime}\r\n\r\n"
                  . file_get_contents($path) . "\r\n"
                  . "--{$boundary}--\r\n";
        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: multipart/form-data; boundary={$boundary}\r\nContent-Length: " . strlen($body),
            'content' => $body,
            'timeout' => 4,
            'ignore_errors' => true,
        ]]);
        $response = @file_get_contents(AI_SERVICE, false, $ctx);
        if ($response === false) return null;
        $data = json_decode($response, true);
        return is_array($data['suggestions'] ?? null) ? $data['suggestions'] : null;
    } catch (Throwable $e) {
        return null;
    }
}

function phpFallbackRecognition(string $path, string $mime, string $filename): array {
    // Analyse de couleur dominante via GD
    $colorLabel = analyzeImageColor($path, $mime);
    // Analyse du nom de fichier
    $nameHints  = extractNameHints(strtolower($filename));
    // Combine les deux signaux
    return buildSuggestions($colorLabel, $nameHints);
}

function analyzeImageColor(string $path, string $mime): string {
    try {
        $img = match($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png'  => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default      => null
        };
        if (!$img) return 'unknown';
        // Redimensionne à 10×10 pour avoir la couleur dominante
        $thumb = imagecreatetruecolor(10, 10);
        imagecopyresampled($thumb, $img, 0,0,0,0, 10,10, imagesx($img),imagesy($img));
        imagedestroy($img);
        $totalR = $totalG = $totalB = 0;
        for ($x=0;$x<10;$x++) for ($y=0;$y<10;$y++) {
            $rgb     = imagecolorat($thumb, $x, $y);
            $totalR += ($rgb >> 16) & 0xFF;
            $totalG += ($rgb >> 8)  & 0xFF;
            $totalB +=  $rgb        & 0xFF;
        }
        imagedestroy($thumb);
        $r = $totalR/100; $g = $totalG/100; $b = $totalB/100;
        // Règles de couleur heuristiques
        if ($r > 150 && $g < 100 && $b < 100) return 'red';     // viande crue
        if ($r > 200 && $g > 180 && $b < 80)  return 'yellow';  // fromage/pâtes
        if ($g > $r && $g > $b && $g > 90)    return 'green';   // légumes verts
        if ($r > 180 && $g > 160 && $b > 140) return 'beige';   // boulangerie
        if ($b > $r && $b > $g && $b > 120)   return 'blue';    // emballage
        if ($r > 160 && $g > 120 && $b < 80)  return 'orange';  // carotte, agrumes
        return 'neutral';
    } catch (Throwable $e) {
        return 'unknown';
    }
}

function extractNameHints(string $name): array {
    $keywords = [
        'boeuf'|'veau'|'agneau'|'poulet'|'canard'|'porc'|'viande'|'steak'|'filet'|'côte' => 'meat',
        'saumon'|'thon'|'dorade'|'loup'|'cabillaud'|'crevette'|'poisson'|'fruits de mer'  => 'fish',
        'tomate'|'carotte'|'salade'|'courgette'|'aubergine'|'oignon'|'légume'|'poivron'   => 'veg',
        'pomme'|'poire'|'fraise'|'orange'|'citron'|'fruit'|'mangue'|'avocat'             => 'fruit',
        'fromage'|'yaourt'|'lait'|'crème'|'beurre'|'mascarpone'                          => 'dairy',
        'pain'|'baguette'|'brioche'|'croissant'|'viennoiserie'|'gâteau'|'tarte'          => 'bakery',
        'riz'|'pâtes'|'farine'|'sucre'|'huile'|'vinaigre'|'sel'                         => 'grocery',
    ];
    // Simple substring matching
    $hints = [];
    $map = [
        ['boeuf','veau','agneau','poulet','canard','porc','viande','steak','filet'] => 'meat',
        ['saumon','thon','dorade','loup','cabillaud','crevette','poisson']          => 'fish',
        ['tomate','carotte','salade','courgette','aubergine','oignon','légume']     => 'veg',
        ['pomme','poire','fraise','orange','citron','fruit','mangue']               => 'fruit',
        ['fromage','yaourt','lait','crème','beurre']                               => 'dairy',
        ['pain','baguette','brioche','croissant','gâteau','tarte']                 => 'bakery',
        ['riz','pâtes','farine','sucre','huile']                                   => 'grocery',
    ];
    foreach ($map as $words => $type) {
        foreach ((array)$words as $w) {
            if (str_contains($name, $w)) { $hints[] = $type; break; }
        }
    }
    return array_unique($hints);
}

function buildSuggestions(string $colorLabel, array $nameHints): array {
    $db = [
        'meat'    => ['name'=>'Viande fraîche',    'category'=>'Viandes & Charcuterie',       'unit'=>'kg', 'description'=>'Viande fraîche de qualité professionnelle.'],
        'fish'    => ['name'=>'Poisson frais',     'category'=>'Poissons & Fruits de mer',    'unit'=>'kg', 'description'=>'Produit de la mer, pêche du jour.'],
        'veg'     => ['name'=>'Légumes frais',     'category'=>'Légumes & Fruits',             'unit'=>'kg', 'description'=>'Légumes frais de saison, origine locale.'],
        'fruit'   => ['name'=>'Fruits frais',      'category'=>'Légumes & Fruits',             'unit'=>'kg', 'description'=>'Fruits frais de saison.'],
        'dairy'   => ['name'=>'Produit laitier',   'category'=>'Produits laitiers',            'unit'=>'kg', 'description'=>'Produit laitier frais, qualité premium.'],
        'bakery'  => ['name'=>'Boulangerie',       'category'=>'Boulangerie & Pâtisserie',     'unit'=>'pièce','description'=>'Produit de boulangerie artisanal, fait maison.'],
        'grocery' => ['name'=>'Épicerie',          'category'=>'Épicerie sèche',               'unit'=>'kg', 'description'=>'Produit d\'épicerie de qualité.'],
    ];
    $colorMap = [
        'red'    => ['meat'],
        'green'  => ['veg'],
        'orange' => ['veg','fruit'],
        'yellow' => ['dairy','grocery'],
        'beige'  => ['bakery'],
        'blue'   => ['grocery'],
    ];

    $types = array_unique(array_merge($nameHints, $colorMap[$colorLabel] ?? []));
    if (empty($types)) $types = ['grocery','veg','meat'];

    $suggestions = [];
    foreach (array_slice($types, 0, 3) as $i => $type) {
        if (!isset($db[$type])) continue;
        $suggestions[] = array_merge($db[$type], ['confidence' => round(0.85 - $i * 0.1, 2)]);
    }
    return $suggestions ?: [['name'=>'Produit alimentaire','category'=>'Autres','unit'=>'kg','description'=>'','confidence'=>0.5]];
}
