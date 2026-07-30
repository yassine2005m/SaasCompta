<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Illuminate\Support\Facades\Mail;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix SSL certificate verification failure on Windows (OpenSSL missing CA bundle)
        // Only applied in local environment — Railway/Linux servers have proper CA bundles
        if (app()->environment('local')) {
            Mail::extend('smtp', function (array $config) {
                $transport = new EsmtpTransport(
                    $config['host'] ?? '127.0.0.1',
                    (int) ($config['port'] ?? 587),
                    false  // false = STARTTLS on port 587
                );

                if (!empty($config['username'])) {
                    $transport->setUsername($config['username']);
                }
                if (!empty($config['password'])) {
                    $transport->setPassword($config['password']);
                }

                // Correct API: options go on the underlying SocketStream
                $transport->getStream()->setStreamOptions([
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ]);

                return $transport;
            });
        }

        \Illuminate\Support\Facades\View::composer('layouts.sidebar', function ($view) {
            $view->with('pendingContractsCount', \App\Models\Contract::where('status', 'pending')->count());

            $fifteenDays = now()->addDays(15)->toDateString();
            $today       = now()->toDateString();

            $view->with('expiringContractsCount', \App\Models\Contract::where('status', 'active')
                ->where('end_date', '<=', $fifteenDays)
                ->where('end_date', '>=', $today)
                ->whereDoesntHave('renewals')
                ->count());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });
    }
}
