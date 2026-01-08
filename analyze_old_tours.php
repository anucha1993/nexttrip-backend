<?php
$pdo = new PDO('mysql:host=27.254.134.77;dbname=nexttrip_web', 'nexttrip_web', '49$$INotHbhk6jqv');

echo "=== Best Consortium ทัวร์ที่ไม่มี country_id ===\n";
$stmt = $pdo->prepare('SELECT name, created_at, updated_at FROM tb_tour WHERE api_type = "best" AND (country_id IS NULL OR country_id = "[]" OR country_id = "") ORDER BY created_at ASC LIMIT 10');
$stmt->execute();
echo "เก่าสุด 10 ทัวร์:\n";
while ($row = $stmt->fetch()) {
    echo sprintf("%-60s | Created: %s | Updated: %s\n", 
        substr($row['name'], 0, 60),
        $row['created_at'],
        $row['updated_at']
    );
}

echo "\n=== Best Consortium ทัวร์ที่มี country_id ===\n";
$stmt = $pdo->prepare('SELECT name, created_at, updated_at FROM tb_tour WHERE api_type = "best" AND country_id IS NOT NULL AND country_id != "[]" AND country_id != "" ORDER BY created_at ASC LIMIT 5');
$stmt->execute();
echo "เก่าสุด 5 ทัวร์:\n";
while ($row = $stmt->fetch()) {
    echo sprintf("%-60s | Created: %s | Updated: %s\n", 
        substr($row['name'], 0, 60),
        $row['created_at'],
        $row['updated_at']
    );
}

echo "\n=== แนวทางแก้ไข ===\n";
echo "1. ทัวร์เก่าที่ไม่มี country_id ควรได้รับการ update ใน sync ครั้งถัดไป\n";
echo "2. หรือสามารถรัน manual update ให้กับ API ที่มี field mapping แล้ว\n";

echo "\n=== Zego API sample ===\n";
$stmt = $pdo->prepare('SELECT name, country_id FROM tb_tour WHERE api_type = "zego" ORDER BY updated_at DESC LIMIT 3');
$stmt->execute();
while ($row = $stmt->fetch()) {
    echo "Name: " . substr($row['name'], 0, 60) . "\n";
    echo "Country ID: " . $row['country_id'] . "\n";
    echo "---\n";
}
?>