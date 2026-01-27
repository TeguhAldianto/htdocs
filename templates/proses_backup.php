<?php
include '../config.php';

try {
    $sql = "-- MySPP Auto Backup\n-- Date: " . date('Y-m-d H:i:s') . "\nSET FOREIGN_KEY_CHECKS = 0;\n\n";
    
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // Struktur
        $res = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $sql .= "\n\n" . $res['Create Table'] . ";\n\n";

        // Data
        $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $row) {
            $sql .= "INSERT INTO `$table` VALUES (";
            $items = [];
            foreach ($row as $item) {
                $items[] = is_null($item) ? "NULL" : "'" . addslashes($item) . "'";
            }
            $sql .= implode(",", $items) . ");\n";
        }
    }
    $sql .= "\nSET FOREIGN_KEY_CHECKS = 1;";

    header('Content-Type: application/octet-stream');
    header("Content-disposition: attachment; filename=\"Backup_SPP_".date('Ymd_His').".sql\"");
    echo $sql;
    exit;
} catch (Exception $e) { die($e->getMessage()); }