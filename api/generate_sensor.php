<?php
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../services/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["Error" => "Method not allowed"]);
    exit();
}

$stmt = $conn->query("
    SELECT
        m.monitor_id,
        d.divice_name,
        m.datax_value
    FROM page_data_manage_monitor m
    LEFT JOIN page_data_manage_group g
        ON m.group_id = g.group_id
    LEFT JOIN page_data_manage_device d
        ON m.device_id = d.device_id
    LEFT JOIN page_data_manage_datax x
        ON m.datax_id = x.datax_id
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data, JSON_UNESCAPED_UNICODE);
