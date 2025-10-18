<?php

// Get schedule IDs
$host = '203.146.252.149';
$db = 'nexttrip_work';
$user = 'tracking_nexttrip_work';
$pass = 'm243cVb1&';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 Available Schedule IDs:\n";
    echo "==========================\n";
    
    $stmt = $pdo->query("
        SELECT s.id, s.api_provider_id, s.name, s.is_active,
               p.name as provider_name
        FROM tb_api_schedules s
        LEFT JOIN tb_api_providers p ON s.api_provider_id = p.id
        ORDER BY s.id
    ");
    
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($schedules as $schedule) {
        $status = $schedule['is_active'] ? '✅ Active' : '❌ Inactive';
        echo "   {$status} Schedule ID: {$schedule['id']} | Provider ID: {$schedule['api_provider_id']} | {$schedule['provider_name']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}