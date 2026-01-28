<?php
require __DIR__ . "/../services/config.php";

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["Error" => "Method not allowed"], JSON_UNESCAPED_UNICODE);
    exit();
}

$result = pg_query($conn,
"
    SELECT * 
    FROM page_data_manage_monitor
    WHERE group_id = 1 AND device_id IN (1,2,3,4)

");

if (!$result){
    http_response_code(500);
    echo json_encode(["Error" => pg_last_error($conn)], JSON_UNESCAPED_UNICODE);
    exit();
}
$data = [];

while ($i = pg_fetch_assoc($result)) {

    $result2 = pg_query_params(
        $conn,
        "
        SELECT data_value, createtime
        FROM data_log
        WHERE group_id  = $1
          AND device_id = $2
          AND type_id   = $3
          AND datax_id  = $4
        ORDER BY createtime
        ",
        [
            $i['group_id'],
            $i['device_id'],
            $i['type_id'],
            $i['datax_id']
        ]
    );

    if (!$result2) {
        http_response_code(500);
        echo json_encode(["Error" => pg_last_error($conn)], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $logs = pg_fetch_all($result2) ?: [];

    $data[] = [
        'monitor_name' => $i['monitor_name'],
        'device_id'    => (int)$i['device_id'],
        'points'       => array_map(fn($r) => [
            'time'  => $r['createtime'],
            'value' => (float)$r['data_value']
        ], $logs)
    ];
}


// echo json_encode($result2, JSON_UNESCAPED_UNICODE);

// $data = pg_fetch_all($result) ?: [];

echo json_encode($data, JSON_UNESCAPED_UNICODE);




