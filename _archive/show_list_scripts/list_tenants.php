<?php
$c = new mysqli('localhost', 'root', '');
$r = $c->query('SELECT id, data FROM kpkb3.tenants LIMIT 3');
echo "Tenants:\n";
while($t = $r->fetch_assoc()) {
    $data = json_decode($t['data'], true);
    echo "- {$t['id']}\n";
    if (isset($data['tenancy_db_name'])) {
        echo "  DB: {$data['tenancy_db_name']}\n";
    }
}
