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
`id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
`user_id` bigint UNSIGNED DEFAULT NULL,
`ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
`user_agent` text COLLATE utf8mb4_unicode_ci,
`payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
`last_activity` int NOT NULL

    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload')->nullable();
            $table->integer('last_activity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
};
