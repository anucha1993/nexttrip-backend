<?php
// Check if ApiManagementController has the new duplicate check code
$file = file_get_contents('../app/Http/Controllers/Backend/ApiManagementController.php');

if (strpos($file, 'SIMPLE: Check if code1 already exists') !== false) {
    echo "✅ NEW CODE FOUND - Version with SIMPLE duplicate check\n";
} else {
    echo "❌ OLD CODE - Need to upload ApiManagementController.php\n";
}

if (strpos($file, 'Tour code1 after mapping') !== false) {
    echo "✅ Logging code present\n";
} else {
    echo "❌ Logging code missing\n";
}

echo "\nFile size: " . strlen($file) . " bytes\n";
echo "Last modified: " . date('Y-m-d H:i:s', filemtime('../app/Http/Controllers/Backend/ApiManagementController.php'));
