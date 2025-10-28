<?php
$file = 'debug_filament.php';
$code = <<<'PHP'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

try {
    $filament = app('filament');
    echo "===== FILAMENT DEBUG =====\n\n";

    // Get all panels
    echo "Registered Panels:\n";
    foreach ($filament->getPanels() as $panel) {
        echo "- ID: " . $panel->getId() . "\n";
        echo "  Path: " . $panel->getPath() . "\n";
        echo "  Auth Guard: " . ($panel->getAuthGuard() ?? 'default') . "\n";
        echo "\n";
    }

    // Check default panel
    $default = $filament->getPanel();
    echo "Default Panel: " . $default->getId() . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
PHP;

file_put_contents($file, $code);
system('php ' . $file);
unlink($file);
