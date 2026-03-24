<?php

namespace ApurbaLabs\ApprovalEngine\Database\Factories;

use ApurbaLabs\ApprovalEngine\Models\WorkflowSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class WorkflowSettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = WorkflowSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module' => 'requisition',
            'role' => 'HOSD',
            'frequency' => 'daily',
            'send_time' => '12:00:00',
            'timezone' => 'Asia/Dhaka',
            'is_active' => true,
        ];
    }
    /**
     * Set a dynamic module for the stage.
     */
    public function forModule(string $name)
    {
        return $this->state(fn () => ['module' => $name]);
    }

    /**
     * Set a dynamic role for the stage.
     */
    public function forRole(string $name)
    {
        return $this->state(fn () => ['role' => $name]);
    }

    /**
     * Set a dynamic send time (needs to be a string like '09:00:00')
     */
    public function atSendTime(string $sendTime) 
    {
        return $this->state(fn () => ['send_time' => $sendTime]);
    }

   /**
     * Set a dynamic frequency with optional day/date values.
     * 
     * @param string $frequency ('instant', 'daily', 'weekly', 'monthly')
     * @param int|null $freqVal (Day 0-6 for weekly, Date 1-31 for monthly)
     */
    public function atFrequency(string $frequency, $freqVal = null) 
    {
        return $this->state(function (array $attributes) use ($frequency, $freqVal) {
            $data = ['frequency' => $frequency];

            if ($frequency === 'weekly') {
                $data['weekly_day'] = $freqVal ?? 0; // Default to Monday if null
            }

            if ($frequency === 'monthly') {
                $data['monthly_date'] = $freqVal ?? 1; // Default to 1st if null
            }

            return $data;
        });
    }
}
