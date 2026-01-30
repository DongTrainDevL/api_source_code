
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
    $sensors = $input["requestApi"];
    if (!isset($input["requestApi"])) {
        throw new Exception ("sensor is required");
    }
    
    
    $sql = "
        WITH input_ids AS (
        SELECT unnest($1::int[]) AS id
        )
            SELECT
            n.*,
            d.*         
            FROM names_table n
            LEFT JOIN datas_table d
            ON d.name_table_id = n.id
            WHERE n.id IN (SELECT id FROM input_ids)
            OR n.child_of_table_id IN (SELECT id FROM input_ids)
            ORDER BY n.id ASC;

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
