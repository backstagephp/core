<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = 'content';
        $column = 'ordered_id';
        $sequence = 'content_ordered_id_seq';
        $trigger = 'content_set_ordered_id';

        if (Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->unsignedBigInteger($column)->after('ulid')->nullable();
        });

        // Fill the ordered_id column for existing content based on creation order (database-agnostic)
        $contents = DB::table($tableName)->orderBy('created_at', 'asc')->get(['ulid']);
        $orderedId = 1;
        foreach ($contents as $content) {
            DB::table($tableName)->where('ulid', $content->ulid)->update([$column => $orderedId++]);
        }

        $driver = DB::getDriverName();

        // Add unique index and make NOT NULL where supported
        Schema::table($tableName, function (Blueprint $table) use ($driver, $column) {
            if ($driver === 'sqlite') {
                $table->unique($column);

                return;
            }

            $table->unsignedBigInteger($column)->nullable(false)->unique()->change();
        });

        // Configure auto-increment using database-specific syntax
        $postgresSql = <<<SQL
        CREATE SEQUENCE IF NOT EXISTS {$sequence} OWNED BY {$tableName}.{$column};
        SELECT setval('{$sequence}', COALESCE((SELECT MAX({$column}) FROM {$tableName}), 0) + 1, false);
        ALTER TABLE {$tableName} ALTER COLUMN {$column} SET DEFAULT nextval('{$sequence}');
        SQL;

        $sqliteTriggerSql = <<<SQL
        CREATE TRIGGER IF NOT EXISTS {$trigger}
        AFTER INSERT ON {$tableName}
        WHEN NEW.{$column} IS NULL
        BEGIN
            UPDATE {$tableName}
            SET {$column} = (
                SELECT COALESCE(MAX({$column}), 0) + 1
                FROM {$tableName}
                WHERE ulid <> NEW.ulid
            )
            WHERE ulid = NEW.ulid;
        END;
        SQL;

        switch ($driver) {
            case 'mysql':
                DB::statement("ALTER TABLE {$tableName} MODIFY {$column} BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");

                break;
            case 'pgsql':
                DB::statement($postgresSql);

                break;
            case 'sqlite':
                DB::statement($sqliteTriggerSql);

                break;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = 'content';
        $column = 'ordered_id';
        $sequence = 'content_ordered_id_seq';
        $trigger = 'content_set_ordered_id';

        if (! Schema::hasColumn($tableName, $column)) {
            return;
        }

        $driver = DB::getDriverName();
        $shouldDrop = true;

        switch ($driver) {
            case 'mysql':
                $columnMeta = DB::selectOne(
                    <<<SQL
                    SELECT EXTRA as extra
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = '{$tableName}'
                      AND COLUMN_NAME = '{$column}'
                    SQL
                );
                $shouldDrop = $columnMeta !== null
                    && str_contains(strtolower($columnMeta->extra ?? ''), 'auto_increment');

                break;
            case 'pgsql':
                $shouldDrop = DB::selectOne(
                    "SELECT 1 FROM pg_class WHERE relkind = 'S' AND relname = '{$sequence}'"
                ) !== null;

                break;
            case 'sqlite':
                $shouldDrop = DB::selectOne(
                    "SELECT 1 FROM sqlite_master WHERE type = 'trigger' AND name = '{$trigger}'"
                ) !== null;

                break;
        }

        if (! $shouldDrop) {
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement("DROP TRIGGER IF EXISTS {$trigger}");
        }

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->dropColumn($column);
        });
    }
};
