<?php

/**
 * Reset Admin Password Script
 * Setzt das Passwort für info@klubportal.com in allen Datenbanken neu
 */

$email = 'info@klubportal.com';
$password = 'Zagreb123!';

// Passwort-Hash erstellen
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

echo "Neues Passwort-Hash: " . substr($passwordHash, 0, 30) . "...\n\n";

// Datenbanken
$databases = [
    'kpkb3',
    'tenant_nknapijed',
    'tenant_nkprigorjem'
];

try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach ($databases as $database) {
        echo "Verarbeite Datenbank: $database\n";

        // Prüfe ob Benutzer existiert
        $stmt = $pdo->query("SELECT id, email FROM $database.users WHERE email = '$email'");
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo "  ✓ Benutzer gefunden (ID: {$user['id']})\n";

            // Update Passwort
            $updateSql = "UPDATE $database.users SET password = ? WHERE email = ?";
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute([$passwordHash, $email]);

            echo "  ✓ Passwort aktualisiert\n";

            // Verifiziere Update
            $stmt = $pdo->query("SELECT SUBSTRING(password, 1, 20) as pass_start FROM $database.users WHERE email = '$email'");
            $verify = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "  ✓ Neues Hash beginnt mit: {$verify['pass_start']}...\n";
        } else {
            echo "  ✗ Benutzer nicht gefunden\n";
        }

        echo "\n";
    }

    echo "\n=== PASSWORT TEST ===\n";
    echo "Email: $email\n";
    echo "Passwort: $password\n";

    // Test ob das Passwort funktioniert
    $stmt = $pdo->query("SELECT password FROM kpkb3.users WHERE email = '$email'");
    $storedHash = $stmt->fetchColumn();

    if (password_verify($password, $storedHash)) {
        echo "\n✓✓✓ PASSWORT VERIFIZIERUNG ERFOLGREICH! ✓✓✓\n";
    } else {
        echo "\n✗✗✗ PASSWORT VERIFIZIERUNG FEHLGESCHLAGEN! ✗✗✗\n";
    }

} catch (PDOException $e) {
    echo "FEHLER: " . $e->getMessage() . "\n";
}
