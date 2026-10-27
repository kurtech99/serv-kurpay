<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        if (app()->environment('production') && (str_starts_with(config('app.url'), 'https://') || request()->header('X-Forwarded-Proto') === 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $smtpHost = \App\Models\Setting::get('smtp_host');
                if ($smtpHost) {
                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.host' => $smtpHost,
                        'mail.mailers.smtp.port' => \App\Models\Setting::get('smtp_port'),
                        'mail.mailers.smtp.username' => \App\Models\Setting::get('smtp_username'),
                        'mail.mailers.smtp.password' => \App\Models\Setting::get('smtp_password'),
                        'mail.mailers.smtp.encryption' => \App\Models\Setting::get('smtp_encryption'),
                        'mail.from.address' => \App\Models\Setting::get('smtp_from_address'),
                        'mail.from.name' => \App\Models\Setting::get('smtp_from_name'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Ignore during setup/migration
        }
    }
}
