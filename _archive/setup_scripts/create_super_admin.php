<?php

$databases = [
    'kpkb3' => 'Central Backend',
    'tenant_nknapijed' => 'NK Naprijed',
    'tenant_nkprigorjem' => 'NK Prigorje',
];

echo "=== SUPER ADMIN BENUTZER ERSTELLEN ===\n\n";

$adminData = [
    'name' => 'Klubportal',
    'email' => 'info@klubportal.com',
    'password' => password_hash('Zagreb12#!', PASSWORD_BCRYPT),
];

echo "Admin-Daten:\n";
echo "  Name:     {$adminData['name']}\n";
echo "  Email:    {$adminData['email']}\n";
echo "  Password: Zagreb12#!\n\n";
echo str_repeat('-', 60) . "\n\n";

foreach ($databases as $dbName => $dbLabel) {
    echo "Datenbank: {$dbLabel} ({$dbName})\n";

    try {
        $pdo = new PDO("mysql:host=localhost;dbname={$dbName};charset=utf8mb4", 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prüfe ob User bereits existiert
        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ?");
        $stmt->execute([$adminData['email']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            echo "  ⚠️  Benutzer existiert bereits (ID: {$existing['id']})\n";

            // Aktualisiere Passwort
            $stmt = $pdo->prepare("UPDATE users SET password = ?, name = ?, updated_at = NOW() WHERE email = ?");
            $stmt->execute([$adminData['password'], $adminData['name'], $adminData['email']]);
            echo "  ✅ Passwort aktualisiert\n";
        } else {
            // Erstelle neuen User
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW(), NOW())
            ");
            $stmt->execute([
                $adminData['name'],
                $adminData['email'],
                $adminData['password']
            ]);

            $userId = $pdo->lastInsertId();
            echo "  ✅ Benutzer erstellt (ID: {$userId})\n";
        }

        echo "\n";

    } catch (PDOException $e) {
        echo "  ❌ Fehler: {$e->getMessage()}\n\n";
    }
}

echo str_repeat('=', 60) . "\n";
echo "✅ Super Admin erstellt/aktualisiert in allen Datenbanken!\n\n";
echo "Login-Daten:\n";
echo "  Email:    info@klubportal.com\n";
echo "  Password: Zagreb12#!\n\n";
echo "URLs:\n";
echo "  Central:       http://localhost:8000/admin/login\n";
echo "  NK Naprijed:   http://nknapijed.localhost:8000/club/login\n";
echo "  NK Prigorje:   http://nkprigorjem.localhost:8000/club/login\n";
echo str_repeat('=', 60) . "\n";
