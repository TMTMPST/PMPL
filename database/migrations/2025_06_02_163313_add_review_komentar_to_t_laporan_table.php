<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Only add the column if it does not already exist (prevents duplicate-column errors
        // when the column was added in an earlier/mismatched migration).
        if (! Schema::hasColumn('t_laporan', 'review_komentar')) {
            Schema::table('t_laporan', function (Blueprint $table) {
                $table->text('review_komentar')->nullable()->after('review_pelapor');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('t_laporan', 'review_komentar')) {
            Schema::table('t_laporan', function (Blueprint $table) {
                $table->dropColumn('review_komentar');
            });
        }
    }
};
