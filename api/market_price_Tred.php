<?php 
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../services/config.php";   

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["Error" => "Method not allowed"], JSON_UNESCAPED_UNICODE);
    exit();
}


$sql = "
SELECT
    e.id,
    e.event_price,
    e.event_month,
    d.value,
    d.start_day,
    d.end_day
    FROM shrimp_price_event e
    LEFT JOIN datas_table d
        ON d.name_table_id = e.data_table_id
    ORDER BY e.id DESC;


    ";
    

$result = pg_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(["Error" => pg_last_error($conn)], JSON_UNESCAPED_UNICODE);
    exit();
}

$data = pg_fetch_all($result) ?: [];

echo json_encode($data, JSON_UNESCAPED_UNICODE);
