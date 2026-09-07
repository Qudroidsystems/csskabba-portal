<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if the table already exists before creating it
        if (!Schema::hasTable('timetable_settings')) {
            Schema::create('timetable_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schoolclass_id');
                $table->unsignedBigInteger('session_id');
                $table->unsignedBigInteger('term_id')->nullable();

                $table->time('school_day_start')->default('08:00:00');
                $table->time('school_day_end')->default('14:30:00');
                $table->unsignedSmallInteger('period_duration_minutes')->default(40);
                $table->unsignedSmallInteger('short_break_duration_minutes')->default(20);
                $table->unsignedSmallInteger('long_break_duration_minutes')->default(40);
                $table->boolean('is_active')->default(true);
                $table->json('active_days')->nullable();
                $table->timestamps();

                $table->index(['schoolclass_id', 'session_id', 'term_id']);
                
                // Only add foreign keys if the referenced tables exist
                if (Schema::hasTable('schoolclass')) {
                    $table->foreign('schoolclass_id')->references('id')->on('schoolclass')->onDelete('cascade');
                }
                
                if (Schema::hasTable('schoolsession')) {
                    $table->foreign('session_id')->references('id')->on('schoolsession')->onDelete('cascade');
                }
                
                if (Schema::hasTable('schoolterm')) {
                    $table->foreign('term_id')->references('id')->on('schoolterm')->onDelete('cascade');
                }
            });
        } else {
            // Log or output that table already exists
            $this->command->info('Table "timetable_settings" already exists. Skipping creation.');
        }
        
        // Now try to add the foreign key constraints to other tables
        $this->addForeignKeysToDependentTables();
    }

    public function down(): void
    {
        // Only drop if it exists
        if (Schema::hasTable('timetable_settings')) {
            Schema::dropIfExists('timetable_settings');
        }
    }
    
    /**
     * Add foreign keys to all dependent tables
     */
    private function addForeignKeysToDependentTables(): void
    {
        // Only proceed if timetable_settings exists
        if (!Schema::hasTable('timetable_settings')) {
            return;
        }
        
        $dependentTables = [
            'timetable_constraints' => 'setting_id',
            'timetable_periods' => 'setting_id',
            'timetable_slots' => 'setting_id',
            'timetable_overrides' => 'setting_id',
            'timetable_change_requests' => 'setting_id', // if this table uses it
        ];
        
        foreach ($dependentTables as $tableName => $columnName) {
            if (Schema::hasTable($tableName)) {
                try {
                    // Check if the column exists
                    if (Schema::hasColumn($tableName, $columnName)) {
                        // Check if the foreign key already exists
                        $foreignKeyName = $tableName . '_' . $columnName . '_foreign';
                        $foreignKeys = $this->getTableForeignKeys($tableName);
                        
                        if (!in_array($foreignKeyName, $foreignKeys)) {
                            Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                                $table->foreign($columnName)
                                      ->references('id')
                                      ->on('timetable_settings')
                                      ->onDelete('cascade');
                            });
                            
                            $this->command->info("Added foreign key for {$tableName}.{$columnName}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->command->warn("Could not add foreign key to {$tableName}: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Get all foreign key names for a table
     */
    private function getTableForeignKeys(string $tableName): array
    {
        $database = config('database.connections.mysql.database');
        $connection = config('database.connections.mysql.database');
        
        try {
            $results = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$database, $tableName]);
            
            return array_map(function ($row) {
                return $row->CONSTRAINT_NAME;
            }, $results);
        } catch (\Exception $e) {
            return [];
        }
    }
};