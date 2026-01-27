<?php 

header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../services/config.php";   

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["Error" => "Method not allowed"]);
    exit();
}


$stmt = $conn->prepare("
    SELECT 
        id,
        name,
        label,(
            SELECT value
            FROM datas_table
            WHERE datas_table.name_table_id = names_table.id
            LIMIT 1
        ) AS value
    FROM names_table
    WHERE id IN (10,14,16)
");

$stmt->execute();



$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data, JSON_UNESCAPED_UNICODE);


?>

