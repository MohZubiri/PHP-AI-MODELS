<?php

namespace KimiAI\Laravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use KimiAI\KimiAIClient;

class KimiAIServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/kimi-ai.php', 'kimi-ai'
        );

        $this->app->singleton('kimi-ai', function ($app) {
            $config = $app['config']['kimi-ai'];
            
            return KimiAIClient::build(
                $config['api_key'],
                $config['base_url'] ?? 'https://api.kimi.ai/v2',
                $config['timeout'] ?? 30
            );
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/kimi-ai.php' => config_path('kimi-ai.php'),
            ], 'config');
        }
    }
}
