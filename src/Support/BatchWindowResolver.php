<?php

namespace ApurbaLabs\ApprovalEngine\Support;

use Carbon\Carbon;

class BatchWindowResolver
{
    public function resolve($setting): array
    {
        $timezone = $setting->timezone ?? config('app.timezone', 'UTC');
        
        $now = now()->timezone($timezone);
        
        $end = $now->copy();

        $start = match ($setting->frequency) {
            'daily' => $end->copy()->subDay(),
            
            'weekly' => $end->copy()->subWeek(),
            
            'monthly' => $end->copy()->subMonth(),
            
            default => throw new \RuntimeException("Unsupported frequency: {$setting->frequency}")
        };

        return compact('start','end');
    }
}
