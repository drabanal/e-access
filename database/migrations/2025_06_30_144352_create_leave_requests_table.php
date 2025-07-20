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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('leave_type_id')->unsigned();
            $table->integer('leave_status_id')->unsigned();
            $table->dateTime('date_time_from');
            $table->dateTime('date_time_to');
            $table->double('duration', 10,2);
            $table->boolean('is_full_shift');
            $table->boolean('remove_break_hours');
            $table->boolean('sl_charged_to_vl');
            $table->text('remarks');
            $table->text('approve_reason')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->text('disapprove_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('reference_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')
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
        Schema::dropIfExists('leave_requests');
    }
};
