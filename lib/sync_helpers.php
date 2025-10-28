<?php

// Generic sync helper functions

/**
 * Compare two arrays for equality of selected keys/values (ignoring timestamps)
 */
function normalize_record(array $row): array {
    // Remove common timestamp fields to avoid false differences
    $ignore = ['created_at', 'updated_at', 'last_synced_at', 'started_at', 'completed_at'];
    foreach ($ignore as $k) {
        if (array_key_exists($k, $row)) unset($row[$k]);
    }
    // Sort by keys for stable comparison
    ksort($row);
    return $row;
}

/**
 * Upsert record if changed.
 * - $conn: mysqli connection
 * - $table: table name
 * - $keyCols: assoc array of key column => value to identify the row (primary unique key)
 * - $data: assoc array of column => value for new data
 * - returns array: ['action'=>'inserted'|'updated'|'skipped', 'changed'=>array_of_changed_columns]
 */
function upsert_if_changed(mysqli $conn, string $table, array $keyCols, array $data): array {
    // Build WHERE clause from keyCols
    $whereParts = [];
    foreach ($keyCols as $col => $val) {
        $whereParts[] = "`" . $conn->real_escape_string($col) . "` = '" . $conn->real_escape_string((string)$val) . "'";
    }
    $where = implode(' AND ', $whereParts);

    // Select existing row
    $sql = "SELECT * FROM `" . $conn->real_escape_string($table) . "` WHERE $where LIMIT 1";
    $res = $conn->query($sql);
    if ($res === false) {
        throw new RuntimeException("DB error selecting existing row: " . $conn->error);
    }

    $existing = $res->fetch_assoc();

    if (!$existing) {
        // Insert new - add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $cols = [];
        $vals = [];
        foreach ($data as $c => $v) {
            $cols[] = "`" . $conn->real_escape_string($c) . "`";
            if ($v === null) $vals[] = 'NULL'; else $vals[] = "'" . $conn->real_escape_string((string)$v) . "'";
        }
        $sql = "INSERT INTO `" . $conn->real_escape_string($table) . "` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
        if ($conn->query($sql) === false) {
            throw new RuntimeException("DB insert error: " . $conn->error);
        }
        return ['action' => 'inserted', 'changed' => array_keys($data)];
    }

    // Compare normalized records
    $normalizedExisting = normalize_record($existing);
    // Merge keyCols into existing for comparison if not present in $data
    $normalizedNew = normalize_record(array_merge($existing, $data));

    // Determine changed columns
    $changed = [];
    foreach ($data as $col => $val) {
        $old = array_key_exists($col, $existing) ? $existing[$col] : null;
        // Normalize boolean/ints to string for comparison
        $oldStr = $old === null ? null : (string)$old;
        $newStr = $val === null ? null : (string)$val;
        if ($oldStr !== $newStr) {
            $changed[] = $col;
        }
    }

    if (empty($changed)) {
        return ['action' => 'skipped', 'changed' => []];
    }

    // Build UPDATE - add updated_at timestamp
    $data['updated_at'] = date('Y-m-d H:i:s');
    $sets = [];
    foreach ($data as $c => $v) {
        if ($v === null) $sets[] = "`" . $conn->real_escape_string($c) . "` = NULL";
        else $sets[] = "`" . $conn->real_escape_string($c) . "` = '" . $conn->real_escape_string((string)$v) . "'";
    }
    $sql = "UPDATE `" . $conn->real_escape_string($table) . "` SET " . implode(',', $sets) . " WHERE $where";
    if ($conn->query($sql) === false) {
        throw new RuntimeException("DB update error: " . $conn->error);
    }

    return ['action' => 'updated', 'changed' => $changed];
}

?>
