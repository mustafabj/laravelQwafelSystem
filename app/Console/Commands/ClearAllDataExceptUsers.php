<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearAllDataExceptUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear-all-except-users {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all data from all tables except the users table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('⚠️  WARNING: This will delete ALL data from ALL tables except the users table. Are you sure you want to continue?')) {
                $this->info('Operation cancelled.');

                return Command::FAILURE;
            }
        }

        $this->info('Starting data cleanup...');

        try {
            // Get database connection
            $connection = Schema::getConnection();
            $databaseName = $connection->getDatabaseName();

            // Get all table names
            $tables = $connection->select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'", [$databaseName]);
            $tableNames = array_map(fn ($table) => $table->TABLE_NAME, $tables);

            // Exclude users table and migrations table (system tables that should be preserved)
            $excludedTables = ['users', 'migrations'];
            $tablesToClear = array_filter($tableNames, fn ($table) => ! in_array(strtolower($table), array_map('strtolower', $excludedTables)));

            if (empty($tablesToClear)) {
                $this->info('No tables to clear.');

                return Command::SUCCESS;
            }

            $this->info('Tables to be cleared: '.count($tablesToClear));
            $this->line('');

            // Disable foreign key checks
            $this->info('Disabling foreign key checks...');
            if ($connection->getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            } elseif ($connection->getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            }

            // Clear each table using DELETE (more reliable than TRUNCATE with foreign keys)
            $clearedCount = 0;
            $failedTables = [];

            $this->info('Clearing tables...');
            $this->line('');

            foreach ($tablesToClear as $table) {
                try {
                    // Use DELETE FROM which works better with foreign keys
                    $deleted = DB::table($table)->delete();
                    $this->line("✓ Cleared: {$table} ({$deleted} rows deleted)");
                    $clearedCount++;
                } catch (\Exception $e) {
                    // If DELETE fails, try TRUNCATE
                    try {
                        DB::statement("TRUNCATE TABLE `{$table}`");
                        $this->line("✓ Cleared (TRUNCATE): {$table}");
                        $clearedCount++;
                    } catch (\Exception $e2) {
                        $this->error("✗ Failed to clear {$table}: {$e2->getMessage()}");
                        $failedTables[] = $table;
                    }
                }
            }

            // Re-enable foreign key checks
            $this->line('');
            $this->info('Re-enabling foreign key checks...');
            if ($connection->getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif ($connection->getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            }

            $this->line('');
            $this->info("✅ Successfully cleared {$clearedCount} table(s).");

            if (! empty($failedTables)) {
                $this->warn('⚠️  Failed to clear '.count($failedTables).' table(s):');
                foreach ($failedTables as $table) {
                    $this->line("   - {$table}");
                }
            }

            $this->info('Users table and migrations table were preserved.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
