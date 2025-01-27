# 👩‍🏫 adrienbrault/instructrice

[![GitHub Actions][gh_actions_image]][gh_actions_link]
[![Packagist][packagist_image]][packagist_link]
[![License][license_image]](./LICENSE)

## Typing LLM completions

Best in class LLMs are able to output JSON following a schema you provide, usually JSON-Schema.
This significantly expands the ways you can leverage LLMs in your application!

Think of the input as:
- A context, anything that is or can be converted to text, like emails/pdfs/html/xlsx
- A schema, "Here is the form you need to fill to complete your task"
- An optional prompt, giving a specific task, rules, etc

And the output/outcome is whichever structure best matches your use case and domain.

The [python instructor cookbook][instructor_cookbook] has interesting examples.

## Introduction

Instructrice is a PHP library that simplifies working with structured output from LLMs in a type-safe manner.

Features:
- Flexible schema options:
  - Classes using [api-platform/json-schema][api_platform_json_schema]
  - Dynamically generated types [PSL][psl]\\[Type][psl_type]
  - Or a JSON-Schema array generated by a third party library, or in plain PHP
- [symfony/serializer][sf_serializer] integration to deserialize LLMs outputs
- Streaming first:
  - As a developer you can be more productive with faster feedback loops than waiting for outputs to complete. This also makes slower local models more usable.
  - You can provide a much better and snappier UX to your users.
  - The headaches of parsing incomplete JSON are handled for you.
