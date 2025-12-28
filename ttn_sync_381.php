<?php

/**
 * TTN Japan API Sync Script - Single Program Test
 * ทดสอบ sync TTN Japan API เฉพาะ program 381 ที่มี period
 * 
 * Usage: php ttn_sync_381.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Backend\ApiManagementController;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\TourModel;
use App\Models\Backend\TourPeriodModel;

echo "=== TTN Japan API Sync Test - Program 381 ===\n";
echo "Testing sync for program with periods...\n\n";

try {
    // หา TTN Japan API Provider
    $provider = ApiProviderModel::where('code', 'ttn_japan')->first();
    
    if (!$provider) {
        echo "❌ Error: TTN Japan API provider not found!\n";
        exit(1);
    }
    
    echo "✓ Found provider: {$provider->name} (ID: {$provider->id})\n\n";
    
    // ลบ tour เดิม (ถ้ามี) เพื่อทดสอบใหม่
    $existingTour = TourModel::where(['api_id' => '381', 'api_type' => 'ttn'])->first();
    if ($existingTour) {
        TourPeriodModel::where('tour_id', $existingTour->id)->forceDelete();
        $existingTour->forceDelete();
        echo "✓ Deleted existing tour and periods for clean test\n";
    }
    
    // Mock tour data สำหรับ program 381 (จาก API response)
    $tourData = [
        'P_ID' => '381',
        'P_CODE' => 'VZ012',
        'P_NAME' => 'TOKYO FUJI ILLUMINATION 5D 3N BY VZ -- MAR\'26',
        'P_PRICE' => '32888',
        'P_DAY' => '5',
        'P_NIGHT' => '3', 
        'P_AIRLINE' => 'VZ',
        'P_AIRLINE_NAME' => 'Thai Vietjet Air',
        'P_LOCATION' => 'Tokyo,Yamanashi',
        'P_HOTEL_STAR' => 3,
        'P_MEAL' => 'ครึ่งเส้น',
        'P_HIGHLIGHT' => 'ชมซากุระ,วัดอาซากุสะ,ภูเขาไฟฟูจิ',
        'P_TAG' => 'HOT',
        'P_DEPARTURE' => '2026-03-02',
        'P_RETURN' => '2026-03-06',
        'BANNER' => 'https://ttntour.com/_next/image?url=https://file-service.lnwtiao.com/f/ttntour/test.png&w=1080&q=75',
        'PDF' => 'https://drive.google.com/file/d/test/view?usp=sharing',
        'WORD' => '',
        'Itinerary' => ''
    ];
    
    // เรียกใช้ ApiManagementController เพื่อ sync แบบจำกัด
    $controller = new ApiManagementController();
    
    echo "🚀 Starting sync with limit 1...\n";
    $result = $controller->performSync($provider, 'manual', 1);
    
    echo "📊 Sync Result:\n";
    echo "   Summary: " . json_encode($result['summary'], JSON_PRETTY_PRINT) . "\n";
    
    // ตรวจสอบ tour ที่สร้างล่าสุด
    $latestTour = TourModel::where('api_type', 'ttn')
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'desc')
        ->first();
        
    if ($latestTour) {
        echo "\n🎯 Latest Tour Details:\n";
        echo "   Code: {$latestTour->code}\n";
        echo "   Name: {$latestTour->name}\n";
        echo "   API ID: {$latestTour->api_id}\n";
        echo "   Image: " . ($latestTour->image ? 'Yes' : 'No') . "\n";
        echo "   PDF: " . ($latestTour->pdf_file ? 'Yes' : 'No') . "\n";
        
        // ตรวจสอบ periods
        $periods = TourPeriodModel::where('tour_id', $latestTour->id)
            ->where('api_type', 'ttn')
            ->whereNull('deleted_at')
            ->get();
            
        echo "   Periods: " . $periods->count() . "\n";
        
        if ($periods->count() > 0) {
            echo "\n📅 Period Details:\n";
            foreach ($periods as $i => $period) {
                echo "   Period " . ($i + 1) . ":\n";
                echo "     Period API ID: {$period->period_api_id}\n";
                echo "     Start Date: {$period->start_date}\n";
                echo "     End Date: {$period->end_date}\n";
                echo "     Price1: {$period->price1}\n";
                echo "     Price2: {$period->price2}\n";
                echo "     Available: {$period->count}\n\n";
            }
        } else {
            echo "   ⚠️ No periods found - tour should be deleted by business logic\n";
        }
    } else {
        echo "\n⚠️ No tour found - might be deleted due to no periods\n";
    }
    
    echo "✓ Test completed successfully!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Exception occurred:\n";
    echo "Message: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}