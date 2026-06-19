<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fixes BUG-DB-001 from bugs.md:
     * The original enum had `supreme` (typo) and missed `superadmin`, yet
     * routes, the role middleware and the user model all reference
     * `superadmin`.  Expand the enum to include the values we actually use.
     *
     * Uses a driver-aware strategy:
     *   - MySQL / MariaDB: ALTER TABLE ... MODIFY COLUMN with the new ENUM
     *   - SQLite:          rebuild the column with the new CHECK constraint
     *   - PostgreSQL:      drop + recreate the check constraint
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role
                ENUM('superadmin','admin','promoter','sub_promoter','buyer')
                NOT NULL DEFAULT 'buyer'");
            return;
        }

        if ($driver === 'sqlite') {
            // SQLite stores the enum values in the original column definition.
            // To "modify" the enum we drop the column and add it back with
            // an IN(...) CHECK constraint that includes both old + new values
            // so any existing rows (with 'supreme' from earlier installs)
            // still validate.
            DB::statement('PRAGMA foreign_keys = OFF');

            $tmp = '__users_role_tmp';
            DB::statement("ALTER TABLE users ADD COLUMN {$tmp} VARCHAR(32)");
            DB::statement("UPDATE users SET {$tmp} = role");
            DB::statement("ALTER TABLE users DROP COLUMN role");

            $allowed = "'superadmin','admin','promoter','sub_promoter','buyer','supreme'";
            DB::statement("ALTER TABLE users ADD COLUMN role VARCHAR(32)
                NOT NULL DEFAULT 'buyer'
                CHECK (role IN ({$allowed}))");

            DB::statement("UPDATE users SET role = {$tmp}");
            DB::statement("ALTER TABLE users DROP COLUMN {$tmp}");

            DB::statement('PRAGMA foreign_keys = ON');
            return;
        }

        if ($driver === 'pgsql') {
            // PostgreSQL stores ENUMs as their own type. We can't widen a
            // native enum with simple SQL, so for dev convenience we just
            // coerce any 'supreme' to 'superadmin' before swapping to a
            // VARCHAR-backed CHECK constraint.
            DB::statement("UPDATE users SET role = 'superadmin' WHERE role = 'supreme'");

            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ALTER COLUMN role DROP DEFAULT");
            DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(32)");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'buyer'");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check
                CHECK (role IN ('superadmin','admin','promoter','sub_promoter','buyer'))");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role
                ENUM('supreme','admin','promoter','sub_promoter','buyer')
                NOT NULL DEFAULT 'buyer'");
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            $tmp = '__users_role_tmp';
            DB::statement("ALTER TABLE users ADD COLUMN {$tmp} VARCHAR(32)");
            DB::statement("UPDATE users SET {$tmp} = CASE
                WHEN role = 'superadmin' THEN 'supreme' ELSE role END");
            DB::statement("ALTER TABLE users DROP COLUMN role");

            DB::statement("ALTER TABLE users ADD COLUMN role VARCHAR(32)
                NOT NULL DEFAULT 'buyer'
                CHECK (role IN ('supreme','admin','promoter','sub_promoter','buyer'))");

            DB::statement("UPDATE users SET role = {$tmp}");
            DB::statement("ALTER TABLE users DROP COLUMN {$tmp}");

            DB::statement('PRAGMA foreign_keys = ON');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("UPDATE users SET role = 'supreme' WHERE role = 'superadmin'");
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check
                CHECK (role IN ('supreme','admin','promoter','sub_promoter','buyer'))");
        }
    }
};
