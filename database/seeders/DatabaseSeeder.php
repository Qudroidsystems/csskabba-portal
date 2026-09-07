<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Track which seeders have been run
     */
    protected array $runSeeders = [];

    /**
     * Seed the application's database.
     *
     * This seeder runs all seeders in the correct order with detailed progress reporting.
     */
    public function run(): void
    {
        // Load already run seeders from the database
        $this->loadRunSeeders();

        // Start timing the seeding process
        $startTime = microtime(true);
        $seededCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        $this->command->info('');
        $this->command->info('╔═══════════════════════════════════════════════════════════════════════════════╗');
        $this->command->info('║                         🚀 DATABASE SEEDING PROCESS                          ║');
        $this->command->info('║                         Starting at: ' . now()->format('Y-m-d H:i:s') . '                         ║');
        $this->command->info('╚═══════════════════════════════════════════════════════════════════════════════╝');
        $this->command->info('');

        // ============================================
        // PART 1: CORE PERMISSIONS & FOUNDATION
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 🔐 PART 1: CORE PERMISSIONS & FOUNDATION                                   │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $result = $this->safeCall(PermissionTableSeeder::class, 'PermissionTableSeeder', '🔐 Seeding permission tables...');
        $this->updateStats($result, $seededCount, $failedCount, $skippedCount);

        $result = $this->safeCall(UserTableSeeder::class, 'UserTableSeeder', '👤 Seeding user data...');
        $this->updateStats($result, $seededCount, $failedCount, $skippedCount);

        $result = $this->safeCall(TermTableSeeder::class, 'TermTableSeeder', '📅 Seeding term data...');
        $this->updateStats($result, $seededCount, $failedCount, $skippedCount);

        $this->command->info('');

        // ============================================
        // PART 2: ACADEMIC PERMISSIONS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 🎓 PART 2: ACADEMIC PERMISSIONS                                            │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $academicSeeders = [
            'ViewClassPermissionTableSeeder' => '  📖 Seeding class view permissions...',
            'CompulsorySubjectsPermissionTableSeeder' => '  📚 Seeding compulsory subjects permissions...',
            'MockSubjectVettingsPermissionTableSeeder' => '  ✏️ Seeding mock subject vettings permissions...',
            'MyClassMySubjectPermissionTableSeeder' => '  🏫 Seeding my class/subject permissions...',
            'MyMockSubjectVettingsPermissionTableSeeder' => '  📝 Seeding my mock subject vettings...',
            'MySubjectVettingsPermissionTableSeeder' => '  📋 Seeding my subject vettings...',
            'PrincipalscommentPermissionTableSeeder' => '  👔 Seeding principal comments permissions...',
            'SchoolInformationPermissionTableSeeder' => '  🏢 Seeding school information permissions...',
            'StudentMockReportPermissionTableSeeder' => '  📊 Seeding student mock report permissions...',
            'StudentPermissionTableSeeder' => '  👨‍🎓 Seeding student permissions...',
            'StudentReportPermissionTableSeeder' => '  📈 Seeding student report permissions...',
            'StudentStatusTableSeeder' => '  🏷️ Seeding student status data...',
            'SubjectClassResultRoomOperationPermissionTableSeeder' => '  🔬 Seeding subject class result permissions...',
            'SubjectUploadForStaffPermissionTableSeeder' => '  💾 Seeding subject upload permissions...',
            'SubjectVettedPermissionTableSeeder' => '  ✅ Seeding subject vetted permissions...',
            'SubjectVettingsPermissionTableSeeder' => '  🔍 Seeding subject vettings...',
            'StudentAssessmentPermissionTableSeeder' => '  📝 Seeding student assessment permissions...',
            'IdCardPermissionSeeder' => '  🪪 Seeding student ID Card permissions...',
            'AdminScoreEntryPermissionSeeder' => '  📝 Seeding Admin score entry...',
        ];

        foreach ($academicSeeders as $seeder => $message) {
            $result = $this->safeCall($seeder, $seeder, $message);
            $this->updateStats($result, $seededCount, $failedCount, $skippedCount);
        }

        $this->command->info('');

        // ============================================
        // PART 3: EXAM & ASSESSMENT PERMISSIONS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 📝 PART 3: EXAM & ASSESSMENT PERMISSIONS                                   │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $examSeeders = [
            'ExamPermissionTableSeeder' => '  📋 Seeding exam permissions...',
            'QuestionPermissionTableSeeder' => '  ❓ Seeding question permissions...',
            'CBTExamPermissionTableSeeder' => '  💻 Seeding CBT exam permissions...',
        ];

        foreach ($examSeeders as $seeder => $message) {
            $result = $this->safeCall($seeder, $seeder, $message);
            $this->updateStats($result, $seededCount, $failedCount, $skippedCount);
        }

        $this->command->info('');

        // ============================================
        // PART 4: PARENT PORTAL PERMISSIONS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 👨‍👩‍👧‍👦 PART 4: PARENT PORTAL PERMISSIONS                                      │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $result = $this->safeCall(ParentPermissionTableSeeder::class, 'ParentPermissionTableSeeder', '  👪 Seeding parent portal permissions...');
        $this->updateStats($result, $seededCount, $failedCount, $skippedCount);

        $this->command->info('');

        // ============================================
        // PART 5: TIMETABLE & SCHEDULING PERMISSIONS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 🕐 PART 5: TIMETABLE & SCHEDULING PERMISSIONS                              │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $timetableSeeders = [
            'TimetablePermissionTableSeeder' => '  📅 Seeding timetable permissions...',
            'RoomPermissionTableSeeder' => '  🚪 Seeding room permissions...',
            'HolidayPermissionTableSeeder' => '  🎉 Seeding holiday permissions...',
            'ExamTimetablePermissionTableSeeder' => '  📋 Seeding exam timetable permissions...',
            'TimetableReportsPermissionTableSeeder' => '  📊 Seeding timetable reports permissions...',
        ];

        foreach ($timetableSeeders as $seeder => $message) {
            $result = $this->safeCall($seeder, $seeder, $message);
            $this->updateStats($result, $seededCount, $failedCount, $skippedCount);
        }

        $this->command->info('');

        // ============================================
        // PART 6: PROMOTION & SCHOOL BILL PERMISSIONS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 🎯 PART 6: PROMOTION & SCHOOL BILL PERMISSIONS                             │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $result = $this->safeCall(PromotionPermissionTableSeeder::class, 'PromotionPermissionTableSeeder', '  🚀 Seeding promotion permissions...');
        $this->updateStats($result, $seededCount, $failedCount, $skippedCount);

        $result = $this->safeCall(SchoolBillTermSessionPermissionTableSeeder::class, 'SchoolBillTermSessionPermissionTableSeeder', '  💰 Seeding school bill term session permissions...');
        $this->updateStats($result, $seededCount, $failedCount, $skippedCount);

        $this->command->info('');

        // ============================================
        // PART 7: FINANCE PERMISSIONS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 💰 PART 7: FINANCE PERMISSIONS                                             │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $financePermissionSeeders = [
            'ScholarshipPermissionSeeder' => '  🎓 Seeding scholarship permissions...',
            'FinancePermissionSeeder' => '  💵 Seeding finance permissions...',
            'SiblingGroupPermissionSeeder' => '  👨‍👩‍👧 Seeding sibling group permissions...',
            'StudentPaymentPermissionTableSeeder' => '  💳 Seeding student payment permissions...',
            'FinancialReportPermissionSeeder' => '  📊 Seeding financial report permissions...',
            'PayrollPermissionSeeder' => '  💰 Seeding payroll permissions...',
            'StaffPaymentPermissionSeeder' => '  👨‍🏫 Seeding staff payment permissions...',
            'SchoolPaymentPermissionTableSeeder' => '  🏫 Seeding school payment permissions...',
            'AllFinancePermissionsSeeder' => '  💰 Seeding all finance permissions...',
            'StaffAttendancePermissionTableSeeder' => '  📋 Seeding staff attendance permissions...',
        ];

        foreach ($financePermissionSeeders as $seeder => $message) {
            $result = $this->safeCall($seeder, $seeder, $message);
            $this->updateStats($result, $seededCount, $failedCount, $skippedCount);
        }

        $this->command->info('');

        // ============================================
        // PART 8: ANALYSIS & TRANSCRIPT PERMISSIONS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 📊 PART 8: ANALYSIS & TRANSCRIPT PERMISSIONS                               │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $analysisSeeders = [
            'AnalysisPermissionTableSeeder' => '  📊 Seeding analysis permissions...',
            'TranscriptPermissionTableSeeder' => '  📄 Seeding transcript permissions...',
            'MyPrincipalsCommentPermissionTableSeeder' => '  👔 Seeding my principals comment permissions...',
            'AdminStudentResultManagerPermissionSeeder' => '  📝 Seeding admin student result manager...',
        ];

        foreach ($analysisSeeders as $seeder => $message) {
            $result = $this->safeCall($seeder, $seeder, $message);
            $this->updateStats($result, $seededCount, $failedCount, $skippedCount);
        }

        $this->command->info('');

        // ============================================
        // PART 9: UPDATED PERMISSION SEEDERS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 🔄 PART 9: UPDATED PERMISSION SEEDERS                                      │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $updatedSeeders = [
            'UpdatedAttendancePermissionTableSeeder' => '  📋 Seeding updated attendance permissions...',
            'UpdatedFinancialReportPermissionTableSeeder' => '  📊 Seeding updated financial report permissions...',
            'UpdatedScholarshipPermissionTableSeeder' => '  🎓 Seeding updated scholarship permissions...',
            'UpdatedAdminScoreEntryPermissionTableSeeder' => '  📝 Seeding updated admin score entry permissions...',
            'UpdatedPromotionPermissionTableSeeder' => '  🚀 Seeding updated promotion permissions...',
            'UpdatedTranscriptPermissionTableSeeder' => '  📄 Seeding updated transcript permissions...',
            'UpdatedFinancePermissionTableSeeder' => '  💰 Seeding updated finance permissions...',
            'UpdatedTimetablePermissionTableSeeder' => '  📅 Seeding updated timetable permissions...',
        ];

        foreach ($updatedSeeders as $seeder => $message) {
            $result = $this->safeCall($seeder, $seeder, $message);
            $this->updateStats($result, $seededCount, $failedCount, $skippedCount);
        }

        $this->command->info('');

        // ============================================
        // PART 10: ATTENDANCE PERMISSIONS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 📋 PART 10: ATTENDANCE PERMISSIONS                                         │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $result = $this->safeCall(AttendancePermissionTableSeeder::class, 'AttendancePermissionTableSeeder', '  📋 Seeding attendance permissions...');
        $this->updateStats($result, $seededCount, $failedCount, $skippedCount);

        $this->command->info('');

        // ============================================
        // PART 11: FINANCE LOOKUP & REFERENCE DATA
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 📚 PART 11: FINANCE LOOKUP & REFERENCE DATA                                 │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $financeLookupSeeders = [
            'ScholarshipTypeSeeder' => '  🎓 Seeding scholarship types...',
            'DiscountTypeSeeder' => '  🏷️ Seeding discount types...',
            'ChartOfAccountsSeeder' => '  📊 Seeding chart of accounts...',
            'ExpenseCategorySeeder' => '  💸 Seeding expense categories...',
            'PaymentMethodSeeder' => '  💳 Seeding payment methods...',
            'BillCategorySeeder' => '  📋 Seeding bill categories...',
        ];

        foreach ($financeLookupSeeders as $seeder => $message) {
            $result = $this->safeCall($seeder, $seeder, $message);
            $this->updateStats($result, $seededCount, $failedCount, $skippedCount);
        }

        $this->command->info('');

        // ============================================
        // PART 12: PAYMENT GATEWAYS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 🌐 PART 12: PAYMENT GATEWAYS                                              │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $result = $this->safeCall(DefaultPaymentGatewaysSeeder::class, 'DefaultPaymentGatewaysSeeder', '  🌐 Seeding default payment gateways...');
        $this->updateStats($result, $seededCount, $failedCount, $skippedCount);

        $this->command->info('');

        // ============================================
        // PART 13: DEMO/TEST DATA (DEVELOPMENT ONLY)
        // ============================================
        if (app()->environment('local', 'development')) {
            $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
            $this->command->info('│ 🧪 PART 13: DEMO & TEST DATA (Development Environment)                    │');
            $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
            $this->command->info('');

            $this->command->warn('  ⚠️  Running in DEVELOPMENT mode - seeding demo data...');
            $this->command->info('');

            $demoSeeders = [
                // Uncomment these when you create the demo seeders
                // 'DemoScholarshipSeeder' => '  🎓 Seeding demo scholarships...',
                // 'DemoDiscountSeeder' => '  🏷️ Seeding demo discounts...',
                // 'DemoStudentPaymentsSeeder' => '  💰 Seeding demo payments...',
                // 'DemoUsersSeeder' => '  👥 Seeding demo users...',
                // 'DemoStudentsSeeder' => '  👨‍🎓 Seeding demo students...',
            ];

            foreach ($demoSeeders as $seeder => $message) {
                $result = $this->safeCall($seeder, $seeder, $message);
                $this->updateStats($result, $seededCount, $failedCount, $skippedCount);
            }

            $this->command->info('');
        } else {
            $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
            $this->command->info('│ 🚀 PART 13: PRODUCTION ENVIRONMENT                                        │');
            $this->command->info('│    Skipping demo data - only seeding essential data                       │');
            $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
            $this->command->info('');
            $skippedCount += count($this->getDemoSeeders());
        }

        // ============================================
        // DATABASE STATISTICS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 📊 DATABASE STATISTICS                                                     │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $this->showDatabaseStats();

        $this->command->info('');

        // ============================================
        // COMPLETION SUMMARY
        // ============================================
        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        $this->command->info('');
        $this->command->info('╔═══════════════════════════════════════════════════════════════════════════════╗');
        $this->command->info('║                         ✅ SEEDING COMPLETED                                 ║');
        $this->command->info('╠═══════════════════════════════════════════════════════════════════════════════╣');
        $this->command->info('║  📊 Total Seeders Executed: ' . str_pad($seededCount, 45, ' ', STR_PAD_RIGHT) . '║');
        if ($skippedCount > 0) {
            $this->command->info('║  ⏭️  Seeders Skipped: ' . str_pad($skippedCount, 49, ' ', STR_PAD_RIGHT) . '║');
        }
        if ($failedCount > 0) {
            $this->command->info('║  ❌ Failed Seeders: ' . str_pad($failedCount, 49, ' ', STR_PAD_RIGHT) . '║');
        } else {
            $this->command->info('║  ✅ All Seeders Executed Successfully! ' . str_pad('', 30, ' ', STR_PAD_RIGHT) . '║');
        }
        $this->command->info('║  ⏱️  Execution Time: ' . str_pad($executionTime . ' seconds', 45, ' ', STR_PAD_RIGHT) . '║');
        $this->command->info('║  🕐 Completed at: ' . str_pad(now()->format('Y-m-d H:i:s'), 45, ' ', STR_PAD_RIGHT) . '║');
        $this->command->info('╚═══════════════════════════════════════════════════════════════════════════════╝');
        $this->command->info('');

        if ($failedCount > 0) {
            $this->command->warn('⚠️  Some seeders failed. Please check the errors above and fix them.');
            $this->command->warn('💡 Tip: Run "php artisan migrate:fresh --seed" to start over if needed.');
            $this->command->warn('💡 Tip: Run "php artisan db:seed --force" to force seed in production.');
        } else {
            $this->command->info('🎉 Database seeding completed successfully!');
            $this->command->info('💡 You can now run "php artisan serve" to start the application.');
            $this->command->info('💡 Default admin credentials: admin@example.com / password');
        }
    }

    /**
     * Load previously run seeders from the database
     */
    protected function loadRunSeeders(): void
    {
        $this->runSeeders = [];

        // Check if the seeder_log table exists
        if (!Schema::hasTable('seeder_log')) {
            // Create the seeder_log table if it doesn't exist
            $this->createSeederLogTable();
            return;
        }

        try {
            $this->runSeeders = DB::table('seeder_log')
                ->where('completed_at', '!=', null)
                ->pluck('seeder_name')
                ->toArray();
        } catch (\Exception $e) {
            // Table might not exist or be accessible
            Log::warning('Could not load seeder log: ' . $e->getMessage());
        }
    }

    /**
     * Create the seeder_log table if it doesn't exist
     */
    protected function createSeederLogTable(): void
    {
        Schema::create('seeder_log', function ($table) {
            $table->id();
            $table->string('seeder_name')->unique();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Check if a seeder has already been run successfully
     */
    protected function hasBeenRun(string $seederName): bool
    {
        return in_array($seederName, $this->runSeeders);
    }

    /**
     * Log that a seeder has been run
     */
    protected function logSeederRun(string $seederName, bool $success, ?string $error = null): void
    {
        try {
            DB::table('seeder_log')->updateOrInsert(
                ['seeder_name' => $seederName],
                [
                    'seeder_name' => $seederName,
                    'started_at' => now(),
                    'completed_at' => $success ? now() : null,
                    'success' => $success,
                    'error_message' => $error,
                    'updated_at' => now(),
                ]
            );

            if ($success && !in_array($seederName, $this->runSeeders)) {
                $this->runSeeders[] = $seederName;
            }
        } catch (\Exception $e) {
            // Silently fail - we don't want logging to break seeding
        }
    }

    /**
     * Update statistics
     */
    protected function updateStats(array $result, int &$seededCount, int &$failedCount, int &$skippedCount): void
    {
        if (isset($result['skipped']) && $result['skipped']) {
            $skippedCount++;
        } elseif ($result['success']) {
            $seededCount++;
        } else {
            $failedCount++;
        }
    }

    /**
     * Safely call a seeder with error handling, progress indicator, and skip if already run
     */
    protected function safeCall($seeder, $name, $message = null): array
    {
        if ($message) {
            $this->command->getOutput()->write($message);
        }

        // Check if seeder class exists
        if (!class_exists($seeder)) {
            $this->command->getOutput()->write("\r\033[K");
            $this->command->warn("  ⚠️  Seeder not found: {$name} - skipping");
            return ['success' => false, 'skipped' => true];
        }

        // Check if seeder already exists in the log and was successful
        if ($this->hasBeenRun($seeder)) {
            $this->command->getOutput()->write("\r\033[K");
            $this->command->info("  ⏭️  {$name} already run - skipping");
            return ['success' => true, 'skipped' => true];
        }

        try {
            $this->call($seeder);
            $this->logSeederRun($seeder, true);

            if ($message) {
                $this->command->getOutput()->write("\r\033[K");
                $this->command->info("  ✅ {$name} completed successfully!");
            }
            return ['success' => true];
        } catch (\Exception $e) {
            if ($message) {
                $this->command->getOutput()->write("\r\033[K");
                $this->command->error("  ❌ {$name} failed: " . $e->getMessage());
            }
            Log::error("Seeder failed: {$name} - " . $e->getMessage());
            $this->logSeederRun($seeder, false, $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Display database statistics
     */
    protected function showDatabaseStats(): void
    {
        $tables = [
            'users' => '👤 Users',
            'studentRegistration' => '👨‍🎓 Students',
            'staff_records' => '👨‍🏫 Staff',
            'school_bill' => '💰 School Bills',
            'scholarships' => '🎓 Scholarships',
            'scholarship_assignments' => '📋 Scholarship Assignments',
            'discounts' => '🏷️ Discounts',
            'discount_assignments' => '📋 Discount Assignments',
            'payment_batches' => '💵 Payment Batches',
            'student_bill_payment' => '💳 Student Payments',
            'chart_of_accounts' => '📊 Chart of Accounts',
            'expense_categories' => '💸 Expense Categories',
            'payment_gateways' => '🌐 Payment Gateways',
            'schoolterm' => '📅 Terms',
            'schoolsession' => '📅 Sessions',
            'schoolclass' => '🏫 Classes',
        ];

        $stats = [];
        foreach ($tables as $table => $label) {
            if (Schema::hasTable($table)) {
                try {
                    $count = DB::table($table)->count();
                    if ($count > 0) {
                        $stats[] = "  {$label}: " . number_format($count);
                    }
                } catch (\Exception $e) {
                    // Table exists but might not be accessible
                }
            }
        }

        if (count($stats) > 0) {
            $this->command->info(implode("\n", $stats));
        } else {
            $this->command->info('  ℹ️  No data found in tables yet.');
        }
    }

    /**
     * Get list of demo seeders (for development environment)
     */
    protected function getDemoSeeders(): array
    {
        return [
            'DemoScholarshipSeeder',
            'DemoDiscountSeeder',
            'DemoStudentPaymentsSeeder',
            'DemoUsersSeeder',
            'DemoStudentsSeeder',
        ];
    }
}