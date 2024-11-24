<?php

declare(strict_types=1);

namespace App\Service;

use Sentry\Event;

class SentryCallback
{
    public static function beforeSend(Event $event): ?Event
    {
        // Добавляем дополнительные теги или контекст
        $event->setTag('app.version', '1.0.0');
        
        // Можно модифицировать или фильтровать события
        if ($event->getLevel() === 'debug') {
            // Игнорируем debug события в production
            if ($_ENV['APP_ENV'] === 'prod') {
                return null;
            }
        }
        
        return $event;
    }
}
