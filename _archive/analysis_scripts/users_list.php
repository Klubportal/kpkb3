<?php
$users_json = shell_exec('cd "c:\xampp\htdocs\kp_club_management" && php artisan tinker --execute="echo json_encode(DB::table(\"users\")->select(\"id\", \"name\", \"email\")->get());"');
$users = json_decode($users_json, true);

echo "========================================\n";
echo "   ALLE BENUTZER IN DER DATENBANK\n";
echo "========================================\n\n";

if (empty($users)) {
    echo "❌ Keine User gefunden!\n";
} else {
    foreach ($users as $user) {
        echo "✓ {$user['name']}\n";
        echo "  Email: {$user['email']}\n";
        echo "  ID: {$user['id']}\n\n";
    }
}

echo "========================================\n";
echo "GESAMT: " . count($users) . " User(s)\n";
echo "========================================\n";
