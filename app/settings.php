<?php

declare(strict_types=1);

use App\Services\Setting;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        Setting::class => function () {
            return new Setting([
                'debug' => true,

                'main' => [
                    'guest_name' => 'Гость',
                ],

                'guestbook' => [
                    'per_page'         => 10,
                    'title_min_length' => 5,
                    'title_max_length' => 50,
                    'text_min_length'  => 5,
                    'text_max_length'  => 5000,
                    'allow_guests'     => true,
                ],

                'session' => [
                    'name'            => 'motor_session',
                    'cookie_secure'   => true,
                    'cookie_httponly' => true,
                    'cookie_lifetime' => 3600,
                    'gc_maxlifetime'  => 3600,
                    'cookie_samesite' => 'Lax',
                ],

                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'motor-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    //'level' => Logger::DEBUG,
                ],
            ]);
        }
    ]);
};
