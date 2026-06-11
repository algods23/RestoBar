<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * SQLite does not support ALTER COLUMN for enum changes.
     * We update the CHECK constraint by manipulating the sqlite_master directly,
     * or simply use DB::statement to recreate the table structure.
     *
     * The simplest safe approach: just do a raw UPDATE on sqlite_master to expand
     * the CHECK constraint to include 'mixed'.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: rewrite the CREATE TABLE statement in sqlite_master
            // to expand the order_type CHECK constraint to include 'mixed'
            DB::statement('PRAGMA writable_schema = ON');

            $sql = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name='orders'");

            if ($sql) {
                $newSql = str_replace(
                    "check (\"order_type\" in ('dine_in', 'takeout', 'delivery'))",
                    "check (\"order_type\" in ('dine_in', 'takeout', 'mixed', 'delivery'))",
                    $sql->sql
                );

                DB::statement("UPDATE sqlite_master SET sql = ? WHERE type='table' AND name='orders'", [$newSql]);
            }

            DB::statement('PRAGMA writable_schema = OFF');
            DB::statement('PRAGMA integrity_check');
        } else {
            // MySQL / PostgreSQL: use CHANGE COLUMN
            DB::statement("ALTER TABLE orders MODIFY COLUMN order_type ENUM('dine_in','takeout','mixed','delivery') NOT NULL");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA writable_schema = ON');

            $sql = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name='orders'");

            if ($sql) {
                $newSql = str_replace(
                    "check (\"order_type\" in ('dine_in', 'takeout', 'mixed', 'delivery'))",
                    "check (\"order_type\" in ('dine_in', 'takeout', 'delivery'))",
                    $sql->sql
                );

                DB::statement("UPDATE sqlite_master SET sql = ? WHERE type='table' AND name='orders'", [$newSql]);
            }

            DB::statement('PRAGMA writable_schema = OFF');
        } else {
            DB::statement("ALTER TABLE orders MODIFY COLUMN order_type ENUM('dine_in','takeout','delivery') NOT NULL");
        }
    }
};
