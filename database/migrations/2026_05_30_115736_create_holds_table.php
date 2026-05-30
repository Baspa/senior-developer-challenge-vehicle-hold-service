<?php

use App\Enums\HoldStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('buyer_ref');
            $table->string('status')->default(HoldStatus::Active->value);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['status', 'expires_at']);
        });

        // Partial unique index for active holds per vehicle (only on databases that support it, like PostgreSQL / SQLite)
        if (!in_array(DB::connection()->getDriverName(), ['pgsql', 'sqlite'], true)) {
            Log::warning(
                'Database driver does not support partial indexes; uniqueness of active holds per vehicle must be enforced in the application layer.'
            );

            return;
        }

        DB::statement(
            "CREATE UNIQUE INDEX holds_one_active_per_vehicle ON holds (vehicle_id) WHERE status = 'active'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};
