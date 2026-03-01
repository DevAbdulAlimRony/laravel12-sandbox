<?php

namespace App\Ai\Agents;

use App\Ai\Middleware\LogPrompts;
use App\Ai\Tools\RandomNumberGenerator;
use App\Ai\Tools\RetrievePreviousTranscripts;
use App\Models\Document;
use App\Models\History;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Tools\SimilaritySearch;
use Laravel\Ai\Providers\Tools\WebSearch;

#[Provider(Lab::Anthropic)]
#[Model('claude-haiku-4-5-20251001')]
#[MaxSteps(10)]
#[MaxTokens(4096)]
#[Temperature(0.7)]
#[Timeout(120)]
#[UseCheapestModel] // #[UseSmartestModel]
class SalesCoach implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    // If want to store chat history in database:
    use RemembersConversations;
    // revious messages are automatically loaded and included in the conversation context when prompting. 

    public function __construct(public User $user) {}

    // Get the instructions for the promt
    public function instructions(): Stringable|string
    {
        return 'You are a sales coach, analyzing transcripts and providing feedback and an overall sales strength score.';
    }

    // Get the history of the chat.
    public function messages(): iterable
    {
        return History::where('user_id', $this->user->id)->all();
    }

    // Tools may be used to give agents additional functionality that they can utilize while responding to prompts. 
    // php artisan make:tool RandomNumberGenerator
    public function tools(): iterable
    {
        return [
            new RandomNumberGenerator,

            // The SimilaritySearch tool allows agents to search for documents similar to a given query using vector embeddings stored in your database:
            SimilaritySearch::usingModel(Document::class, 'embedding'),
            // Optional arguments: minSimilarity: 0.7, limit: 10, query: fn ($query) => $query->where('published', true),
            // If want more control, use callback.
            // ->withDescription('Search the knowledge base for relevant articles.'),

            // Provider tools are special tools implemented natively by AI providers, offering capabilities like web searching, URL fetching, and file searching. 
            // The WebSearch provider tool allows agents to search the web for real-time information.
            new WebSearch,
            // (new WebSearch)->max(5)->allow(['laravel.com', 'php.net']),
            // ->location(city: 'New York',region: 'NY',country: 'US');

            // The WebFetch provider tool allows agents to fetch and read the contents of web pages. 
            // The FileSearch provider tool allows agents to search through files stored in vector stores.

        ];
    }

    // Structured output:
    public function schema(JsonSchema $schema): array
    {
        return [
            'feedback' => $schema->string()->required(),
            'score' => $schema->integer()->min(1)->max(10)->required(),
        ];
    }

    // Agents support middleware, allowing you to intercept and modify prompts before they are sent to the provider.
    // php artisan make:agent-middleware LogPrompts (app/Ai/Middleware)
    public function middleware(): array
    {
        return [ new LogPrompts, ];
    }
}