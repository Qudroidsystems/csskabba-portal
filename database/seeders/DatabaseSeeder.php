<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This seeder runs all seeders in the correct order with detailed progress reporting.
     *
     * Order of execution:
     * 1. Core Permissions - Foundation for all access control
     * 2. User & Role Management - Base user data
     * 3. Academic Structure - Terms, classes, subjects
     * 4. Academic Permissions - Teacher, student, exam permissions
     * 5. Parent Portal Permissions
     * 6. Timetable & Scheduling Permissions
     * 7. Promotion & School Bill Permissions
     * 8. Finance Permissions
     * 9. Finance Lookup & Reference Data
     * 10. Payment Gateways
     * 11. Demo/Local Data (Development Only)
     */
    public function run(): void
    {
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
        if ($result['success']) { $seededCount++; } else { $failedCount++; }

        // RoleTableSeeder is commented - keeping as is from original
        // $this->call(RoleTableSeeder::class);

        $result = $this->safeCall(UserTableSeeder::class, 'UserTableSeeder', '👤 Seeding user data...');
        if ($result['success']) { $seededCount++; } else { $failedCount++; }

        $result = $this->safeCall(TermTableSeeder::class, 'TermTableSeeder', '📅 Seeding term data...');
        if ($result['success']) { $seededCount++; } else { $failedCount++; }

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
            'IdCardPermissionTableSeeder' => '  📝 Seeding student ID Card permissions...',
            'AdminScoreEntryPermissionSeeder' => '  📝 Seeding Admin score entry...',

        ];

        foreach ($academicSeeders as $seeder => $message) {
            $result = $this->safeCall($seeder, $seeder, $message);
            if ($result['success']) { $seededCount++; } else { $failedCount++; }
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
            if ($result['success']) { $seededCount++; } else { $failedCount++; }
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
        if ($result['success']) { $seededCount++; } else { $failedCount++; }

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
            if ($result['success']) { $seededCount++; } else { $failedCount++; }
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
        if ($result['success']) { $seededCount++; } else { $failedCount++; }

        $result = $this->safeCall(SchoolBillTermSessionPermissionTableSeeder::class, 'SchoolBillTermSessionPermissionTableSeeder', '  💰 Seeding school bill term session permissions...');
        if ($result['success']) { $seededCount++; } else { $failedCount++; }

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
        ];

        foreach ($financePermissionSeeders as $seeder => $message) {
            $result = $this->safeCall($seeder, $seeder, $message);
            if ($result['success']) { $seededCount++; } else { $failedCount++; }
        }

        $this->command->info('');

        // ============================================
        // PART 8: FINANCE LOOKUP & REFERENCE DATA
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 📚 PART 8: FINANCE LOOKUP & REFERENCE DATA                                 │');
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
            if ($result['success']) { $seededCount++; } else { $failedCount++; }
        }

        $this->command->info('');

        // ============================================
        // PART 9: PAYMENT GATEWAYS
        // ============================================
        $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('│ 🌐 PART 9: PAYMENT GATEWAYS                                               │');
        $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');

        $result = $this->safeCall(DefaultPaymentGatewaysSeeder::class, 'DefaultPaymentGatewaysSeeder', '  🌐 Seeding default payment gateways...');
        if ($result['success']) { $seededCount++; } else { $failedCount++; }

        $this->command->info('');

        // ============================================
        // PART 10: DEMO/TEST DATA (DEVELOPMENT ONLY)
        // ============================================
        if (app()->environment('local', 'development')) {
            $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
            $this->command->info('│ 🧪 PART 10: DEMO & TEST DATA (Development Environment)                     │');
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
                if ($result['success']) { $seededCount++; } else { $failedCount++; }
            }

            $this->command->info('');
        } else {
            $this->command->info('┌─────────────────────────────────────────────────────────────────────────────┐');
            $this->command->info('│ 🚀 PART 10: PRODUCTION ENVIRONMENT                                        │');
            $this->command->info('│    Skipping demo data - only seeding essential data                       │');
            $this->command->info('└─────────────────────────────────────────────────────────────────────────────┘');
            $this->command->info('');
            $skippedCount = count($this->getDemoSeeders());
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
     * Safely call a seeder with error handling and progress indicator
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

        try {
            $this->call($seeder);
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
