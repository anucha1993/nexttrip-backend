<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\TourModel;
use App\Models\Backend\TourPeriodModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "=== Syncing Checkin Group API (3 records) ===\n\n";

// Get provider using model
$provider = ApiProviderModel::where('code', 'checkingroup')->first();
if (!$provider) {
    echo "❌ Provider not found!\n";
    exit(1);
}

echo "Provider: {$provider->name} (ID: {$provider->id})\n";
echo "URL: {$provider->url}\n\n";

// Parse headers
$headers = is_array($provider->headers) ? $provider->headers : json_decode($provider->headers, true) ?? [];

echo "Calling API...\n";

try {
    $response = Http::withHeaders($headers)
        ->timeout(30)
        ->get($provider->url);
    
    if (!$response->successful()) {
        echo "❌ API call failed: HTTP {$response->status()}\n";
        exit(1);
    }
    
    $tours = $response->json();
    
    if (!is_array($tours)) {
        echo "❌ Invalid response format\n";
        exit(1);
    }
    
    echo "✓ Got " . count($tours) . " tours from API\n\n";
    
    // Limit to 3 records
    $toursToSync = array_slice($tours, 0, 3);
    
    echo "=== Processing 3 tours ===\n\n";
    
    $created = 0;
    $updated = 0;
    $errors = [];
    
    foreach ($toursToSync as $index => $tourData) {
        $num = $index + 1;
        echo "[$num/3] Processing: {$tourData['name']}\n";
        echo "  API ID: {$tourData['id']}, Code: {$tourData['code']}\n";
        
        try {
            DB::beginTransaction();
            
            // Check if tour exists by api_id and api_type first
            $tour = TourModel::where('api_id', $tourData['id'])
                ->where('api_type', 'checkingroup')
                ->first();
            
            // If not found by api_id, check by code1 to handle duplicates
            if (!$tour && !empty($tourData['code'])) {
                $tour = TourModel::where('code1', $tourData['code'])->first();
                if ($tour) {
                    echo "  → Found existing tour by code (ID: {$tour->id}, api_type: {$tour->api_type})\n";
                    echo "  → Updating api_id and api_type to checkingroup\n";
                }
            }
            
            $isNew = !$tour;
            
            if (!$tour) {
                $tour = new TourModel();
                $tour->api_id = $tourData['id'];
                $tour->api_type = 'checkingroup';
                $tour->data_type = 2; // API data
                echo "  → Creating new tour\n";
            } else {
                echo "  → Updating existing tour (ID: {$tour->id})\n";
            }
            
            // Map tour fields
            $tour->code1 = $tourData['code'] ?? null;
            $tour->name = $tourData['name'] ?? null;
            $tour->tour_detail = $tourData['highlight'] ?? null;
            $tour->image = $tourData['banner'] ?? null;
            
            // PDF: Download and process (don't save URL directly)
            echo "  Downloading PDF...\n";
            if (!empty($tourData['pdf'])) {
                try {
                    $pdfUrl = $tourData['pdf'];
                    $response = Http::timeout(60)->get($pdfUrl);
                    
                    if ($response->successful() && $response->header('Content-Type') && 
                        strpos($response->header('Content-Type'), 'application/pdf') !== false) {
                        
                        // Generate filename from URL
                        $urlPath = parse_url($pdfUrl, PHP_URL_PATH);
                        $filename = basename($urlPath);
                        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
                        
                        if (!str_ends_with(strtolower($filename), '.pdf')) {
                            $filename .= '.pdf';
                        }
                        
                        $dirPath = 'upload/tour/pdf_file/checkingroupapi';
                        $newPath = $dirPath . '/' . $filename;
                        
                        // Create directory
                        if (!Storage::disk('public')->exists($dirPath)) {
                            Storage::disk('public')->makeDirectory($dirPath, 0755, true);
                        }
                        
                        // Save PDF
                        Storage::disk('public')->put($newPath, $response->body());
                        
                        echo "    → PDF saved to: {$newPath}\n";
                        
                        // Apply header/footer if enabled
                        $provider = DB::table('tb_api_providers')->where('code', 'checkingroup')->first();
                        if ($provider && $provider->pdf_header_footer_enabled === 'on') {
                            echo "    → Applying header/footer...\n";
                            
                            $fpdi = new \setasign\Fpdi\Fpdi;
                            $filePath = public_path($newPath);
                            $count = $fpdi->setSourceFile($filePath);
                            
                            for ($i=1; $i<=$count; $i++) {
                                $template = $fpdi->importPage($i);
                                $size = $fpdi->getTemplateSize($template);
                                $fpdi->AddPage($size['orientation'], array($size['width'], $size['height']));
                                $fpdi->useTemplate($template);
                                
                                if ($provider->pdf_header && file_exists(public_path($provider->pdf_header))) {
                                    $fpdi->Image(public_path($provider->pdf_header), 0, 0, 210);
                                }
                                
                                if ($provider->pdf_footer && file_exists(public_path($provider->pdf_footer))) {
                                    $fpdi->Image(public_path($provider->pdf_footer), 0, 285, 210);
                                }
                            }
                            
                            $fpdi->Output($filePath, 'F');
                            echo "    → Header/footer applied\n";
                        }
                        
                        $tour->pdf_file = $newPath;
                        
                    } else {
                        echo "    → PDF download failed or invalid content type\n";
                    }
                } catch (\Exception $e) {
                    echo "    → PDF error: {$e->getMessage()}\n";
                }
            }
            
            $tour->save();
            
            echo "  ✓ Tour saved (ID: {$tour->id})\n";
            
            // Process periods
            if (isset($tourData['periods']) && is_array($tourData['periods'])) {
                $periodCount = count($tourData['periods']);
                echo "  Processing {$periodCount} periods...\n";
                
                $periodCreated = 0;
                $periodUpdated = 0;
                
                foreach ($tourData['periods'] as $periodData) {
                    $period = TourPeriodModel::where('tour_id', $tour->id)
                        ->where('period_api_id', $periodData['id'])
                        ->where('api_type', 'checkingroup')
                        ->first();
                    
                    $isPeriodNew = !$period;
                    
                    if (!$period) {
                        $period = new TourPeriodModel();
                        $period->tour_id = $tour->id;
                        $period->period_api_id = $periodData['id'];
                        $period->api_type = 'checkingroup';
                        $periodCreated++;
                    } else {
                        $periodUpdated++;
                    }
                    
                    // Map period fields based on mappings
                    $period->start_date = $periodData['start'] ?? null;
                    $period->end_date = $periodData['end'] ?? null;
                    $period->price1 = $periodData['priceAdultDouble'] ?? 0;
                    $period->price2 = $periodData['priceForOne'] ?? 0;
                    $period->price3 = $periodData['priceChild'] ?? 0;
                    $period->price4 = $periodData['priceChildNoBed'] ?? 0;
                    $period->special_price1 = $periodData['priceAdultDouble'] ?? 0;
                    $period->special_price2 = $periodData['priceForOne'] ?? 0;
                    $period->special_price3 = $periodData['priceChild'] ?? 0;
                    $period->special_price4 = $periodData['priceChildNoBed'] ?? 0;
                    $period->count = $periodData['seat'] ?? 0;
                    $period->group = $periodData['group'] ?? 0;
                    
                    $period->save();
                }
                
                echo "  ✓ Periods: {$periodCreated} created, {$periodUpdated} updated\n";
            }
            
            DB::commit();
            
            if ($isNew) {
                $created++;
            } else {
                $updated++;
            }
            
            echo "  ✓ Completed\n\n";
            
        } catch (\Exception $e) {
            DB::rollBack();
            $error = "Tour {$tourData['id']}: {$e->getMessage()}";
            $errors[] = $error;
            echo "  ❌ Error: {$e->getMessage()}\n";
            echo "  File: {$e->getFile()}:{$e->getLine()}\n\n";
        }
    }
    
    echo "\n=== Sync Summary ===\n";
    echo "Created: {$created} tours\n";
    echo "Updated: {$updated} tours\n";
    echo "Errors: " . count($errors) . "\n";
    
    if (!empty($errors)) {
        echo "\nError details:\n";
        foreach ($errors as $error) {
            echo "  - {$error}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
}

echo "\nDone!\n";
