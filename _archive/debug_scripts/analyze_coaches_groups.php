<?php

$host = 'localhost';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "🔍 Analyzing available coaches and groups...\n";
echo "============================================================\n\n";

// 1. Get coaches from landlord DB
echo "📋 Coaches in kpkb3.comet_coaches:\n";
echo "------------------------------------------------------------\n";

$coaches = $conn->query("
    SELECT id, person_fifa_id, name, first_name, last_name, role, status
    FROM kpkb3.comet_coaches
    WHERE status = 'active'
    ORDER BY role, name
");

if ($coaches->num_rows == 0) {
    echo "❌ No coaches found\n\n";
} else {
    while ($coach = $coaches->fetch_assoc()) {
        $displayName = $coach['name'] ?: ($coach['first_name'] . ' ' . $coach['last_name']);
        echo sprintf("ID %-3d | FIFA: %-10s | %-35s | Role: %-20s\n",
            $coach['id'],
            $coach['person_fifa_id'],
            $displayName,
            $coach['role']
        );
    }
    echo "\nTotal active coaches: " . $coaches->num_rows . "\n\n";
}

// 2. Get groups from tenant DB
echo "📋 Groups (Mannschaften) in tenant_nkprigorjem.groups:\n";
echo "------------------------------------------------------------\n";

$groups = $conn->query("
    SELECT id, name, label, gender, active, published
    FROM tenant_nkprigorjem.groups
    ORDER BY `order`, name
");

if ($groups->num_rows == 0) {
    echo "❌ No groups found - need to create groups first!\n\n";
} else {
    while ($group = $groups->fetch_assoc()) {
        echo sprintf("ID %-3d | %-20s | %-30s | Gender: %s | Active: %d | Published: %d\n",
            $group['id'],
            $group['name'],
            $group['label'],
            $group['gender'],
            $group['active'],
            $group['published']
        );
    }
    echo "\nTotal groups: " . $groups->num_rows . "\n\n";
}

// 3. Check existing coach_group assignments
echo "📋 Existing coach-group assignments:\n";
echo "------------------------------------------------------------\n";

$assignments = $conn->query("
    SELECT cg.id, cg.coach_id, cg.group_id, g.name as group_name
    FROM tenant_nkprigorjem.coach_group cg
    LEFT JOIN tenant_nkprigorjem.groups g ON cg.group_id = g.id
");

if ($assignments->num_rows == 0) {
    echo "❌ No assignments yet\n";
} else {
    while ($a = $assignments->fetch_assoc()) {
        echo sprintf("Assignment ID %-3d | Coach ID: %-3d | Group ID: %-3d (%s)\n",
            $a['id'],
            $a['coach_id'],
            $a['group_id'],
            $a['group_name']
        );
    }
}

echo "\n============================================================\n";

$conn->close();
