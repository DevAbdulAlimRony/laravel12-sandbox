<?php

namespace App\Http\Controllers;
use Laravel\Ai\Image;
use Laravel\Ai\Audio;

class AiController {
    public function sdk(){
        // The Laravel AI SDK provides a unified, expressive API for interacting with AI providers such as OpenAI, Anthropic, Gemini, and more. 
        // We can build =intelligent agents, generate images etc using this api.
        
        // Install: composer require laravel/ai
        // Publish config and migrations: php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
        // Run migration to migrate agent_conversations and agent_conversation_messages table.
        // Put api key credentials in config/ai.php

        //* Building own Agent:
        // Each agent is a dedicated PHP class that encapsulates the instructions, conversation context, tools, and output schema needed to interact with a large language model.
        // php artisan make:agent SalesCoach (can take --structured also.)
        // See app/Ai/Agents
        $response = SalesCoach::make()->prompt('Analyze this sales transcript...'); // promting, calling agent class.
        // promt('txt', provider: Lab::OpenAI, model:'claude-haiku-4-5-20251001', timeout: 120);
        // For user: ->forUser($user), $response->conversationId.
        // Continue existing conversation: ->continue($conversationId, as: $user)
        // If has structured output schema in agent class, can access: $response['score']
        // Pass attachment: promt('txt', attachments: [ Files\Document::fromStorage('transcript.pdf'), Files\Image::fromStorage('photo.jpg')])
        // Streaming Response: Take a route, ->stream('Analyze this sales transcript...')->then(function (StreamedAgentResponse $response){})
        // Can iterate, broadcast on the streaming promt.
        // Queue:  ->queue($request->input('transcript'))->then()->catch()

        //* Anonymous Agent:
        // Sometimes you may want to quickly interact with a model without creating a dedicated agent class.
        agent(instructions: '', messages: [], tools: [])->promt(); // Can take Schema to produce structured output also.

        //* Images: 
        Image::of('A donut sitting on the kitchen counter')->generate();
        // ->quality('high'), ->landscape(),  ->timeout(120), ->attachments()
        // Can store that image using our filesystem. Can be queued the generation. 

        //* Audio:
        Audio::of('I love coding with Laravel.')->generate();
        // ->female(), ->voice('voice-id-or-name'), ->instructions('Said like a pirate')
        // Can be stored and queued. ->queue()->then().

        //* Transcription:
        Transcription::fromPath('/home/laravel/audio.mp3')->generate(); // Transcript of the given audio.
        // fromStorage(), fromUpload(), ->queued()
        // The diarize method may be used to indicate you would like the response to include the diarized transcript in addition to the raw text transcript, allowing you to access the segmented transcript by speaker

        //* Embeddings: 
        // generate vector embeddings for any given string using the new toEmbeddings method available via Laravel's Stringable class
        Str::of('Napa Valley has great wine.')->toEmbeddings();
        // https://laravel.com/docs/12.x/ai-sdk#embeddings

        // Reranking allows you to reorder a list of documents based on their relevance to a given query.
        // https://laravel.com/docs/12.x/ai-sdk#reranking
        // Files: https://laravel.com/docs/12.x/ai-sdk#files
        
        // Failover:
        (new SalesCoach)->prompt('Analyze this sales transcript...', provider: [Lab::OpenAI, Lab::Anthropic],);

        // Testing:
        // https://laravel.com/docs/12.x/ai-sdk#testing
    }

    public function mcp(){
        // A Laravel MCP server acts as a translator between your private application data and an AI Agent (like Claude, ChatGPT, or a local LLM).
        // You should use an MCP server when you want to give an AI permission to interact with your app directly, rather than just talking about it.
        // You expose a "Tool" via your Laravel MCP server called get_user_order_history.
        // When a customer asks, "Where is my refund?", the AI agent says, "Let me check." It automatically calls your Laravel MCP server, fetches the order status from your DB, sees the refund was processed yesterday, and tells the user.
        // The "Self-Healing" Infrastructure: server said I found the error why this is not working, can I fix it?
        // The "AI Business Analyst": : You expose your Eloquent models (Sales, Products, Subscriptions) via an MCP server.

        // https://laravel.com/docs/12.x/mcp
    }

    public function boost(){
        // Laravel Boost accelerates AI-assisted development by providing the essential guidelines and agent skills
        // https://laravel.com/docs/12.x/boost
    }
}