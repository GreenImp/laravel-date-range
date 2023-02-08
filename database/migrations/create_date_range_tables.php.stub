<?php

use Exception;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('date-range.table_names');
        $columnNames = config('date-range.column_names');

        if (empty($tableNames)) {
            throw new Exception('Error: config/date-range.php not loaded. Run [php artisan config:clear] and try again.');
        }

        if (config('date-range.multiple_dates')) {
            Schema::create($tableNames['date_ranges'], function (Blueprint $table) use ($columnNames) {
                $table->id($columnNames['date_range_key']);
                $table->datetime($columnNames['start_at'])
                    ->nullable();
                $table->dateTime($columnNames['end_at'])
                    ->nullable();

                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);

                if ((new config('date-range.models.date_range'))->timestamps) {
                    $table->timestamps();
                }

                $table->index([$columnNames['start_at'], $columnNames['end_at']]);
                $table->index([$columnNames['model_morph_key'], 'model_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/date-ranges.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        foreach ($tableNames as $table) {
            Schema::dropIfExists($table);
        }
    }
};
