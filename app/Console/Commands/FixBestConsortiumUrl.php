<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;

class FixBestConsortiumUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:best-consortium-url';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix Best Consortium API URL to get tour data instead of country data';

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
        
        $this->info("=== Current Best Consortium URL ===");
        $this->info("Old URL: {$provider->url}");
        $this->newLine();
        
        // Update to the correct tour API endpoint (Myanmar example)
        $newUrl = 'https://tour-api.bestinternational.com/api/tour-programs/v2/6';
        $provider->url = $newUrl;
        $provider->save();
        
        $this->info("✅ Updated Best Consortium URL");
        $this->info("New URL: {$provider->url}");
        $this->newLine();
        
        $this->info("📝 Note: This URL will return actual tour data instead of just country data");
        $this->info("Country ID 6 = Myanmar tours");
        $this->info("You can change to other country IDs:");
        $this->info("- 7 = Vietnam");  
        $this->info("- 8 = Laos");
        $this->info("- 9 = Hong Kong");
        $this->info("- 11 = Taiwan");
        
        return 0;
    }
}
