<?php
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../services/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["Error" => "Method not allowed"]);
    exit();
}

$stmt = $conn->query("
    SELECT * FROM dashboard_main
    WHERE name= 'date_begin'
    
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data, JSON_UNESCAPED_UNICODE);
