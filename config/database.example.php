<?php
// Copiez ce fichier en database.php et adaptez les valeurs
define('DB_HOST',       'localhost');
define('DB_PORT',       '3306');
define('DB_USER',       'root');
define('DB_PASS',       '');
define('DB_NAME',       'rushify_db');
define('SITE_URL',      'http://localhost:8888');
define('UPLOAD_DIR',    __DIR__ . '/../uploads/products/');
define('UPLOAD_URL',    SITE_URL . '/uploads/products/');
define('AI_SERVICE',    'http://localhost:8080/api/recognize');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('RUSHIFY DB Error: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Service temporairement indisponible']));
}
