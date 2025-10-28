<?php
$ch = curl_init('https://api-hns.analyticom.de/api/export/comet/competitions?limit=1');
curl_setopt($ch, CURLOPT_USERPWD, 'nkprigorje:3c6nR$dS');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (is_array($data) && count($data) > 0) {
    echo "【 Competition Structure Analysis 】\n";
    echo str_repeat("=", 80) . "\n\n";

    $comp = $data[0];
    echo "Top-level keys: " . implode(', ', array_keys($comp)) . "\n\n";

    echo "First 100 lines of first competition:\n";
    $json = json_encode($comp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $lines = explode("\n", $json);
    foreach (array_slice($lines, 0, 100) as $line) {
        echo $line . "\n";
    }

    if (count($lines) > 100) {
        echo "\n... [" . (count($lines) - 100) . " more lines] ...\n";
    }
} else {
    echo "No data returned\n";
}
