<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Finding Field Mapping Tables ===\n\n";

try {
    $tables = DB::select('SHOW TABLES');
    
    echo "📋 All tables containing 'field' or 'mapping':\n";
    foreach ($tables as $table) {
        foreach ($table as $key => $value) {
            if (stripos($value, 'field') !== false || stripos($value, 'mapping') !== false) {
                echo "   {$value}\n";
                
                // Show columns of this table
                $columns = DB::select("SHOW COLUMNS FROM `{$value}`");
                echo "     Columns: ";
                $columnNames = array_map(function($col) { return $col->Field; }, $columns);
                echo implode(', ', $columnNames) . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}