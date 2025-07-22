<?php

namespace KimiAI\Enums;

enum Models: string
{
    // نماذج النصوص
    case K2_CHAT = 'kimi-k2-chat';
    case DEEPSEEK_R1 = 'deepseek-r1';
    case LLAMA_3_3_70B = 'llama-3-3-70b-instruct';
    case LLAMA_3_2_11B_VISION = 'llama-3-2-11b-vision';
    case MISTRAL_7B = 'mistral-7b-instruct-v0.3';
    
    // دالة للحصول على نوع النموذج
    public function isVisionModel(): bool
    {
        return match($this) {
            self::LLAMA_3_2_11B_VISION => true,
            default => false,
        };
    }
}
