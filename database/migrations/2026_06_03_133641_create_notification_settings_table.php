<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->boolean('daily_summary')->default(true);
            $table->boolean('weekly_report')->default(false);

            $table->boolean('spending_alert')->default(true);

            $table->integer('alert_threshold')
                  ->default(75);

            $table->bigInteger('monthly_budget')
                  ->default(2500000);

            $table->boolean('savings_goal_enabled')
                  ->default(true);

            $table->bigInteger('monthly_savings_goal')
                  ->default(3000000);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};