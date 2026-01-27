<?php 
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../services/config.php";   

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["Error" => "Method not allowed"], JSON_UNESCAPED_UNICODE);
    exit();
}

// $conn = pg_connect(...);  // สมมติว่าคุณต่อไว้แล้ว

$sql = "
    SELECT 
        id,
        name,
        label,
        (
            SELECT value
            FROM datas_table
            WHERE datas_table.name_table_id = names_table.id
            LIMIT 1
        ) AS value
    FROM names_table
    WHERE id IN (10,14,16)
";

$result = pg_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(["Error" => pg_last_error($conn)], JSON_UNESCAPED_UNICODE);
    exit();
}

$data = pg_fetch_all($result) ?: [];

echo json_encode($data, JSON_UNESCAPED_UNICODE);


