<?php

// Test manual sync via web route
$host = '203.146.252.149';
$db = 'nexttrip_work';
$user = 'tracking_nexttrip_work';
$pass = 'm243cVb1&';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 Testing Manual Sync via API Management\n";
    echo "=========================================\n\n";
    
    // 1. Check current state before manual sync
    echo "1️⃣ Current Sync Logs (Before Manual Sync):\n";
    echo "--------------------------------------------\n";
    
    $stmt = $pdo->query("
        SELECT COUNT(*) as total,
               SUM(CASE WHEN sync_type = 'manual' THEN 1 ELSE 0 END) as manual_count,
               SUM(CASE WHEN sync_type = 'auto' THEN 1 ELSE 0 END) as auto_count
        FROM tb_api_sync_logs
    ");
    
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   📊 Total logs: {$counts['total']}\n";
    echo "   👤 Manual syncs: {$counts['manual_count']}\n";
    echo "   ⏰ Auto syncs: {$counts['auto_count']}\n\n";
    
    // 2. Show recent logs
    echo "2️⃣ Recent Sync Logs (Last 10):\n";
    echo "-------------------------------\n";
    
    $stmt = $pdo->query("
        SELECT l.id, l.api_provider_id, l.sync_type, l.status, l.started_at, l.completed_at,
               l.total_records, l.created_tours, l.updated_tours, l.duplicated_tours,
               p.name as provider_name
        FROM tb_api_sync_logs l
        LEFT JOIN tb_api_providers p ON l.api_provider_id = p.id
        ORDER BY l.started_at DESC 
        LIMIT 10
    ");
    
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($logs)) {
        echo "   📝 No logs found\n";
    } else {
        foreach ($logs as $log) {
            $typeIcon = $log['sync_type'] === 'manual' ? '👤' : '⏰';
            $statusIcon = $log['status'] === 'completed' ? '✅' : ($log['status'] === 'failed' ? '❌' : '⏳');
            
            echo "   {$statusIcon} {$typeIcon} ID: {$log['id']} | {$log['provider_name']} | Type: {$log['sync_type']} | Status: {$log['status']}\n";
            echo "      📅 Started: {$log['started_at']} | Completed: " . ($log['completed_at'] ?: 'Running') . "\n";
            echo "      📊 Records: {$log['total_records']} | Created: {$log['created_tours']} | Updated: {$log['updated_tours']} | Duplicated: {$log['duplicated_tours']}\n\n";
        }
    }
    
    // 3. Instructions for manual testing
    echo "3️⃣ Manual Testing Instructions:\n";
    echo "--------------------------------\n";
    echo "   🌐 Go to: http://localhost/nexttrip-backend/public/backend/api-management\n";
    echo "   👆 Click 'Manual Sync' button for any provider\n";
    echo "   📊 Check if new log appears with sync_type = 'manual'\n\n";
    
    // 4. Test URL for manual sync
    echo "4️⃣ Direct Test URLs:\n";
    echo "--------------------\n";
    
    $stmt = $pdo->query("SELECT id, name FROM tb_api_providers WHERE status = 'active' LIMIT 3");
    $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($providers as $provider) {
        echo "   🔗 Manual sync {$provider['name']}: http://localhost/nexttrip-backend/public/backend/api-management/{$provider['id']}/sync?_token=test\n";
    }
    
    echo "\n5️⃣ Testing Scheduled Sync Recording:\n";
    echo "-------------------------------------\n";
    echo "   📝 Run: php artisan api:sync-scheduled\n";
    echo "   📊 Check if new logs appear with sync_type = 'auto'\n\n";
    
    // 6. Monitor database for changes
    echo "6️⃣ To Monitor Real-time Changes:\n";
    echo "--------------------------------\n";
    echo "   🔄 Run this script again after manual sync to see changes\n";
    echo "   📱 Or check database directly:\n";
    echo "      SELECT * FROM tb_api_sync_logs ORDER BY started_at DESC LIMIT 5;\n\n";
    
    echo "✅ Testing setup complete!\n";
    echo "📋 Next steps:\n";
    echo "   1. Open web interface and perform manual sync\n";
    echo "   2. Run scheduled sync command\n";
    echo "   3. Check logs to verify sync_type recording\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "\n";
}