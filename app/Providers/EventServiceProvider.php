<?php

namespace App\Providers;

use App\Events\SincronizarSistema;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        'App\Events\CultivoInactivo' => [
            'App\Listeners\ManejarCultivoInactivo',
        ],
        'App\Events\InicioDeAplicacion' => [
            'App\Listeners\EjecutarScriptsDeBase',
        ],
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SincronizarSistema::class => [
            SincronizarSistemaListener::class,
        ],
        CheckEvent::class => [
            CheckEventListener::class,
        ],
        RestartEvent::class => [
            RestartEventListener::class,
        ],
        RiegoEvent::class => [
            RiegoEventListener::class,
        ],
    ];


    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
