<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two-tier commission overrides.
     *
     *   - `manager_commissions`     — admin-set, per (promoter manager, ticket type).
     *                                  Overrides the default TicketCommission for that
     *                                  manager. Optional.
     *   - `sub_promoter_commissions` — manager-set, per (sub-promoter, ticket type).
     *                                   Stored commission paid to the sub-promoter.
     *                                   The manager's payout is derived as
     *                                     max(0, manager_commission - sub_promoter_commission).
     *
     * Both tables mirror the shape of `ticket_commissions` (commission_amount +
     * valid_from / valid_to for time-versioning) but are scoped to a specific
     * festival_user pivot row.
     *
     * This migration also extends the `role_in_festival` enum to include
     * `promoter_manager` — a promoter who is allowed to create their own
     * sub-promoters. The plain `promoter` role means "regular promoter,
     * no sub-promoters of their own".
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE festival_user
                MODIFY COLUMN role_in_festival
                ENUM('admin', 'promoter', 'promoter_manager', 'sub_promoter') NOT NULL");
        } elseif ($driver === 'sqlite') {
            // SQLite can't drop a column that is part of an index, and a
            // CHECK constraint is bound to the column itself.  Strategy:
            // rebuild the table from scratch with the new column type.
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement('DROP INDEX IF EXISTS festival_user_unique');
            DB::statement('DROP INDEX IF EXISTS festival_user_user_id_role_in_festival_index');
            DB::statement('ALTER TABLE festival_user RENAME TO __festival_user_old');
            DB::statement(<<<SQL
                CREATE TABLE festival_user (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    festival_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    role_in_festival VARCHAR(32) NOT NULL DEFAULT 'promoter'
                        CHECK (role_in_festival IN ('admin','promoter','promoter_manager','sub_promoter')),
                    assigned_by INTEGER NULL,
                    assigned_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    FOREIGN KEY (festival_id) REFERENCES festivals(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
                )
            SQL);
            DB::statement(<<<'SQL'
                INSERT INTO festival_user
                    (id, festival_id, user_id, role_in_festival, assigned_by, assigned_at, created_at, updated_at)
                SELECT
                    id, festival_id, user_id, role_in_festival, assigned_by, assigned_at, created_at, updated_at
                FROM __festival_user_old
            SQL);
            DB::statement('DROP TABLE __festival_user_old');
            DB::statement('CREATE UNIQUE INDEX festival_user_unique ON festival_user (festival_id, user_id, role_in_festival)');
            DB::statement('CREATE INDEX festival_user_user_id_role_in_festival_index ON festival_user (user_id, role_in_festival)');
            DB::statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE festival_user DROP CONSTRAINT IF EXISTS festival_user_role_in_festival_check');
            DB::statement('ALTER TABLE festival_user ALTER COLUMN role_in_festival TYPE VARCHAR(32)');
            DB::statement('ALTER TABLE festival_user ALTER COLUMN role_in_festival SET DEFAULT \'promoter\'');
            DB::statement("ALTER TABLE festival_user ADD CONSTRAINT festival_user_role_in_festival_check
                CHECK (role_in_festival IN ('admin','promoter','promoter_manager','sub_promoter'))");
        }

        Schema::create('manager_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festival_user_id')->constrained('festival_user')->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained('ticket_types')->cascadeOnDelete();
            $table->decimal('commission_amount', 8, 2);
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_to')->nullable();
            $table->foreignId('set_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Only one active row per (manager, ticket_type) — historical rows
            // are tracked via valid_from / valid_to for time-versioning.
            $table->index(['festival_user_id', 'ticket_type_id', 'valid_from'], 'manager_commissions_lookup_idx');
        });

        Schema::create('sub_promoter_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festival_user_id')->constrained('festival_user')->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained('ticket_types')->cascadeOnDelete();
            $table->decimal('commission_amount', 8, 2);
            $table->timestamp('valid_from')->useCurrent();
            $table->timestamp('valid_to')->nullable();
            $table->foreignId('set_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['festival_user_id', 'ticket_type_id', 'valid_from'], 'sub_promoter_commissions_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_promoter_commissions');
        Schema::dropIfExists('manager_commissions');

        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE festival_user
                MODIFY COLUMN role_in_festival
                ENUM('admin', 'promoter', 'sub_promoter') NOT NULL");
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement('DROP INDEX IF EXISTS festival_user_unique');
            DB::statement('DROP INDEX IF EXISTS festival_user_user_id_role_in_festival_index');
            DB::statement('ALTER TABLE festival_user RENAME TO __festival_user_old');
            DB::statement(<<<SQL
                CREATE TABLE festival_user (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    festival_id INTEGER NOT NULL,
                    user_id INTEGER NOT NULL,
                    role_in_festival VARCHAR(32) NOT NULL DEFAULT 'promoter'
                        CHECK (role_in_festival IN ('admin','promoter','sub_promoter')),
                    assigned_by INTEGER NULL,
                    assigned_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    FOREIGN KEY (festival_id) REFERENCES festivals(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
                )
            SQL);
            DB::statement(<<<'SQL'
                INSERT INTO festival_user
                    (id, festival_id, user_id, role_in_festival, assigned_by, assigned_at, created_at, updated_at)
                SELECT
                    id, festival_id, user_id, role_in_festival, assigned_by, assigned_at, created_at, updated_at
                FROM __festival_user_old
            SQL);
            DB::statement('DROP TABLE __festival_user_old');
            DB::statement('CREATE UNIQUE INDEX festival_user_unique ON festival_user (festival_id, user_id, role_in_festival)');
            DB::statement('CREATE INDEX festival_user_user_id_role_in_festival_index ON festival_user (user_id, role_in_festival)');
            DB::statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE festival_user DROP CONSTRAINT IF EXISTS festival_user_role_in_festival_check');
            DB::statement("ALTER TABLE festival_user ADD CONSTRAINT festival_user_role_in_festival_check
                CHECK (role_in_festival IN ('admin','promoter','sub_promoter'))");
        }
    }
};
