<?php
$c = new mysqli('localhost', 'root', '');
$r = $c->query('DESCRIBE kpkb3.comet_competitions');
echo "Columns in comet_competitions:\n";
while($col = $r->fetch_assoc()) {
    echo "  - {$col['Field']}\n";
}
