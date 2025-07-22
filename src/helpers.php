<?php

use KimiAI\KimiAIClient;

if (! function_exists('kimi_ai')) {
    /**
     * Get the Kimi AI client instance.
     *
     * @return \KimiAI\KimiAIClient
     */
    function kimi_ai()
    {
        return app('kimi-ai');
    }
}
