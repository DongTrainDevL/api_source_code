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
    $sql = " SELECT * FROM page_data_manage_monitor WHERE monitor_name = ANY($1)";

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
