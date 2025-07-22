<?php

require __DIR__ . '/../vendor/autoload.php';

use KimiAI\KimiAIClient;
use KimiAI\Enums\Models;
use KimiAI\Attachment;

// 1. إنشاء العميل مع النموذج المطلوب
$client = KimiAIClient::build('YOUR_API_KEY');

// 2. مثال استخدام نموذج الرؤية مع صورة
function exampleVisionModel()
{
    global $client;
    
    // تحميل الصورة
    $imagePath = __DIR__ . '/example.jpg';
    
    // إرسال استعلام مع صورة
    $response = $client
        ->withModel(Models::LLAMA_3_2_11B_VISION->value)
        ->query("ما الذي تراه في هذه الصورة؟", [
            Attachment::fromPath($imagePath, "صورة توضيحية")
        ])
        ->run();

    echo $response . "\n\n";
}

// 3. مثال استخدام نموذج نصي متقدم
function exampleTextModel()
{
    global $client;
    
    $response = $client
        ->withModel(Models::DEEPSEEK_R1->value)
        ->setSystemPrompt("أنت مساعد مفيد يتحدث باللغة العربية الفصحى")
        ->setTemperature(0.7)
        ->query("اكتب مقالاً قصيراً عن أهمية الذكاء الاصطناعي")
        ->run();

    echo $response . "\n\n";
}

// 4. مثال استخدام معالجة الملفات
function exampleFileProcessing()
{
    global $client;
    
    $pdfPath = __DIR__ . '/document.pdf';
    
    $response = $client
        ->withModel(Models::LLAMA_3_2_11B_VISION->value)
        ->query("ألخص لي هذا المستند", [
            Attachment::fromPath($pdfPath, "مستند PDF")
        ])
        ->run();

    echo $response . "\n\n";
}

// 5. مثال استخدام الدفق (Streaming)
function exampleStreaming()
{
    global $client;
    
    echo "جاري توليد النص...\n";
    
    $client
        ->withModel(Models::MISTRAL_7B->value)
        ->withStreaming(function($chunk) {
            echo $chunk;
            flush();
        })
        ->query("اكتب قصة خيالية قصيرة")
        ->run();
}

// تشغيل الأمثلة
try {
    echo "==== مثال نموذج الرؤية ====\n";
    exampleVisionModel();
    
    echo "\n==== مثال نموذج نصي ====\n";
    exampleTextModel();
    
    echo "\n==== مثال معالجة الملفات ====\n";
    exampleFileProcessing();
    
    // تعليق مؤقت لتفعيل الدفق عند الحاجة
    // echo "\n==== مثال الدفق (Streaming) ====\n";
    // exampleStreaming();
    
} catch (\Exception $e) {
    echo "حدث خطأ: " . $e->getMessage() . "\n";
}
