<?php

/**
 * Create Missing Spatie Permission Tables
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$databases = ['kpkb3', 'tenant_nknapijed', 'tenant_nkprigorjem'];

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach ($databases as $db) {
        echo "Verarbeite Datenbank: $db\n";

        // Erstelle model_has_permissions
        $sql = "CREATE TABLE IF NOT EXISTS `$db`.`model_has_permissions` (
            `permission_id` bigint unsigned NOT NULL,
            `model_type` varchar(255) NOT NULL,
            `model_id` bigint unsigned NOT NULL,
            PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
            KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $pdo->exec($sql);
        echo "  ✓ model_has_permissions erstellt/existiert\n";

        // Erstelle model_has_roles
        $sql = "CREATE TABLE IF NOT EXISTS `$db`.`model_has_roles` (
            `role_id` bigint unsigned NOT NULL,
            `model_type` varchar(255) NOT NULL,
            `model_id` bigint unsigned NOT NULL,
            PRIMARY KEY (`role_id`, `model_id`, `model_type`),
            KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $pdo->exec($sql);
        echo "  ✓ model_has_roles erstellt/existiert\n";

        // Erstelle role_has_permissions
        $sql = "CREATE TABLE IF NOT EXISTS `$db`.`role_has_permissions` (
            `permission_id` bigint unsigned NOT NULL,
            `role_id` bigint unsigned NOT NULL,
            PRIMARY KEY (`permission_id`, `role_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $pdo->exec($sql);
        echo "  ✓ role_has_permissions erstellt/existiert\n\n";
    }

    echo "✓✓✓ ALLE PERMISSION-TABELLEN ERSTELLT! ✓✓✓\n";

} catch (PDOException $e) {
    echo "FEHLER: " . $e->getMessage() . "\n";
}
