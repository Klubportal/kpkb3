<?php
// Sehr einfaches Debug-Script - kein Laravel Bootstrap
header('Content-Type: text/plain');

echo "=== LOCALHOST DEBUG ===\n\n";
echo "Zeit: " . date('Y-m-d H:i:s') . "\n";
echo "Host: " . $_SERVER['HTTP_HOST'] . "\n";
echo "URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "IP: " . $_SERVER['REMOTE_ADDR'] . "\n\n";

echo "=== FUNKTIONIERT ===\n";
echo "Wenn du das siehst, läuft localhost:8000!\n";
echo "Das Problem ist also in Laravel/Filament.\n";
