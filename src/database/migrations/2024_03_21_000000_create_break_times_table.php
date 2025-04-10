<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('break_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->datetime('start_time');
            $table->datetime('end_time')->nullable();
            $table->integer('break_time')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('break_times');
    }
};
