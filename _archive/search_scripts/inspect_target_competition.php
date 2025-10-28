<?php
$ch = curl_init('https://api-hns.analyticom.de/api/export/comet/competitions');
curl_setopt($ch, CURLOPT_USERPWD, 'nkprigorje:3c6nR$dS');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// Find competition 100629221 (our NK Prigorje competition)
$targetFifaId = '100629221';
$found = null;

foreach ($data as $comp) {
    if ((string)$comp['competitionFifaId'] === $targetFifaId) {
        $found = $comp;
        break;
    }
}

if ($found) {
    echo "【 NK Prigorje Competition Structure 】\n";
    echo "FIFA ID: 100629221\n";
    echo str_repeat("=", 80) . "\n\n";

    echo "Top-level keys (" . count($found) . "):\n";
    foreach ($found as $key => $value) {
        $type = gettype($value);
        if ($type === 'array') {
            echo "  - $key (array, " . count($value) . " items)\n";
        } else if ($type === 'NULL') {
            echo "  - $key (NULL)\n";
        } else {
            $preview = substr((string)$value, 0, 50);
            echo "  - $key ($type): $preview\n";
        }
    }

    // Show full structure
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "Full structure:\n";
    $json = json_encode($found, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $lines = explode("\n", $json);
    foreach (array_slice($lines, 0, 200) as $line) {
        echo $line . "\n";
    }

    if (count($lines) > 200) {
        echo "\n... [" . (count($lines) - 200) . " more lines] ...\n";
    }
} else {
    echo "Competition 100629221 not found in API data\n";
    echo "Available competitions in database:\n";
    // Count and show what we have
    $count = 0;
    foreach ($data as $comp) {
        $count++;
        if ($count <= 5) {
            echo "  - " . $comp['competitionFifaId'] . ": " . $comp['internationalName'] . "\n";
        }
    }
    echo "  ... and " . ($count - 5) . " more\n";
}
