<?php
namespace ApurbaLabs\ApprovalEngine\Support;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use ApurbaLabs\ApprovalEngine\Contracts\PluginInterface;

abstract class BasePlugin implements PluginInterface
{
    public function register(): void {}
    
    public function boot(): void {}

    protected function listen(string $eventClass, callable $listener): void
    {
        Event::listen($eventClass, function ($eventInstance) use ($listener, $eventClass) {
            try {
                $listener($eventInstance);
            } catch (\Throwable $e) {
                Log::error('Plugin listener failed', [
                    'event' => $eventClass,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}