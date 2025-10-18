<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;

class SetupTtnJapanEndpoints extends Command
{
    protected $signature = 'ttn:setup-endpoints';
    protected $description = 'Setup TTN Japan multi-step API endpoints configuration';

    public function handle()
    {
        $this->info('🚀 Setting up TTN Japan multi-step API endpoints...');
        
        $ttnJapan = ApiProviderModel::where('code', 'ttn_japan')->first();
        
        if (!$ttnJapan) {
            $this->error('❌ TTN Japan provider not found!');
            return 1;
        }
        
        // อัปเดตการตั้งค่า
        $ttnJapan->update([
            'tour_detail_endpoint' => '/api/agency/program/{P_ID}',
            'period_endpoint' => '/api/agency/program/period/{P_ID}',
            'requires_multi_step' => true,
            'url_parameters' => [
                'tour_detail_id_field' => 'P_ID',
                'period_id_field' => 'P_ID'
            ]
        ]);
        
        $this->info('✅ TTN Japan endpoints configured successfully!');
        
        // แสดงการตั้งค่า
        $this->info("\n📋 Current Configuration:");
        $this->info("Base URL: {$ttnJapan->url}");
        $this->info("Tour Detail Endpoint: {$ttnJapan->tour_detail_endpoint}");
        $this->info("Period Endpoint: {$ttnJapan->period_endpoint}");
        $this->info("Multi-step Required: " . ($ttnJapan->requires_multi_step ? 'Yes' : 'No'));
        $this->info("URL Parameters: " . json_encode($ttnJapan->url_parameters, JSON_PRETTY_PRINT));
        
        return 0;
    }
}