<?php
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../../services/config.php";

$parent_id = $_POST['requestApi'] ?? null;

if (empty($parent_id)) {
  echo json_encode(["status"=>"error","message"=>"requestApi is required"], JSON_UNESCAPED_UNICODE);
  exit;
}

/* ดึงตารางแม่ */
$sql_parent = "SELECT * FROM names_table WHERE id = $1";
$res_parent = pg_query_params($conn, $sql_parent, [$parent_id]);
$parent = pg_fetch_assoc($res_parent);

if (!$parent) {
  echo json_encode(["status"=>"error","message"=>"parent not found"], JSON_UNESCAPED_UNICODE);
  exit;
}

/*  ดึง columns (ลูกของ parent) */
$sql_cols = "SELECT * FROM names_table WHERE child_of_table_id = $1 ORDER BY id ASC";
$res_cols = pg_query_params($conn, $sql_cols, [$parent_id]);

$result = $parent;
$result["columns"] = [];
/* จะเก็บ logs แยกตาม column id: logs */
$group = [];
while ($col = pg_fetch_assoc($res_cols)) {
  $col_id = $col["id"];
  $result["columns"][] = $col;

  /*  ดึง logs ของแต่ละ column */
  $sql_logs = "SELECT * FROM datas_table WHERE name_table_id = $1 ORDER BY id DESC";
  $res_logs = pg_query_params($conn, $sql_logs, [$col_id]);
  $logs = pg_fetch_all($res_logs) ?: [];

  /* แยกเป็น logs{column_id} */
  $group = [];

  foreach ($logs as $log) {
  $key = $log['second_label'] ?? 'อื่นๆ';
  if (!isset($groups[$key])) $groups[$key] = [];

  /* ตัด filed ให้ fontend */
  $item = [
    "id" => $log["id"],
    "start_day" => $log["start_day"],
    "end_day" => $log["end_day"],
    "value" => $log["value"],

  ];

  $groups[$key][] = $item;
}


$column["Columns_in_Table"] = $groups;
// $result["logs".$col_id] = $logs;

  // logs รวม:
//   $result["logs_by_column"][$col_id] = $logs;
}

echo json_encode([
  "status" => "success",
  "data" => $column,
// echo "key is : ".$res_id;
], JSON_UNESCAPED_UNICODE);

pg_close($conn);
