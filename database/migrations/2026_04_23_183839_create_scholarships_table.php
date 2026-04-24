<?php
// database/migrations/2024_01_01_000003_create_scholarships_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Scholarship types master table
        Schema::create('scholarship_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Full Scholarship, Merit Scholarship, Sports Scholarship, etc.
            $table->string('code', 50)->unique();
            $table->enum('type', ['full', 'percentage', 'fixed_amount', 'category_specific']);
            $table->enum('application', ['auto', 'manual'])->default('manual'); // Auto-applied or requires application
            $table->text('description')->nullable();
            $table->json('criteria')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Scholarships table
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('scholarship_no')->unique();
            $table->unsignedBigInteger('scholarship_type_id');
            $table->string('title');
            $table->text('description')->nullable();

            // Scholarship value
            $table->enum('value_type', ['percentage', 'fixed_amount']);
            $table->decimal('value', 15, 2); // e.g., 50 for 50% or 50000 for fixed amount
            $table->decimal('cap_amount', 15, 2)->nullable(); // Maximum amount if percentage-based

            // Eligibility
            $table->boolean('requires_application')->default(false);
            $table->json('eligible_classes')->nullable(); // Array of class IDs
            $table->json('eligible_status_ids')->nullable(); // Array of student status IDs
            $table->json('excluded_bill_categories')->nullable(); // Bill categories not covered

            // effective dates
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->integer('max_recipients')->nullable(); // Limit number of recipients
            $table->integer('renewal_frequency')->nullable(); // Months before renewal required

            // Financial tracking
            $table->decimal('budget_amount', 15, 2)->nullable();
            $table->decimal('utilized_amount', 15, 2)->default(0);

            // Approval workflow
            $table->enum('status', ['draft', 'active', 'expired', 'suspended'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('scholarship_type_id')->references('id')->on('scholarship_types');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->index(['effective_from', 'effective_to']);
            $table->index('status');
        });

        // Scholarship assignments to students
        Schema::create('scholarship_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scholarship_id');
            $table->unsignedBigInteger('student_id');
            $table->string('application_no')->nullable(); // If manual application
            $table->enum('status', ['pending', 'approved', 'rejected', 'active', 'expired', 'revoked']);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('renewed_at')->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            // Values at assignment time (snapshot)
            $table->enum('value_type', ['percentage', 'fixed_amount']);
            $table->decimal('value', 15, 2);
            $table->decimal('cap_amount', 15, 2)->nullable();

            // Tracking
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->unsignedBigInteger('assigned_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('scholarship_id')->references('id')->on('scholarships');
            $table->foreign('student_id')->references('id')->on('studentRegistration');
            $table->foreign('assigned_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->unique(['scholarship_id', 'student_id', 'effective_from'], 'unique_student_scholarship_period');
            $table->index(['student_id', 'status']);
        });

        // Discount types master table
        Schema::create('discount_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Early Payment, Sibling Discount, Loyalty Discount
            $table->string('code', 50)->unique();
            $table->enum('type', ['percentage', 'fixed_amount', 'per_child']);
            $table->text('description')->nullable();
            $table->json('criteria')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Discounts table
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('discount_no')->unique();
            $table->unsignedBigInteger('discount_type_id');
            $table->string('title');
            $table->text('description')->nullable();

            // Discount value
            $table->enum('value_type', ['percentage', 'fixed_amount']);
            $table->decimal('value', 15, 2);
            $table->decimal('max_amount', 15, 2)->nullable(); // Maximum discount amount if percentage

            // Eligibility
            $table->enum('applicable_to', ['all_bills', 'specific_bills', 'specific_categories']);
            $table->json('applicable_bill_ids')->nullable();
            $table->json('applicable_categories')->nullable();
            $table->json('eligible_classes')->nullable();

            // Conditions
            $table->enum('condition_type', ['none', 'early_payment', 'min_amount', 'sibling_count'])->default('none');
            $table->decimal('condition_value', 15, 2)->nullable(); // For min_amount or sibling_count
            $table->integer('days_before_due')->nullable(); // For early payment

            // Stacking rules
            $table->boolean('stackable_with_scholarship')->default(false);
            $table->boolean('stackable_with_other_discounts')->default(false);
            $table->integer('stacking_priority')->default(1); // Higher priority applies first

            // effective dates
            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->enum('status', ['draft', 'active', 'expired', 'suspended'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('discount_type_id')->references('id')->on('discount_types');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
        });

        // Discount assignments to students
        Schema::create('discount_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discount_id');
            $table->unsignedBigInteger('student_id');

            // Values at assignment time
            $table->enum('value_type', ['percentage', 'fixed_amount']);
            $table->decimal('value', 15, 2);
            $table->decimal('max_amount', 15, 2)->nullable();

            // Sibling discount specific fields
            $table->unsignedBigInteger('sibling_group_id')->nullable(); // For sibling discounts
            $table->integer('sibling_count')->nullable();
            $table->decimal('per_child_discount', 15, 2)->nullable();

            $table->enum('status', ['active', 'expired', 'removed']);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('assigned_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('discount_id')->references('id')->on('discounts');
            $table->foreign('student_id')->references('id')->on('studentRegistration');
            $table->foreign('assigned_by')->references('id')->on('users');
            $table->foreign('sibling_group_id')->references('id')->on('sibling_groups');
            $table->index(['student_id', 'status']);
        });

        // Sibling groups for family discounts
        Schema::create('sibling_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_no')->unique();
            $table->string('family_name');
            $table->string('parent_phone')->nullable();
            $table->string('parent_email')->nullable();
            $table->text('address')->nullable();
            $table->integer('total_children')->default(0);
            $table->enum('discount_type', ['percentage', 'fixed_per_child'])->nullable();
            $table->decimal('discount_value', 15, 2)->nullable();
            $table->unsignedBigInteger('primary_contact_id')->nullable(); // Parent/Guardian user ID
            $table->timestamps();
        });

        // Add student to sibling group (pivot)
        Schema::create('sibling_group_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sibling_group_id');
            $table->unsignedBigInteger('student_id');
            $table->integer('birth_order')->nullable();
            $table->timestamps();

            $table->foreign('sibling_group_id')->references('id')->on('sibling_groups')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('studentRegistration')->onDelete('cascade');
            $table->unique(['sibling_group_id', 'student_id']);
        });

        // Scholarship/Discount application history
        Schema::create('scholarship_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scholarship_id');
            $table->unsignedBigInteger('student_id');
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'revoked']);
            $table->text('motivation_letter')->nullable();
            $table->json('documents')->nullable(); // Array of uploaded document paths
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();

            $table->foreign('scholarship_id')->references('id')->on('scholarships');
            $table->foreign('student_id')->references('id')->on('studentRegistration');
            $table->foreign('reviewed_by')->references('id')->on('users');
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_applications');
        Schema::dropIfExists('sibling_group_students');
        Schema::dropIfExists('sibling_groups');
        Schema::dropIfExists('discount_assignments');
        Schema::dropIfExists('discounts');
        Schema::dropIfExists('discount_types');
        Schema::dropIfExists('scholarship_assignments');
        Schema::dropIfExists('scholarships');
        Schema::dropIfExists('scholarship_types');
    }
};
