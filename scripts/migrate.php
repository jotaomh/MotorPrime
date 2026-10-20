<?php

require_once __DIR__ . '/../app/bootstrap.php';

$db = db()->getConnection();

$db->exec("CREATE TABLE IF NOT EXISTS schema_migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$dir = __DIR__ . '/../database/migrations';
if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
    echo "Diretório de migrations criado.\n";
    exit;
}

$files = glob($dir . '/*.sql');
sort($files);

foreach ($files as $file) {
    $name = basename($file);
    $stmt = $db->prepare("SELECT id FROM schema_migrations WHERE migration = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        echo "Já aplicada: $name\n";
        continue;
    }
    $sql = file_get_contents($file);
    try {
        $db->exec($sql);
        $stmt = $db->prepare("INSERT INTO schema_migrations (migration) VALUES (?)");
        $stmt->execute([$name]);
        echo "Aplicada: $name\n";
    } catch (Exception $e) {
        echo "Erro ao aplicar $name: " . $e->getMessage() . "\n";
        exit(1);
    }
}
