<?php
header('Content-Type: application/json; charset=utf-8');

$start = $_GET['start'] ?? date('Y-m-d', strtotime('-10 days'));
$end   = $_GET['end'] ?? date('Y-m-d');
$sizesParam = $_GET['sizes'] ?? '50,70';
$sizes = array_values(array_filter(array_map('intval', explode(',', $sizesParam))));
if (count($sizes) === 0) $sizes = [50, 70];

$labels = [];
$dt = new DateTime($start);
$dtEnd = new DateTime($end);
$dtEnd->modify('+1 day');

for (; $dt < $dtEnd; $dt->modify('+1 day')) {
  $labels[] = $dt->format('Y-m-d');
}

$series = [];
foreach ($sizes as $s) {
  // ตั้งค่าเริ่มต้นคนละระดับ เพื่อให้เส้นดูต่างกัน
  $base = ($s <= 50) ? 150 : 120;
  $data = [];
  $v = $base;

  foreach ($labels as $_) {
    // สุ่มขึ้นลงเล็กน้อยให้เหมือน trend
    $v += rand(-2, 3);
    $data[] = $v;
  }

  $series[] = ["size" => $s, "data" => $data];
}

echo json_encode([
  "start"  => $start,
  "end"    => $end,
  "labels" => $labels,
  "series" => $series
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
