<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiFieldMappingModel;

class FixBestConsortiumMappings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:best-consortium-mappings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Best Consortium API field mappings';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $provider = ApiProviderModel::where('code', 'BESTCONSORTIUM')->first();
        
        if (!$provider) {
            $this->error('❌ Best Consortium provider not found');
            return 1;
        }
        
        $this->info("=== Best Consortium Provider Info ===");
        $this->info("ID: {$provider->id}");
        $this->info("Name: {$provider->name}");
        $this->info("Code: {$provider->code}");
        $this->info("URL: {$provider->url}");
        $this->newLine();

        $this->info("=== Current Field Mappings ===");
        $mappings = $provider->fieldMappings()->get();
        
        if ($mappings->isEmpty()) {
            $this->warn("❌ No field mappings found!");
            $this->newLine();
            
            $this->info("🔧 Creating basic field mappings...");
            
            // Create API ID mapping
            ApiFieldMappingModel::create([
                'api_provider_id' => $provider->id,
                'field_type' => 'tour',
                'local_field' => 'api_id',
                'api_field' => 'id',
                'data_type' => 'string',
                'is_required' => true
            ]);
            
            // Create basic tour mappings
            $basicMappings = [
                ['local_field' => 'code', 'api_field' => 'code', 'data_type' => 'string'],
                ['local_field' => 'name', 'api_field' => 'nameEng', 'data_type' => 'string'],
                ['local_field' => 'name_th', 'api_field' => 'nameTh', 'data_type' => 'string'],
                ['local_field' => 'category', 'api_field' => 'category', 'data_type' => 'string']
            ];
            
            foreach ($basicMappings as $mapping) {
                ApiFieldMappingModel::create([
                    'api_provider_id' => $provider->id,
                    'field_type' => 'tour',
                    'local_field' => $mapping['local_field'],
                    'api_field' => $mapping['api_field'],
                    'data_type' => $mapping['data_type'],
                    'is_required' => false
                ]);
            }
            
            $this->info("✅ Basic field mappings created!");
            $this->newLine();
            
        } else {
            foreach ($mappings as $mapping) {
                $this->info("- {$mapping->field_type}: {$mapping->local_field} -> {$mapping->api_field} ({$mapping->data_type})");
            }
        }

        $this->newLine();
        $this->info("=== Fixing Field Mappings Based on Original Code ===");
        
        // Remove all existing mappings first
        $provider->fieldMappings()->delete();
        $this->info("✓ Removed all existing mappings");
        
        // Create correct mappings based on original ApiController.php
        $correctMappings = [
            // Tour fields
            ['field_type' => 'tour', 'local_field' => 'api_id', 'api_field' => 'id', 'data_type' => 'string', 'is_required' => true],
            ['field_type' => 'tour', 'local_field' => 'code1', 'api_field' => 'code', 'data_type' => 'string', 'is_required' => false],
            ['field_type' => 'tour', 'local_field' => 'name', 'api_field' => 'name', 'data_type' => 'string', 'is_required' => false],
            ['field_type' => 'tour', 'local_field' => 'image', 'api_field' => 'bannerSq', 'data_type' => 'string', 'is_required' => false],
            ['field_type' => 'tour', 'local_field' => 'pdf_file', 'api_field' => 'filePdf', 'data_type' => 'string', 'is_required' => false],
            ['field_type' => 'tour', 'local_field' => 'airline_name', 'api_field' => 'airline_name', 'data_type' => 'string', 'is_required' => false],
            
            // Period fields - based on original code structure
            ['field_type' => 'period', 'local_field' => 'periods', 'api_field' => 'period', 'data_type' => 'json', 'is_required' => false],
            ['field_type' => 'period', 'local_field' => 'period_api_id', 'api_field' => 'pid', 'data_type' => 'string', 'is_required' => false],
            ['field_type' => 'period', 'local_field' => 'price1', 'api_field' => 'adultPrice', 'data_type' => 'decimal', 'is_required' => false],
            ['field_type' => 'period', 'local_field' => 'price2', 'api_field' => 'singlePrice', 'data_type' => 'decimal', 'is_required' => false],
            ['field_type' => 'period', 'local_field' => 'price3', 'api_field' => 'childWbPrice', 'data_type' => 'decimal', 'is_required' => false],
            ['field_type' => 'period', 'local_field' => 'price4', 'api_field' => 'childNbPrice', 'data_type' => 'decimal', 'is_required' => false],
            ['field_type' => 'period', 'local_field' => 'special_price1', 'api_field' => 'adultPrice_old', 'data_type' => 'decimal', 'is_required' => false],
            ['field_type' => 'period', 'local_field' => 'start_date', 'api_field' => 'dateGo', 'data_type' => 'date', 'is_required' => false],
            ['field_type' => 'period', 'local_field' => 'end_date', 'api_field' => 'dateBack', 'data_type' => 'date', 'is_required' => false],
            ['field_type' => 'period', 'local_field' => 'group', 'api_field' => 'groupSize', 'data_type' => 'integer', 'is_required' => false],
            ['field_type' => 'period', 'local_field' => 'count', 'api_field' => 'avbl', 'data_type' => 'string', 'is_required' => false],
        ];
        
        foreach ($correctMappings as $mapping) {
            ApiFieldMappingModel::create([
                'api_provider_id' => $provider->id,
                'field_type' => $mapping['field_type'],
                'local_field' => $mapping['local_field'],
                'api_field' => $mapping['api_field'],
                'data_type' => $mapping['data_type'],
                'is_required' => $mapping['is_required'] ?? false
            ]);
        }
        
        $this->info("✅ Created correct field mappings based on original code");

        $this->newLine();
        $this->info("=== Final Mappings Check ===");
        $mappings = $provider->fieldMappings()->get();
        foreach ($mappings as $mapping) {
            $this->info("✓ {$mapping->field_type}: {$mapping->local_field} -> {$mapping->api_field} ({$mapping->data_type})");
        }
        
        return 0;
    }
}