- [A set of pre-configured LLMs](#supported-providers) with the best available settings. Set your API keys and switch between different providers and models without having to think about the model name, json mode, function calling, etc.

A [Symfony Bundle][sf_bundle] is also available.

## Installation and Usage

```bash
composer require kargnas/instructrice
```

```php
use AdrienBrault\Instructrice\InstructriceFactory;
use AdrienBrault\Instructrice\LLM\Provider\Ollama;
use AdrienBrault\Instructrice\LLM\Provider\OpenAi;
use AdrienBrault\Instructrice\LLM\Provider\Anthropic;

$instructrice = InstructriceFactory::create(
    defaultLlm: Ollama::HERMES2THETA_LLAMA3_8B,
    apiKeys: [ // Unless you inject keys here, api keys will be fetched from environment variables
        OpenAi::class => $openAiApiKey,
        Anthropic::class => $anthropicApiKey,
    ],
);
```

### List of object

```php
use AdrienBrault\Instructrice\Attribute\Prompt;

class Character
{
    // The prompt annotation lets you add instructions specific to a property
    #[Prompt('Just the first name.')]
    public string $name;
    public ?string $rank = null;
}

$characters = $instructrice->getList(
    Character::class,
    'Colonel Jack O\'Neil walks into a bar and meets Major Samanta Carter. They call Teal\'c to join them.',
);

/*
dump($characters);
array:3 [
  0 => Character^ {
    +name: "Jack"
    +rank: "Colonel"
  }
  1 => Character^ {
    +name: "Samanta"
    +rank: "Major"
  }
  2 => Character^ {
    +name: "Teal'c"
    +rank: null
  }
]
*/
```

### Object

```php
$character = $instructrice->get(
    type: Character::class,
    context: 'Colonel Jack O\'Neil.',
);

/*
dump($character);
Character^ {
  +name: "Jack"
  +rank: "Colonel"
}
*/
```

### Dynamic Schema

```php
$label = $instructrice->get(
    type: [
        'type' => 'string',
        'enum' => ['positive', 'neutral', 'negative'],
    ],
    context: 'Amazing great cool nice',
    prompt: 'Sentiment analysis',
);

/*
dump($label);
"positive"
*/
```

You can also use third party json schema libraries like [goldspecdigital/oooas][oooas] to generate the schema:
- [examples/oooas.php](examples/oooas.php)

https://github.com/adrienbrault/instructrice/assets/611271/da69281d-ac56-4135-b2ef-c5e306a56de2

## Supported providers

| Provider                          | Environment Variables | Enum                                          | API Key Creation URL                           |
|-----------------------------------|-----------------------|-----------------------------------------------|------------------------------------------------|
| [Ollama][ollama]                  | `OLLAMA_HOST`         | [Ollama](src/LLM/Provider/Ollama.php)         |                                                |
| [OpenAI][openai_pricing]          | `OPENAI_API_KEY`      | [OpenAi](src/LLM/Provider/OpenAi.php)         | [API Key Management][openai_apikey_create]     |
| [Anthropic][anthropic_pricing]    | `ANTHROPIC_API_KEY`   | [Anthropic](src/LLM/Provider/Anthropic.php)   | [API Key Management][anthropic_apikey_create]  |
| [Mistral][mistral_pricing]        | `MISTRAL_API_KEY`     | [Mistral](src/LLM/Provider/Mistral.php)       | [API Key Management][mistral_apikey_create]    |
| [Fireworks AI][fireworks_pricing] | `FIREWORKS_API_KEY`   | [Fireworks](src/LLM/Provider/Fireworks.php)   | [API Key Management][fireworks_apikey_create]  |
| [Groq][groq_pricing]              | `GROQ_API_KEY`        | [Groq](src/LLM/Provider/Groq.php)             | [API Key Management][groq_apikey_create]       |
| [Together AI][together_pricing]   | `TOGETHER_API_KEY`    | [Together](src/LLM/Provider/Together.php)     | [API Key Management][together_apikey_create]   |
| [Deepinfra][deepinfra_pricing]    | `DEEPINFRA_API_KEY`   | [Deepinfra](src/LLM/Provider/DeepInfra.php)   | [API Key Management][deepinfra_apikey_create]  |
| [Perplexity][perplexity_pricing]  | `PERPLEXITY_API_KEY`  | [Perplexity](src/LLM/Provider/Perplexity.php) | [API Key Management][perplexity_apikey_create] |
| [Anyscale][anyscale_pricing]      | `ANYSCALE_API_KEY`    | [Anyscale](src/LLM/Provider/Anyscale.php)     | [API Key Management][anyscale_apikey_create]   |
| [OctoAI][octoai_pricing]          | `OCTOAI_API_KEY`      | [OctoAI](src/LLM/Provider/OctoAI.php)         | [API Key Management][octoai_apikey_create]     |

The supported providers are Enums, which you can pass to the `llm` argument of `InstructriceFactory::create`:

```php
use AdrienBrault\Instructrice\InstructriceFactory;
use AdrienBrault\Instructrice\LLM\Provider\OpenAi;

$instructrice->get(
    ...,
    llm: OpenAi::GPT_4T, // API Key will be fetched from the OPENAI_API_KEY environment variable
);
```

## Supported models

| Strategy | 📄 Text | 🧩 JSON | 🚀 Function |
|----------|---------|---------|-------------|

| Commercial usage 💼 | ✅ Yes | ⚠️ Yes, but | ❌ Nope |
|---------------------|-------|-------------|--------|

### Open Weights

#### Foundation

|                                        | 💼                   | ctx  | [Ollama][o_m] | [Mistral][m_m] | [Fireworks][f_m] | [Groq][g_m] | [Together][t_m] | [DeepInfra][d_m] | [Perplexity][p_m]  | Anyscale | [OctoAI][oa_m] |
|----------------------------------------|----------------------|------|---------------|----------------|------------------|-------------|-----------------|------------------|--------------------|----------|----------------|
| [Mistral 7B][hf_m7b]                   | [✅][apache2]         | 32k  | 🧩            | 🧩 68/s        |                  |             | 📄 98/s         |                  | 📄 88/s !ctx=16k!  | 🧩       | 🧩             |
| [Mixtral 8x7B][hf_mx7]                 | [✅][apache2]         | 32k  | 🧩            | 🧩 44/s        | 🧩 237/s         | 🚀 560/s    | 🚀 99/s         |                  | 📄 119/s !ctx=16k! | 🧩       | 🧩             |
| [Mixtral 8x22B][hf_mx22]               | [✅][apache2]         | 65k  | 🧩            | 🧩 77/s        | 🧩 77/s          |             | 📄 52/s         | 🧩 40/s          | 📄 62/s !ctx=16k!  | 🧩       | 🧩             |
| [Phi-3-Mini-4K][hf_p3_mini_4k]         | [✅][mit]             | 4k   | 🧩            |                |                  |             |                 |                  |                    |          |                |
| [Phi-3-Mini-128K][hf_p3_mini_128k]     | [✅][mit]             | 128k | 🧩            |                |                  |             |                 |                  |                    |          |                |
| [Phi-3-Medium-4K][hf_p3_medium_4k]     | [✅][mit]             | 4k   | 🧩            |                |                  |             |                 |                  |                    |          |                |
| [Phi-3-Medium-128K][hf_p3_medium_128k] | [✅][mit]             | 128k | 🧩            |                |                  |             |                 |                  |                    |          |                |
| [Qwen2 0.5B][hf_qw2_05]                | [✅][apache2]         | 32k  | 🧩            |                |                  |             |                 |                  |                    |          |                |
| [Qwen2 1.5B][hf_qw2_15]                | [✅][apache2]         | 32k  | 🧩            |                |                  |             |                 |                  |                    |          |                |
| [Qwen2 7B][hf_qw2_7]                   | [✅][apache2]         | 128k | 🧩            |                |                  |             |                 |                  |                    |          |                |
| [Llama3 8B][hf_l3_8]                   | [⚠️][llama3_license] | 8k   | 📄            |                | 🧩 280/s         | 🚀 800/s    | 📄 194/s        | 🧩 133/s         | 📄 121/s           | 🧩       | 🧩             |
| [Llama3 70B][hf_l3_70]                 | [⚠️][llama3_license] | 8k   | 🧩            |                | 🧩 116/s         | 🚀 270/s    | 📄 105/s        | 🧩 26/s          | 📄 42/s            | 🧩       | 🧩             |
| [Gemma 7B][hf_g7]                      | ⚠️                   | 8k   |               |                |                  | 🚀 800/s    | 📄 118/s        | 🧩 64/s          |                    | 🧩       |                |
| [DBRX][hf_dbrx]                        | [⚠️][databricks_oml] | 32k  |               |                | 🧩 50/s          |             | 📄 72/s         | 🧩               |                    |          |                |
| [Qwen2 72B][hf_qw2_72]                 | [⚠️][qwen_l]         | 128k | 🧩            |                |                  |             |                 |                  |                    |          |                |
| [Qwen1.5 32B][hf_qw15_32]              | [⚠️][qwen_l]         | 32k  |               |                |                  |             | 📄              |                  |                    |          | 🧩             |
| [Command R][hf_cr]                     | [❌][cc_nc]           | 128k | 📄            |                |                  |             |                 |                  |                    |          |                |
| [Command R+][hf_crp]                   | [❌][cc_nc]           | 128k | 📄            |                |                  |             |                 |                  |                    |          |                |

Throughputs from https://artificialanalysis.ai/leaderboards/providers .

#### Fine Tune

|                                         | 💼                   | ctx  | Base         | [Ollama][o_m] | [Fireworks][f_m] | [Together][t_m] | [DeepInfra][d_m] | [OctoAI][o_m] |
|-----------------------------------------|----------------------|------|--------------|---------------|------------------|-----------------|------------------|---------------|
| [Hermes 2 Pro Mistral 7B][hf_h2p]       | [✅][apache2]         |      | Mistral 7B   | 🧩            | 🧩               |                 |                  | 🧩            |
| [FireFunction V1][hf_ff]                | [✅][apache2]         |      | Mixtral 8x7B |               | 🚀               |                 |                  |               |
| WizardLM 2 7B                           | [✅][apache2]         |      | Mistral 7B   |               |                  |                 | 🧩               |               |
| WizardLM 2 8x22B                        | [✅][apache2]         |      | Mixtral 8x7B |               |                  | 📄              | 🧩               | 🧩            |
| [Capybara 34B][hf_capy]                 | [✅][apache2]         | 200k | Yi 34B       |               | 🧩               |                 |                  |               |
| [Hermes 2 Pro Llama3 8B][hf_h2p_l38b]   | [⚠️][llama3_license] |      | Llama3 8B    | 📄            |                  |                 |                  |               |
| [Hermes 2 Theta Llama3 8B][hf_h2t_l38b] | [⚠️][llama3_license] |      | Llama3 8B    | 📄            |                  |                 |                  |               |
| [Dolphin 2.9][hf_d29]                   | [⚠️][llama3_license] | 8k   | Llama3 8B    | 🧩            |                  | 📄              | 🧩               |               |

### Proprietary

| Provider   | Model               | ctx   |          |
|------------|---------------------|-------|----------|
| Mistral    | Large               | 32k   | ✅ 26/s   |
| OpenAI     | GPT-4o              | 128k  | 🚀 83/s  |  
| OpenAI     | GPT-4o mini         | 128k  | 🚀 140/s |  
| OpenAI     | GPT-4 Turbo         | 128k  | 🚀 28/s  |  
| OpenAI     | GPT-3.5 Turbo       | 16k   | 🚀 72/s  |  
| Anthropic  | Claude 3 Haiku      | 200k  | 📄 88/s  |  
| Anthropic  | Claude 3 Sonnet     | 200k  | 📄 59/s  |  
| Anthropic  | Claude 3 Opus       | 200k  | 📄 26/s  |  
| Google     | Gemini 1.5 Flash    | 1000k | 🧩 136/s |  
| Google     | Gemini 1.5 Pro      | 1000k | 🧩 57/s  |  
| Perplexity | Sonar Small Chat    | 16k   | 📄       |  
| Perplexity | Sonar Small Online  | 12k   | 📄       |  
| Perplexity | Sonar Medium Chat   | 16k   | 📄       |  
| Perplexity | Sonar Medium Online | 12k   | 📄       |

Throughputs from https://artificialanalysis.ai/leaderboards/providers .

Automate updating these tables by scraping https://artificialanalysis.ai , along with chatboard arena elo.?
Would be a good use case / showcase of this library/cli?

### Custom Models

#### Ollama

If you want to use an Ollama model that is not available in the enum, you can use the `Ollama::create` static method:

```php
use AdrienBrault\Instructrice\LLM\LLMConfig;
use AdrienBrault\Instructrice\LLM\Cost;
use AdrienBrault\Instructrice\LLM\OpenAiJsonStrategy;
use AdrienBrault\Instructrice\LLM\Provider\Ollama;

$instructrice->get(
    ...,
    llm: Ollama::create(
        'codestral:22b-v0.1-q5_K_M', // check its license first!
        32000,
    ),
);
```

#### OpenAI

You can also use any OpenAI compatible api by passing an [LLMConfig](src/LLM/LLMConfig.php):

```php
use AdrienBrault\Instructrice\LLM\LLMConfig;
use AdrienBrault\Instructrice\LLM\Cost;
use AdrienBrault\Instructrice\LLM\OpenAiJsonStrategy;

$instructrice->get(
    ...,
    llm: new LLMConfig(
        uri: 'https://api.together.xyz/v1/chat/completions',
        model: 'meta-llama/Llama-3-70b-chat-hf',
        contextWindow: 8000,
        label: 'Llama 3 70B',
        provider: 'Together',
        cost: Cost::create(0.9),
        strategy: OpenAiJsonStrategy::JSON,
        headers: [
            'Authorization' => 'Bearer ' . $apiKey,
        ]
    ),
);
```

#### DSN

You may configure the LLM using a DSN:
- the scheme is the provider: `openai`, `openai-http`, `anthropic`, `google`
- the password is the api key
- the host, port and path are the api endpoints without the scheme
- the query string:
  - `model` is the model name
  - `context` is the context window
  - `strategy` is the strategy to use:
    - `json` for json mode with the schema in the prompt only
    - `json_with_schema` for json mode with probably the completion perfectly constrained to the schema
    - `tool_any`
    - `tool_auto`
    - `tool_function`

Examples:
```php
use AdrienBrault\Instructrice\InstructriceFactory;

$instructrice = InstructriceFactory::create(
    defaultLlm: 'openai://:api_key@api.openai.com/v1/chat/completions?model=gpt-3.5-turbo&strategy=tool_auto&context=16000'
);

$instructrice->get(
    ...,
    llm: 'openai-http://localhost:11434?model=adrienbrault/nous-hermes2theta-llama3-8b&strategy=json&context=8000'
);

$instructrice->get(
    ...,
    llm: 'openai://:api_key@api.fireworks.ai/inference/v1/chat/completions?model=accounts/fireworks/models/llama-v3-70b-instruct&context=8000&strategy=json_with_schema'
);

$instructrice->get(
    ...,
    llm: 'google://:api_key@generativelanguage.googleapis.com/v1beta/models?model=gemini-1.5-flash&context=1000000'
);

$instructrice->get(
    ...,
    llm: 'anthropic://:api_key@api.anthropic.com?model=claude-3-haiku-20240307&context=200000'
);
```

#### LLMInterface

You may also implement [LLMInterface](src/LLM/LLMInterface.php).

## Acknowledgements

Obviously inspired by [instructor-php][instructor-php] and [instructor][instructor-python].

> How is it different from instructor php?

Both libraries essentially do the same thing:
- Automatic schema generation from classes
- Multiple LLM/Providers abstraction/support
- Many strategies to extract data: function calling, json mode, etc
- Automatic deserialization/hydration
- Maybe validation/retries later for this lib.

However, instructice differs with:
- Streaming first.
- Preconfigured provider+llms, to not have to worry about:
  - Json mode, function calling, etc
  - The best prompt format to use
  - Your options for local models
  - Whether streaming works. For example, groq can only do streaming without json-mode/function calling.
- PSR-3 logging
- Guzzle+symfony/http-client support
- No messages. You just pass context, prompt.
  - I am hoping that this choice enables cool things later like supporting few-shots examples, evals, etc
- More flexible schema options
- Higher level abstraction. You aren't able to provide a list of messages, while it is possible with `instructor-php`.

## Notes/Ideas

Things to look into:
- [Unstructured][unstructured_docker]
- [Llama Parse][llama_parse]
- [EMLs][eml]
- [jina-ai/reader][jina_reader] -> This is awesome, `$client->request('GET', 'https://r.jina.ai/' . $url)`
- [firecrawl][firecrawl]

[DSPy][dspy] is very interesting. There are great ideas to be inspired by.

Ideally this library is good to prototype with, but can support more advanced extraction workflows
with few shot examples, some sort of eval system, generating samples/output like DSPy, etc

Would be cool to have a CLI, that accepts a FQCN and a context.
```
instructrice get "App\Entity\Customer" "$(cat some_email_body.md)" 
```

Autosave all input/schema/output in sqlite db. Like [llm][llm_logging]?
Leverage that to test examples, add few shots, evals?

[firecrawl]: https://www.firecrawl.dev
[liform]: https://github.com/Limenius/Liform
[instructor-php]: https://github.com/cognesy/instructor-php/
[instructor-python]: https://python.useinstructor.com
[sf_form]: https://symfony.com/doc/current/components/form.html
[sf_serializer]: https://symfony.com/doc/current/components/serializer.html
[unstructured_docker]: https://unstructured-io.github.io/unstructured/installation/docker.html
[llama_parse]: https://github.com/run-llama/llama_parse
[eml]: https://en.wikipedia.org/wiki/Email#Filename_extensions
[dspy]: https://github.com/stanfordnlp/dspy
[jina_reader]: https://github.com/jina-ai/reader
[psl]: https://github.com/azjezz/psl
[psl_type]: https://github.com/azjezz/psl/blob/next/src/Psl/Type/README.md
[api_platform_json_schema]: https://github.com/api-platform/json-schema
[llm_logging]: https://llm.datasette.io/en/stable/logging.html
[openai_pricing]: https://openai.com/pricing
[openai_gpt4o]: https://platform.openai.com/docs/models/gpt-4o
[openai_gpt4t]: https://platform.openai.com/docs/models/gpt-4-turbo-and-gpt-4
[openai_gpt35t]: https://platform.openai.com/docs/models/gpt-3-5-turbo
[openai_apikey_create]: https://platform.openai.com/api-keys
[ollama]: https://ollama.com
[ollama_h2p]: https://ollama.com/adrienbrault/nous-hermes2pro
[ollama_command_r]: https://ollama.com/library/command-r
[ollama_command_r_plus]: https://ollama.com/library/command-r-plus
[o_m]: https://ollama.com/library
[mistral_pricing]: https://mistral.ai/technology/#pricing
[m_m]: https://docs.mistral.ai/getting-started/models/
[mistral_apikey_create]: https://console.mistral.ai/api-keys/
[fireworks_pricing]: https://fireworks.ai/pricing
[f_m]: https://fireworks.ai/models
[fireworks_apikey_create]: https://fireworks.ai/api-keys
[groq_pricing]: https://wow.groq.com
[g_m]: https://console.groq.com/docs/models
[groq_apikey_create]: https://console.groq.com/keys
[together_pricing]: https://www.together.ai/pricing
[t_m]: https://docs.together.ai/docs/inference-models
[together_apikey_create]: https://api.together.xyz/settings/api-keys
[oooas]: https://github.com/goldspecdigital/oooas
[anthropic_pricing]: https://www.anthropic.com/api
[anthropic_m]: https://docs.anthropic.com/claude/docs/models-overview
[anthropic_apikey_create]: https://console.anthropic.com/settings/keys
[deepinfra_pricing]: https://deepinfra.com/pricing
[d_mixtral]: https://deepinfra.com/mistralai/Mixtral-8x22B-Instruct-v0.1
[d_m]: https://deepinfra.com/models/text-generation
[deepinfra_wizardlm2_22]: https://deepinfra.com/microsoft/WizardLM-2-8x22B
[deepinfra_wizardlm2_7]: https://deepinfra.com/microsoft/WizardLM-2-8x7B
[deepinfra_dbrx]: https://deepinfra.com/databricks/dbrx-instruct
[perplexity_pricing]: https://docs.perplexity.ai/docs/pricing
[p_m]: https://docs.perplexity.ai/docs/model-cards
[perplexity_apikey_create]: https://www.perplexity.ai/settings/api
[anyscale_pricing]: https://docs.endpoints.anyscale.com/pricing/
[anyscale_apikey_create]: https://app.endpoints.anyscale.com/credentials
[deepinfra_apikey_create]: https://deepinfra.com/dash/api_keys
[octoai_pricing]: https://octo.ai/docs/getting-started/pricing-and-billing#text-gen-solution
[octoai_apikey_create]: https://octoai.cloud/settings
[oa_m]: https://octoai.cloud/text?selectedTags=Chat
[databricks_oml]: https://www.databricks.com/legal/open-model-license
[llama3_license]: https://github.com/meta-llama/llama3/blob/main/LICENSE
[apache2]: https://www.apache.org/licenses/LICENSE-2.0
[mit]: https://en.wikipedia.org/wiki/MIT_License
[cc_nc]: https://en.wikipedia.org/wiki/Creative_Commons_NonCommercial_license
[hf_m7b]: https://huggingface.co/mistralai/Mistral-7B-Instruct-v0.2
[hf_h2p]: https://huggingface.co/NousResearch/Hermes-2-Pro-Mistral-7B
[hf_h2p_l38b]: https://huggingface.co/NousResearch/Hermes-2-Pro-Llama-3-8B
[hf_h2t_l38b]: https://huggingface.co/NousResearch/Hermes-2-Theta-Llama-3-8B
[hf_ff]: https://huggingface.co/fireworks-ai/firefunction-v1
[hf_mx22]: https://huggingface.co/mistralai/Mixtral-8x22B-Instruct-v0.1
[hf_mx7]: https://huggingface.co/mistralai/Mixtral-8x7B-Instruct-v0.1
[hf_l3_8]: https://huggingface.co/meta-llama/Meta-Llama-3-8B-Instruct
[hf_l3_70]: https://huggingface.co/meta-llama/Meta-Llama-3-70B-Instruct
[hf_g7]: https://huggingface.co/google/gemma-7b-it
[hf_dbrx]: https://huggingface.co/databricks/dbrx-instruct
[hf_crp]: https://huggingface.co/CohereForAI/c4ai-command-r-plus
[hf_cr]: https://huggingface.co/CohereForAI/c4ai-command-r
[hf_capy]: https://huggingface.co/NousResearch/Nous-Capybara-34B
[hf_d29]: https://huggingface.co/cognitivecomputations/dolphin-2.9-llama3-8b
[hf_qw15_32]: https://huggingface.co/Qwen/Qwen1.5-32B-Chat
[hf_qw2_05]: https://huggingface.co/Qwen/Qwen2-0.5B-Instruct
[hf_qw2_15]: https://huggingface.co/Qwen/Qwen2-1.5B-Instruct
[hf_qw2_7]: https://huggingface.co/Qwen/Qwen2-7B-Instruct
[hf_qw2_72]: https://huggingface.co/Qwen/Qwen2-72B-Instruct
[hf_p3_mini_4k]: https://huggingface.co/microsoft/Phi-3-mini-4k-instruct
[hf_p3_mini_128k]: https://huggingface.co/microsoft/Phi-3-mini-128k-instruct
[hf_p3_medium_4k]: https://huggingface.co/microsoft/Phi-3-medium-4k-instruct
[hf_p3_medium_128k]: https://huggingface.co/microsoft/Phi-3-medium-128k-instruct
[qwen_l]: https://github.com/QwenLM/Qwen/blob/main/Tongyi%20Qianwen%20LICENSE%20AGREEMENT
[sf_bundle]: https://github.com/adrienbrault/instructrice-bundle
[instructor_cookbook]: https://python.useinstructor.com/examples/
[gh_actions_image]: https://github.com/adrienbrault/instructrice/workflows/Tests/badge.svg
[gh_actions_link]: https://github.com/adrienbrault/instructrice/actions?query=workflow%3A%22Tests%22+branch%3Amain
[packagist_image]: https://img.shields.io/packagist/v/adrienbrault/instructrice.svg
[packagist_link]: https://packagist.org/packages/adrienbrault/instructrice
[license_image]: https://img.shields.io/github/license/openai-php/client
