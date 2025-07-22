<?php

namespace KimiAI;

use GuzzleHttp\Client;
use KimiAI\Exceptions\KimiAIException;

class KimiAIClient
{
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;
    private string $model = 'kimi-k2-chat';
    private float $temperature = 0.8;
    private int $maxTokens = 2048;
    private array $modelConfig = [];
    private string $responseFormat = 'text';
    private string $query = '';
    private array $messages = [];
    private array $functions = [];
    private array $attachments = [];
    private bool $stream = false;
    private ?Client $httpClient = null;
    private ?\Closure $streamCallback = null;
    private ?string $systemPrompt = null;

    public static function build(string $apiKey, string $baseUrl = null, int $timeout = null): self
    {
        $config = config('kimi-ai', []);
        
        $instance = new self();
        $instance->apiKey = $apiKey;
        $instance->baseUrl = $baseUrl ?? $config['api_base_url'] ?? 'https://api.kimi.ai/v2';
        $instance->timeout = $timeout ?? ($config['timeout'] ?? 30);
        
        $instance->httpClient = new Client([
            'base_uri' => $instance->baseUrl,
            'timeout' => $instance->timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $instance->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
        return $instance;
    }

    public function withModel(string $model): self
    {
        $this->model = $model;
        
        // تحميل إعدادات النموذج من ملف التكوين
        $config = config('kimi-ai');
        
        if (isset($config['models'][$model])) {
            $this->modelConfig = $config['models'][$model];
            
            // تطبيق الإعدادات الافتراضية للنموذج
            if (isset($this->modelConfig['default_temperature'])) {
                $this->temperature = $this->modelConfig['default_temperature'];
            }
            
            if (isset($this->modelConfig['max_tokens'])) {
                $this->maxTokens = $this->modelConfig['max_tokens'];
            }
            
            // تحديث عنوان URL الأساسي إذا كان هناك نقطة نهاية مخصصة
            if (isset($this->modelConfig['endpoint'])) {
                $baseUrl = rtrim($config['api_base_url'] ?? $this->baseUrl, '/');
                $endpoint = ltrim($this->modelConfig['endpoint'], '/');
                $this->baseUrl = "{$baseUrl}/{$endpoint}";
            }
        }
        
        return $this;
    }

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function setMaxTokens(int $maxTokens): self
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function setResponseFormat(string $format): self
    {
        $this->responseFormat = $format;
        return $this;
    }

    public function query(string $query, array $attachments = []): self
    {
        $this->query = $query;
        
        $message = [
            'role' => 'user',
            'content' => $query,
        ];

        // إضافة المرفقات إذا وجدت
        if (!empty($attachments)) {
            $message['attachments'] = array_map(fn($attachment) => 
                $attachment instanceof Attachment ? $attachment->toArray() : $attachment,
                $attachments
            );
        }

        $this->messages[] = $message;
        return $this;
    }

    public function addAttachment(Attachment $attachment): self
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    public function setSystemPrompt(string $prompt): self
    {
        $this->systemPrompt = $prompt;
        return $this;
    }

    public function addMessage(string $role, string $content): self
    {
        $this->messages[] = ['role' => $role, 'content' => $content];
        return $this;
    }

    public function withFunction(callable $function, string $name, string $description, array $parameters): self
    {
        $this->functions[] = [
            'name' => $name,
            'description' => $description,
            'parameters' => $parameters,
            'callable' => $function
        ];
        return $this;
    }

    public function withStreaming(\Closure $callback = null): self
    {
        $this->stream = true;
        $this->streamCallback = $callback;
        return $this;
    }

    public function run(): mixed
    {
        try {
            // تجهيز الرسائل
            $messages = !empty($this->messages) ? $this->messages : [
                ['role' => 'user', 'content' => $this->query]
            ];

            // إضافة رسالة النظام إذا وجدت
            if ($this->systemPrompt !== null) {
                array_unshift($messages, [
                    'role' => 'system',
                    'content' => $this->systemPrompt
                ]);
            }

            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
                'stream' => $this->stream,
            ];

            // إضافة المرفقات إذا كانت النماذج تدعمها
            if (!empty($this->attachments) && $this->isVisionModel()) {
                $payload['attachments'] = array_map(
                    fn($a) => $a->toArray(),
                    $this->attachments
                );
            }

            if ($this->responseFormat) {
                $payload['response_format'] = $this->responseFormat;
            }

            if (!empty($this->functions)) {
                $payload['functions'] = array_map(fn($fn) => [
                    'name' => $fn['name'],
                    'description' => $fn['description'],
                    'parameters' => $fn['parameters'],
                ], $this->functions);
            }

            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ];

            if ($this->stream) {
                return $this->handleStreamResponse($options);
            }

            $response = $this->httpClient->post('/chat/completions', $options);
            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['choices'][0]['message']['function_call'])) {
                return $this->handleFunctionCall($body);
            }

            return $body['choices'][0]['message']['content'] ?? $body;
        } catch (\Exception $e) {
            throw new KimiAIException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function isVisionModel(): bool
    {
        return in_array($this->model, [
            Models::LLAMA_3_2_11B_VISION->value,
        ]);
    }

    private function handleStreamResponse(array $options): void
    {
        $options['stream'] = true;
        $response = $this->httpClient->post('/chat/completions', [
            'headers' => $options['headers'],
            'json' => $options['json'],
            'stream' => true,
        ]);

        $stream = $response->getBody();
        $buffer = '';

        while (!$stream->eof()) {
            $chunk = $stream->read(1024);
            $buffer .= $chunk;

            while (($newlinePos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $newlinePos);
                $buffer = substr($buffer, $newlinePos + 1);

                if (strpos($line, 'data: ') === 0) {
                    $data = substr($line, 6);
                    if ($data === '[DONE]') {
                        break 2;
                    }

                    $json = json_decode(trim($data), true);
                    if ($json && isset($json['choices'][0]['delta']['content'])) {
                        $content = $json['choices'][0]['delta']['content'];
                        if ($this->streamCallback) {
                            ($this->streamCallback)($content);
                        } else {
                            echo $content;
                            flush();
                        }
                    }
                }
            }
        }
    }

    private function handleFunctionCall(array $response): mixed
    {
        $functionCall = $response['choices'][0]['message']['function_call'];
        $functionName = $functionCall['name'];
        $arguments = json_decode($functionCall['arguments'], true);

        foreach ($this->functions as $function) {
            if ($function['name'] === $functionName) {
                $result = call_user_func_array($function['callable'], [$arguments]);
                
                // Add the function response to messages
                $this->messages[] = [
                    'role' => 'function',
                    'name' => $functionName,
                    'content' => is_string($result) ? $result : json_encode($result)
                ];

                // Continue the conversation
                return $this->run();
            }
        }

        throw new KimiAIException("Function {$functionName} not found");
    }
}
