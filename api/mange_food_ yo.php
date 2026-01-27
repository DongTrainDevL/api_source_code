<?php 

header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../services/config.php";   

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["Error" => "Method not allowed"]);
    exit();
}

$stmt = $conn->query("
    SELECT id, name
    FROM names_table
    WHERE id IN (16 ,17) 
    ORDER BY id ASC ;
");
// $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
// echo json_encode($data, JSON_UNESCAPED_UNICODE);



$stmt2 = $conn->prepare("
    SELECT *
    FROM datas_table
    WHERE name_table_id = :id
    ORDER BY id ASC ;
   
    
");

$data = []; // ตัวแปรเก็บข้อมูลทั้งหมด

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $stmt2->execute([':id' => $row['id']]);
    $value = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // เก็บข้อมูล
    $data[] = [
        'id'    => $row['id'],
        'name'  => $row['name'],
        'value' => $value
    ];
}

// แปลงเป็น JSON
echo json_encode($data, JSON_UNESCAPED_UNICODE);



?>
