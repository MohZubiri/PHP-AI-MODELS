
# PHP-KIMI-AI

# PHP Kimi AI Client

A robust and developer-friendly PHP Client for integrating with the Kimi AI K2 API. 

## Features
- Seamless API Integration: PHP-first interface for Kimi AI K2 capabilities.
- Fluent Builder Pattern: Chainable methods for intuitive request building.
- PSR-18 compatible (works with Guzzle, Symfony HttpClient, etc.)
- Laravel Integration: ServiceProvider, Facade, and config file included
- Helper functions for easier usage
- Support for multiple AI models:
  - Kimi K2 Chat
  - DeepSeek R1
  - Llama 3.3 70B Instruct
  - Llama 3.2 11B Vision (with image support)
  - Mistral 7B Instruct v0.3
- File and Image Processing
- Streaming Responses
- Function Calling

## Installation
```bash
composer require mohzubiri/php-kimi-ai
```

## Quick Start

### Basic Text Generation
```php
use KimiAI\KimiAIClient;

$response = KimiAIClient::build('your-api-key')
    ->withModel('llama-3-3-70b-instruct')
    ->query('Hello Kimi!')
    ->run();

echo $response;
```

### Image Analysis
```php
use KimiAI\KimiAIClient;
use KimiAI\Attachment;

$response = KimiAIClient::build('your-api-key')
    ->withModel('llama-3-2-11b-vision')
    ->query('ما الذي تراه في هذه الصورة؟', [
        Attachment::fromPath('path/to/your/image.jpg', 'وصف للصورة')
    ])
    ->run();

echo $response;
```

## Laravel Integration
### Publish Config (Optional)
```bash
php artisan vendor:publish --provider="KimiAI\Laravel\KimiAIServiceProvider" --tag="config"
```

### Add to .env
```env
KIMI_AI_API_KEY=your-api-key
KIMI_AI_BASE_URL=https://api.kimi.ai/v2
KIMI_AI_TIMEOUT=30
```

### Using Facade
```php
use KimiAI\Laravel\Facades\KimiAI;

$response = KimiAI::query('Hello Kimi!')->run();
```

### Using Helper Function
```php
$response = kimi_ai()->query('Hello Kimi!')->run();
```

## Advanced Usage
```php
use KimiAI\KimiAIClient;
use KimiAI\Enums\Models;

$client = KimiAIClient::build('your-api-key', 'https://api.kimi.ai/v2', 30);
$response = $client
    ->withModel(Models::K2_CHAT->value)
    ->setTemperature(1.0)
    ->setMaxTokens(2048)
    ->query('Write a poem about the sea')
    ->run();

print_r($response);
```

## Testing
```bash
composer test
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
MIT
>>>>>>> 356d134 (First Vertio)
