<?php 

header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../services/config.php";   

// ---- รับ param ----
$sizesParam = $_GET['sizes'] ?? '50,70'; // ต้องการ 2 เส้นเหมือนภาพ
$sizes = array_values(array_filter(array_map('intval', explode(',', $sizesParam))));
if (count($sizes) === 0) $sizes = [50, 70];

$start = $_GET['start'] ?? null; // "2022-01-01"
$end   = $_GET['end'] ?? null;

// ---- สร้าง IN placeholders ----
$inPlaceholders = implode(',', array_fill(0, count($sizes), '?'));

/**
 * เปลี่ยนชื่อตารางให้ตรงของคุณ:
 * สมมติเป็น shrimp_prices_raw มีคอลัมน์: year, month, product_name, value
 */
$sql = "
  SELECT
    make_date((replace(year::text, ',', '')::int - 543), month::int, 1) AS price_date,
    NULLIF(substring(product_name from 'ขนาด\\s*([0-9]+)\\s*ตัว/กก'), '')::int AS size,
    replace(value::text, ',', '')::numeric AS price
  FROM shrimp_prices_raw
  WHERE NULLIF(substring(product_name from 'ขนาด\\s*([0-9]+)\\s*ตัว/กก'), '')::int IN ($inPlaceholders)
";

$params = $sizes;

if ($start) { $sql .= " AND make_date((replace(year::text, ',', '')::int - 543), month::int, 1) >= ? "; $params[] = $start; }
if ($end)   { $sql .= " AND make_date((replace(year::text, ',', '')::int - 543), month::int, 1) <= ? "; $params[] = $end; }

$sql .= " ORDER BY price_date ASC, size ASC ";

// ---- Execute ----
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---- labels (เดือนทั้งหมดที่มีจริง) ----
$labelsMap = [];
foreach ($rows as $r) $labelsMap[$r['price_date']] = true;
$labels = array_keys($labelsMap);
sort($labels);

// map label -> index
$labelIndex = array_flip($labels);

// เตรียม series map
$seriesMap = [];
foreach ($sizes as $s) $seriesMap[$s] = array_fill(0, count($labels), null);

// ใส่ค่า
foreach ($rows as $r) {
  $d = $r['price_date'];
  $s = (int)$r['size'];
  if (isset($labelIndex[$d]) && isset($seriesMap[$s])) {
    $seriesMap[$s][$labelIndex[$d]] = (float)$r['price'];
  }
}

// ส่งออก
$series = [];
foreach ($seriesMap as $s => $data) $series[] = ["size" => (int)$s, "data" => $data];

echo json_encode([
  "labels" => $labels,   // "2022-01-01", ...
  "series" => $series    // [{size:50,data:[...]}, {size:70,data:[...]}]
], JSON_UNESCAPED_UNICODE);
