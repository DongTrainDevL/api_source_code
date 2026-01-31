<?php 
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../../services/config.php";   

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "POST only"], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    $input = json_decode(file_get_contents("php://input"), true);

    // if (!isset($input["sensors"])) {
    //     throw new Exception ("sensor is required");
    // }
  
    $sensors = $input["sensors"];
    $sql = " 
    WITH input_ids AS (
    SELECT unnest($1::text[]) AS monitor_name ) 
    SELECT p.*,l.*
    FROM page_data_manage_monitor p 
    LEFT JOIN datas_log l  
    ";
    

    $result = pg_query_params(
        $conn, 
        $sql,  ['{' .implode(',', $sensors). '}']
    );

    // echo json_encode([
    //     "chickeType" => is_object($result)
    // ]);

    if(!$result) {
        throw new Expection (pg_last_error($conn));
    }

    $data = pg_fetch_all($result) ;
    
    echo json_encode([
       "status" => "success", 
       "reponse_data" => $data ? : []
    ],null);

} catch (Exception $error) {
    ["Error" => "Expection Error", 
    "message" => $error->getMessage()];

};
