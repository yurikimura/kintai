<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnsFromStampCorrectionRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            $table->dropColumn([
                'request_date',
                'request_type',
                'current_time',
                'request_time',
                'reason',
                'status'
            ]);
        });
    }

    public function down()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            $table->date('request_date');
            $table->string('request_type');
            $table->datetime('current_time');
            $table->datetime('request_time');
            $table->text('reason');
            $table->string('status')->default('pending');
        });
    }
}