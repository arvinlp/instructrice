<?php
/*
 * @Author: Arvin Loripour - ViraEcosystem 
 * @Date: 2024-11-01 17:55:15 
 * Copyright by Arvin Loripour 
 * WebSite : http://www.arvinlp.ir 
 * @Last Modified by: Arvin.Loripour
 * @Last Modified time: 2024-11-01 17:57:19
 * Description: AvalAi is iranian ai service provider like OpenAi
 */

declare(strict_types=1);

namespace AdrienBrault\Instructrice\LLM\Provider;

use AdrienBrault\Instructrice\LLM\Cost;
use AdrienBrault\Instructrice\LLM\LLMConfig;
use AdrienBrault\Instructrice\LLM\OpenAiToolStrategy;

enum AvalAi: string implements ProviderModel
{
    case GPT_35T = 'gpt-3.5-turbo';
    case GPT_4T = 'gpt-4-turbo';
    case GPT_4O = 'gpt-4o';
    case GPT_4O_MINI = 'gpt-4o-mini';

    public function getApiKeyEnvVar(): ?string
    {
        return 'AVALAI_API_KEY';
    }

    public function createConfig(string $apiKey): LLMConfig
    {
        return new LLMConfig(
            'https://api.avalai.ir/v1/chat/completions',
            $this->value,
            match ($this) {
                self::GPT_35T => 16385,
                self::GPT_4T, self::GPT_4O, self::GPT_4O_MINI => 128000,
            },
            match ($this) {
                self::GPT_35T => 'GPT-3.5 Turbo',
                self::GPT_4T => 'GPT-4 Turbo',
                self::GPT_4O => 'GPT-4o',
                self::GPT_4O_MINI => 'GPT-4o mini',
            },
            'AvalAi',
            match ($this) {
                self::GPT_35T => new Cost(0.5, 1.5),
                self::GPT_4T => new Cost(10, 30),
                self::GPT_4O => new Cost(5, 15),
                self::GPT_4O_MINI => new Cost(0.15, 0.6),
            },
            OpenAiToolStrategy::FUNCTION,
            headers: [
                'Authorization' => 'Bearer ' . $apiKey,
            ],
            maxTokens: 4096,
            docUrl: match ($this) {
                self::GPT_35T => 'https://avalai.ir/blog/how-to-use-avalai-api-keys/',
                self::GPT_4T => 'https://avalai.ir/blog/how-to-use-avalai-api-keys/',
                self::GPT_4O => 'https://avalai.ir/blog/how-to-use-avalai-api-keys/',
                self::GPT_4O_MINI => 'https://avalai.ir/blog/how-to-use-avalai-api-keys/',
            }
        );
    }
}
