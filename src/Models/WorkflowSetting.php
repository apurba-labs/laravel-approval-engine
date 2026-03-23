<?php

namespace ApurbaLabs\ApprovalEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use ApurbaLabs\ApprovalEngine\Database\Factories\WorkflowSettingFactory;

class WorkflowSetting extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'module',
        'role',
        'frequency',
        'weekly_day',
        'monthly_date',
        'send_time',
        'timezone',
        'last_run_at',
        'is_active',
    ];

    protected $casts = [
        'last_run_at'=>'datetime',
        'is_active' => 'boolean',
        'send_time' => 'datetime:H:i',
    ];

    public static function frequencies()
    {
        return [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ];
    }

    public static function weekdays()
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return WorkflowSettingFactory::new();
    }
}
