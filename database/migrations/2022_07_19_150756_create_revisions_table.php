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
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')
                    ->constrained()
                    ->cascadeOnDelete();
            $table->string('code')
                    ->unique();
            $table->enum('classification', ['major', 'minor']);
            $table->longText('reason_change');
            $table->unsignedTinyInteger('level')
                    ->nullable()
                    ->default(null);
            $table->unsignedBigInteger('created_by_id');
            $table->timestamps();

            $table->foreign('created_by_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('revisions');
    }
};
