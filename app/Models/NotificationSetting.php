<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'daily_summary',
        'weekly_report',
        'spending_alert',
        'alert_threshold',
        'monthly_budget',
        'savings_goal_enabled',
        'monthly_savings_goal'
    ];
}