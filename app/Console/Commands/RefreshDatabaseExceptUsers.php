<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefreshDatabaseExceptUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:refresh-except-users {--seed : Run seeders after refreshing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh database by truncating all tables except users and related auth tables';

    /**
     * Tables to preserve (not truncate).
     *
     * @var array<string>
     */
    protected array $preservedTables = [
        'users',
        'migrations',
        'password_reset_tokens',
        'sessions',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->confirm('This will delete all data from all tables except users. Are you sure?', true)) {
            $this->info('Operation cancelled.');

            return Command::FAILURE;
        }

        $this->info('Starting database refresh...');

        try {
            // Disable foreign key checks
            Schema::disableForeignKeyConstraints();

            // Get all tables
            $tables = $this->getAllTables();

            $truncatedCount = 0;

            foreach ($tables as $table) {
                if (! in_array($table, $this->preservedTables, true)) {
                    $this->line("Truncating table: {$table}");

                    try {
                        DB::table($table)->truncate();
                        $truncatedCount++;
                    } catch (\Exception $e) {
                        $this->warn("Could not truncate {$table}: {$e->getMessage()}");
                    }
                } else {
                    $this->line("Preserving table: {$table}");
                }
            }

            // Re-enable foreign key checks
            Schema::enableForeignKeyConstraints();

            $this->info("Successfully truncated {$truncatedCount} table(s).");

            // Run seeders if requested
            if ($this->option('seed')) {
                $this->info('Running seeders...');
                $this->call('db:seed', ['--force' => true]);
            }

            $this->info('Database refresh completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
            $this->error('Error refreshing database: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Get all table names from the database.
     *
     * @return array<string>
     */
    protected function getAllTables(): array
    {
        $databaseName = DB::getDatabaseName();
        $tables = DB::select("SHOW TABLES FROM `{$databaseName}`");
        $tableKey = "Tables_in_{$databaseName}";

        return array_map(function ($table) use ($tableKey) {
            return $table->$tableKey;
        }, $tables);
    }
}
