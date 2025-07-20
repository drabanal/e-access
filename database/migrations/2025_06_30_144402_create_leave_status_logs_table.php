<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_status_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('leave_request_id')->unsigned();
            $table->integer('leave_status_id')->unsigned();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('leave_request_id')->references('id')->on('leave_requests')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('leave_status_id')->references('id')->on('leave_statuses')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_status_logs');
    }
};
