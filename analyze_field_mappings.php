<?php
$pdo = new PDO('mysql:host=27.254.134.77;dbname=nexttrip_web', 'nexttrip_web', '49$$INotHbhk6jqv');

echo "=== Field mappings for country_id ===\n";
$stmt = $pdo->prepare('SELECT api_provider_id, api_field, transformation_rules FROM tb_api_field_mappings WHERE local_field = "country_id" ORDER BY api_provider_id');
$stmt->execute();
echo sprintf("%-12s %-20s %s\n", 'PROVIDER', 'API FIELD', 'RULES');
echo str_repeat('-', 60) . "\n";
while ($row = $stmt->fetch()) {
    $rules = $row['transformation_rules'] ? 'YES' : 'NO';
    echo sprintf("%-12s %-20s %s\n", $row['api_provider_id'], $row['api_field'], $rules);
}

echo "\n=== Best Consortium field mappings ===\n";
// Find provider with 'best' in code or name
$stmt = $pdo->prepare('SHOW TABLES LIKE "tb_api_provider"');
$stmt->execute();
if ($stmt->fetch()) {
    echo "tb_api_provider table exists\n";
} else {
    echo "tb_api_provider table does not exist\n";
    echo "Checking other provider tables...\n";
    
    $stmt = $pdo->prepare('SHOW TABLES LIKE "%provider%"');
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        echo "Found table: " . $row[0] . "\n";
    }
}

echo "\n=== API Provider mapping ===\n";
echo "Provider ID 47: api_field 'country_name'\n";
echo "Provider ID 48: api_field 'tour_country'\n"; 
echo "Provider ID 52: api_field 'CountryName'\n";
echo "Provider ID 42: api_field 'nameEng' และ 'country_name_eng'\n";
echo "Provider ID 41: api_field 'CountryName'\n";
echo "Provider ID 43: api_field 'JAPAN'\n";
echo "Provider ID 46: api_field 'Country'\n";

echo "\n=== Best Consortium headcode analysis ===\n";
echo "จาก headcode: Best Consortium ใช้ \$call1['nameEng'] และ country_name_en\n";
echo "Provider ID 42 มี field 'nameEng' และ 'country_name_eng' - น่าจะเป็น Best Consortium\n";

?>