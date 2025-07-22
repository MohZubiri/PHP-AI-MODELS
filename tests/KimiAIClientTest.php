<?php

namespace KimiAI\Tests;

use PHPUnit\Framework\TestCase;
use KimiAI\KimiAIClient;
use KimiAI\Enums\Models;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class KimiAIClientTest extends TestCase
{
    private KimiAIClient $client;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();
        
        // إنشاء تكوين وهمي
        $config = [
            'api_base_url' => 'http://test-api.com',
            'timeout' => 30,
            'models' => [
                'test-model' => [
                    'endpoint' => 'v2/test-model/chat/completions',
                    'default_temperature' => 0.7,
                    'max_tokens' => 2048
                ]
            ]
        ];
        
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        
        // إنشاء عميل تجريبي مع التكوين الوهمي
        $this->client = KimiAIClient::build('test-key', 'http://test-api.com');
        
        // حقن التكوين الوهمي
        $reflection = new \ReflectionClass($this->client);
        
        $property = $reflection->getProperty('httpClient');
        $property->setAccessible(true);
        $property->setValue($this->client, new \GuzzleHttp\Client(['handler' => $handlerStack]));
        
        // حقن التكوين الوهمي
        if (function_exists('config')) {
            $this->originalConfig = config('kimi-ai');
            app()['config']->set('kimi-ai', $config);
        }
    }

    public function testBuild()
    {
        $client = KimiAIClient::build('test-key');
        $this->assertInstanceOf(KimiAIClient::class, $client);
    }
    
    public function testWithModelAppliesModelConfig()
    {
        $this->client->withModel('test-model');
        
        $reflection = new \ReflectionClass($this->client);
        
        $modelConfig = $reflection->getProperty('modelConfig');
        $modelConfig->setAccessible(true);
        
        $temperature = $reflection->getProperty('temperature');
        $temperature->setAccessible(true);
        
        $this->assertArrayHasKey('endpoint', $modelConfig->getValue($this->client));
        $this->assertEquals(0.7, $temperature->getValue($this->client));
    }

    public function testBasicQuery()
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'choices' => [['message' => ['content' => 'Test response']]]
        ])));

        $response = $this->client->query('Hello')->run();
        $this->assertEquals('Test response', $response);
    }

    public function testWithModel()
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'choices' => [['message' => ['content' => 'Test response']]]
        ])));

        $response = $this->client
            ->withModel(Models::K2_CHAT->value)
            ->query('Hello')
            ->run();
            
        $this->assertEquals('Test response', $response);
    }

    public function testFunctionCalling()
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'choices' => [[
                'message' => [
                    'function_call' => [
                        'name' => 'get_weather',
                        'arguments' => '{"location":"Berlin"}'
                    ]
                ]
            ]]
        ])));

        $this->mockHandler->append(new Response(200, [], json_encode([
            'choices' => [['message' => ['content' => 'Weather in Berlin is sunny']]]
        ])));

        $response = $this->client
            ->withFunction(
                function(array $args) {
                    $this->assertEquals('Berlin', $args['location']);
                    return 'sunny';
                },
                'get_weather',
                'Get the weather in a location',
                [
                    'type' => 'object',
                    'properties' => [
                        'location' => ['type' => 'string']
                    ],
                    'required' => ['location']
                ]
            )
            ->query('What\'s the weather in Berlin?')
            ->run();

        $this->assertEquals('Weather in Berlin is sunny', $response);
    }

    public function testStreaming()
    {
        $streamContent = [
            'data: ' . json_encode(['choices' => [['delta' => ['content' => 'Hello']]]]) . "\n\n",
            'data: ' . json_encode(['choices' => [['delta' => ['content' => ' World']]]]) . "\n\n",
            'data: [DONE]'
        ];

        $this->mockHandler->append(new Response(200, [], 
            new class($streamContent) implements \Psr\Http\Message\StreamInterface {
                private array $chunks;
                private int $position = 0;

                public function __construct(array $chunks) {
                    $this->chunks = $chunks;
                }

                public function read($length) {
                    if ($this->position >= count($this->chunks)) {
                        return '';
                    }
                    return $this->chunks[$this->position++];
                }

                public function eof() {
                    return $this->position >= count($this->chunks);
                }

                // Implement other required methods with empty bodies
                public function __toString() { return ''; }
                public function close() {}
                public function detach() { return null; }
                public function getSize() { return null; }
                public function tell() { return 0; }
                public function isSeekable() { return false; }
                public function seek($offset, $whence = SEEK_SET) {}
                public function rewind() {}
                public function isWritable() { return false; }
                public function write($string) { return 0; }
                public function isReadable() { return true; }
                public function getContents() { return ''; }
                public function getMetadata($key = null) { return null; }
            }
        ));

        ob_start();
        $this->client
            ->withStreaming()
            ->query('Hello')
            ->run();
        $output = ob_get_clean();

        $this->assertEquals('Hello World', $output);
    }
}
