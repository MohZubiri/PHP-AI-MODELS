<?php

namespace KimiAI\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \KimiAI\KimiAIClient withModel(string $model)
 * @method static \KimiAI\KimiAIClient setTemperature(float $temperature)
 * @method static \KimiAI\KimiAIClient setMaxTokens(int $maxTokens)
 * @method static \KimiAI\KimiAIClient setResponseFormat(string $format)
 * @method static \KimiAI\KimiAIClient query(string $query)
 * @method static mixed run()
 */
class KimiAI extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'kimi-ai';
    }
}
