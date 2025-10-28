<?php
$c = new mysqli('localhost', 'root', '');
$r = $c->query('DESCRIBE kpkb3.comet_matches');
echo "Columns in comet_matches:\n";
while($col = $r->fetch_assoc()) {
    echo "  - {$col['Field']}\n";
}
