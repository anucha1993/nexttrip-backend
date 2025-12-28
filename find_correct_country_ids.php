<?php
$pdo = new PDO('mysql:host=27.254.134.77;dbname=nexttrip_web', 'nexttrip_web', '49$$INotHbhk6jqv');
echo "Finding correct country IDs:\n";
$countries = ['จีน', 'ญี่ปุ่น', 'สิงคโปร์', 'มาเลเซีย', 'อินโดนีเซีย', 'ไทย', 'เกาหลี', 'เวียดนาม', 'พม่า', 'กัมพูชา', 'ลาว', 'ฟิลิปปินส์'];
foreach ($countries as $name) {
    $stmt = $pdo->prepare('SELECT id, country_name_th, country_name_en FROM tb_country WHERE country_name_th LIKE ? AND status = "on" AND deleted_at IS NULL LIMIT 1');
    $stmt->execute(['%' . $name . '%']);
    if ($row = $stmt->fetch()) {
        echo sprintf("%-12s => ID: %-3s (%s)\n", $name, $row['id'], $row['country_name_en']);
    } else {
        echo sprintf("%-12s => NOT FOUND\n", $name);
    }
}

echo "\n=== Additional search by English names ===\n";
$englishCountries = ['China', 'Japan', 'Singapore', 'Malaysia', 'Indonesia', 'Thailand', 'Korea', 'Vietnam', 'Myanmar', 'Cambodia', 'Laos', 'Philippines'];
foreach ($englishCountries as $name) {
    $stmt = $pdo->prepare('SELECT id, country_name_th, country_name_en FROM tb_country WHERE country_name_en LIKE ? AND status = "on" AND deleted_at IS NULL LIMIT 1');
    $stmt->execute(['%' . $name . '%']);
    if ($row = $stmt->fetch()) {
        echo sprintf("%-12s => ID: %-3s (%s)\n", $name, $row['id'], $row['country_name_th']);
    } else {
        echo sprintf("%-12s => NOT FOUND\n", $name);
    }
}
?>