<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kimi AI API Key
    |--------------------------------------------------------------------------
    |
    | Your Kimi AI API key. You can find this in your Kimi AI dashboard.
    |
    */
    'api_key' => env('KIMI_AI_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Kimi AI API.
    |
    */
    'base_url' => env('KIMI_AI_BASE_URL', 'https://api.kimi.ai/v2'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests.
    |
    */
    'timeout' => env('KIMI_AI_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Global API configuration.
    |
    */
    'api_version' => 'v2',
    'api_base_url' => env('KIMI_AI_BASE_URL', 'https://api.kimi.ai'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default model to use when none is specified.
    |
    */
    'default_model' => env('KIMI_AI_DEFAULT_MODEL', 'kimi-k2-chat'),

    /*
    |--------------------------------------------------------------------------
    | Available Models
    |--------------------------------------------------------------------------
    |
    | List of all available models with their configurations.
    |
    */
    'models' => [
        'kimi-k2-chat' => [
            'name' => 'Kimi K2 Chat',
            'id' => 'kimi-k2-chat',
            'api_key' => env('KIMI_K2_API_KEY', env('KIMI_AI_API_KEY')),
            'endpoint' => '/v2/chat/completions',
            'description' => 'النموذج الأساسي من Kimi AI',
            'capabilities' => ['chat', 'function-calling'],
            'max_tokens' => 4096,
            'supports_vision' => false,
            'default_temperature' => 0.7,
        ],
        'deepseek-r1' => [
            'name' => 'DeepSeek R1',
            'id' => 'deepseek-r1',
            'api_key' => env('DEEPSEEK_API_KEY', env('KIMI_AI_API_KEY')),
            'endpoint' => '/v2/deepseek/chat/completions',
            'description' => 'نموذج DeepSeek R1 للبرمجة والاستشارات التقنية',
            'capabilities' => ['chat', 'code', 'function-calling'],
            'max_tokens' => 8192,
            'supports_vision' => false,
            'default_temperature' => 0.5,
        ],
        'llama-3-3-70b' => [
            'name' => 'Llama 3.3 70B Instruct',
            'id' => 'llama-3-3-70b-instruct',
            'api_key' => env('LLAMA_70B_API_KEY', env('KIMI_AI_API_KEY')),
            'endpoint' => '/v2/llama-3-3-70b/chat/completions',
            'description' => 'نموذج Llama 3.3 70B متعدد الاستخدامات',
            'capabilities' => ['chat', 'code', 'creative-writing'],
            'max_tokens' => 8192,
            'supports_vision' => false,
            'default_temperature' => 0.7,
        ],
        'llama-3-2-11b-vision' => [
            'name' => 'Llama 3.2 11B Vision',
            'id' => 'llama-3-2-11b-vision',
            'api_key' => env('LLAMA_VISION_API_KEY', env('KIMI_AI_API_KEY')),
            'endpoint' => '/v2/llama-3-2-11b-vision/chat/completions',
            'description' => 'نموذج Llama 3.2 11B مع دعم الرؤية',
            'capabilities' => ['chat', 'vision', 'image-analysis'],
            'max_tokens' => 4096,
            'supports_vision' => true,
            'default_temperature' => 0.5,
            'supported_mime_types' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'application/pdf',
                'text/plain',
            ],
            'max_file_size' => 10 * 1024 * 1024, // 10MB
        ],
        'mistral-7b' => [
            'name' => 'Mistral 7B Instruct v0.3',
            'id' => 'mistral-7b-instruct-v0.3',
            'api_key' => env('MISTRAL_API_KEY', env('KIMI_AI_API_KEY')),
            'endpoint' => '/v2/mistral-7b/chat/completions',
            'description' => 'نموذج Mistral 7B سريع وفعال',
            'capabilities' => ['chat', 'summarization'],
            'max_tokens' => 8192,
            'supports_vision' => false,
            'default_temperature' => 0.7,
        ],
    ],
];
