<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('experts')) {
            // 1. Ensure user_id column exists
            Schema::table('experts', function (Blueprint $table) {
                if (!Schema::hasColumn('experts', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('business_id');
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                    $table->index(['business_id', 'user_id']);
                }
            });

            // 2. Data sync before dropping columns
            try {
                // Backfill user_id where email matches
                if (Schema::hasColumn('experts', 'email')) {
                    DB::statement("
                        UPDATE experts e
                        INNER JOIN users u ON e.email = u.email
                        SET e.user_id = u.id
                        WHERE e.user_id IS NULL AND e.email IS NOT NULL AND e.deleted_at IS NULL
                    ");
                }

                // Copy expert_image to user profile if user has no profile photo
                if (Schema::hasColumn('experts', 'expert_image')) {
                    DB::statement("
                        UPDATE users u
                        INNER JOIN experts e ON e.user_id = u.id
                        SET u.profile = e.expert_image
                        WHERE (u.profile IS NULL OR u.profile = '') AND e.expert_image IS NOT NULL AND e.expert_image != ''
                    ");
                }

                // Sync expert_name into users first_name/last_name where user first_name is empty
                if (Schema::hasColumn('experts', 'expert_name')) {
                    $experts = DB::table('experts')
                        ->whereNotNull('user_id')
                        ->whereNotNull('expert_name')
                        ->get(['user_id', 'expert_name']);

                    foreach ($experts as $exp) {
                        $user = DB::table('users')->where('id', $exp->user_id)->first();
                        if ($user && (empty($user->first_name) || $user->first_name === 'Expert')) {
                            $parts = explode(' ', trim($exp->expert_name), 2);
                            $firstName = mb_substr($parts[0], 0, 50);
                            $lastName = isset($parts[1]) && trim($parts[1]) !== '' ? mb_substr(trim($parts[1]), 0, 50) : '.';
                            DB::table('users')->where('id', $user->id)->update([
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                            ]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore if tables are empty or query syntax differs
            }

            // 3. Drop email, password, expert_image, and expert_name from experts table
            Schema::table('experts', function (Blueprint $table) {
                if (Schema::hasColumn('experts', 'email')) {
                    try {
                        $table->dropUnique('experts_email_unique');
                    } catch (\Throwable $e) {
                        // Index might have already been dropped or named differently
                    }
                    $table->dropColumn('email');
                }

                if (Schema::hasColumn('experts', 'password')) {
                    $table->dropColumn('password');
                }

                if (Schema::hasColumn('experts', 'expert_image')) {
                    $table->dropColumn('expert_image');
                }

                if (Schema::hasColumn('experts', 'expert_name')) {
                    $table->dropColumn('expert_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('experts')) {
            Schema::table('experts', function (Blueprint $table) {
                if (!Schema::hasColumn('experts', 'expert_name')) {
                    $table->string('expert_name')->nullable()->after('department_id');
                }
                if (!Schema::hasColumn('experts', 'email')) {
                    $table->string('email')->nullable()->after('expert_name');
                }
                if (!Schema::hasColumn('experts', 'expert_image')) {
                    $table->string('expert_image')->nullable()->after('email');
                }
                if (!Schema::hasColumn('experts', 'password')) {
                    $table->string('password')->nullable()->after('is_need_booking_confirmation');
                }
            });
        }
    }
};
