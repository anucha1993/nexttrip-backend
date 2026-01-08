<?php
$pdo = new PDO('mysql:host=27.254.134.77;dbname=nexttrip_web', 'nexttrip_web', '49$$INotHbhk6jqv');

echo "=== ทัวร์ที่ไม่มี country_id แยกตาม API ===\n";
$stmt = $pdo->prepare('SELECT api_type, COUNT(*) as count FROM tb_tour WHERE country_id IS NULL OR country_id = "[]" OR country_id = "" GROUP BY api_type ORDER BY count DESC');
$stmt->execute();
echo sprintf('%-20s %s' . "\n", 'API TYPE', 'COUNT');
echo str_repeat('-', 30) . "\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf('%-20s %d' . "\n", $row['api_type'] ?: 'NULL', $row['count']);
}

echo "\n=== สาเหตุที่ไม่มี country_id ===\n";

echo "1. Super Holiday API: ไม่ส่ง Country field หรือส่งมาเป็นค่าว่าง\n";
echo "2. Best Consortium API: อาจไม่มี country mapping\n";  
echo "3. Zego API: อาจไม่มี country mapping\n";
echo "4. iTravel API: อาจไม่มี country mapping\n";

echo "\n=== แนวทางแก้ไข ===\n";
echo "1. Super Holiday: ✅ ใช้ headcode logic (ถ้าไม่มี Country field ให้เป็น [])\n";
echo "2. API อื่นๆ: ใช้ transformation_rules สำหรับ country detection จากชื่อทัวร์\n";

echo "\n=== ตรวจสอบ API field mappings ===\n";
$apis = ['best', 'zego', 'itravel', 'ttn_all', 'go365'];
foreach ($apis as $apiType) {
    $stmt = $pdo->prepare('
        SELECT COUNT(fm.id) as mapping_count 
        FROM tb_api_field_mappings fm 
        JOIN tb_api_provider p ON fm.api_provider_id = p.id 
        WHERE p.code LIKE ? AND fm.local_field = "country_id"
    ');
    $stmt->execute(['%' . $apiType . '%']);
    $result = $stmt->fetch();
    echo sprintf("%-15s: %d country mappings\n", $apiType, $result['mapping_count']);
}
?>