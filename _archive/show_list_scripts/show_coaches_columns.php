<?php
$c = new mysqli('localhost', 'root', '');
$r = $c->query('DESCRIBE kpkb3.comet_coaches');
echo "comet_coaches columns:\n";
while($col = $r->fetch_assoc()) {
    echo "  - {$col['Field']} ({$col['Type']})\n";
}
