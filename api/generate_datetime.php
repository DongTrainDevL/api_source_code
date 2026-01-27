<?php
require __DIR__ . "/../services/config.php";

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["Error" => "Method not allowed"], JSON_UNESCAPED_UNICODE);
    exit();
}

// สมมติว่า config.php มี $conn = pg_connect(...)

$sql = "
    SELECT *
    FROM dashboard_main
    WHERE name = 'date_begin'
";

$result = pg_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(
        ["Error" => pg_last_error($conn)],
        JSON_UNESCAPED_UNICODE
    );
    exit();
}

$data = pg_fetch_all($result) ?: [];

echo json_encode($data, JSON_UNESCAPED_UNICODE);
