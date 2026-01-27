<?php 

header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../services/config.php";   

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["Error" => "Method not allowed"], JSON_UNESCAPED_UNICODE);
    exit();
}

// สมมติว่าคุณมี $conn = pg_connect(...) แล้ว

$result = pg_query($conn, "
    SELECT id, name
    FROM names_table
    WHERE id IN (11,12)
");

if (!$result) {
    http_response_code(500);
    echo json_encode(["Error" => pg_last_error($conn)], JSON_UNESCAPED_UNICODE);
    exit();
}

$data = [];

while ($row = pg_fetch_assoc($result)) {

    // แทน prepare/execute ด้วย pg_query_params
    $result2 = pg_query_params(
        $conn,
        "SELECT * FROM datas_table WHERE name_table_id = $1",
        [$row['id']]
    );

    if (!$result2) {
        http_response_code(500);
        echo json_encode(["Error" => pg_last_error($conn)], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $value = pg_fetch_all($result2) ?: [];

    $data[] = [
        'id'    => (int)$row['id'],
        'name'  => $row['name'],
        'value' => $value
    ];
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);


