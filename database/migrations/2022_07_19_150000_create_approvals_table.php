<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvalable');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('requester_id');
            $table->timestamp('requested_at');
            $table->text('requester_note')
                    ->nullable()
                    ->default(null);
            $table->unsignedBigInteger('responder_id');
            $table->timestamp('responded_at')
                    ->nullable()
                    ->default(null);
            $table->text('responder_note')
                    ->nullable()
                    ->default(null);
            $table->timestamps();

            $table->foreign('requester_id')
                    ->references('id')
                    ->on('users');

            $table->foreign('responder_id')
                    ->references('id')
                    ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approvals');
    }
};
