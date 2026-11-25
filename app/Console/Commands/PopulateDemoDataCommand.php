<?php

namespace App\Console\Commands;

use Database\Seeders\DemoBusinessesSeeder;
use Illuminate\Console\Command;

class PopulateDemoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:populate {--fresh : Refresh database migrations before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate complete demonstration business profiles and published websites (Patil Spices & Dr. Kulkarni Ayurveda)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Populating authentic multi-business demonstration data...');

        if ($this->option('fresh')) {
            $this->warn('Running migrate:fresh...');
            $this->call('migrate:fresh');
        }

        $seeder = new DemoBusinessesSeeder();
        $seeder->run();

        $this->info('Successfully seeded:');
        $this->line('  1. Patil Traditional Spices & Foods -> /sites/patil-traditional-spices');
        $this->line('  2. Dr. Kulkarni Ayurvedic Wellness Clinic -> /sites/dr-kulkarni-ayurveda');
        $this->line('  User credentials: demo@websites.co.in / password123');

        return Command::SUCCESS;
    }
}
