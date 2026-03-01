<?php

namespace App\Http\Controllers;
use Illuminate\Http\Client\Batch;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\Pool as HttpClientPool;
use Illuminate\Process\Pipe;
use Illuminate\Process\Pool as ProcessPool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Exception;

class HttpClientController{
   public function HttpClient(){
    // Using Guzzle HTTP client, make outgoing HTTP requests to communicate with other web applications. 
    $response = Http::get('http://example.com');

    //* Methods: $esponse->body(), json(), object(), collect(), resource(), status(), successful(), redirect(), failed(), clientError(), header(), headers().
    // ok()- 200 status code, created()- 201, accepted()- 202, nonContent()- 204, movedPermanantly()- 301, found()- 302, badRequest()- 400, unauthorized()- 401, paymentRequired()- 402, forbidden()- 403, notFound()- 404, requestTimeout()- 408, conflict()- 409, unprocessableEntry()- 422, tooManyRequests()- 429, serverError()- 500.

    Http::get('http://example.com/users/1')['name'];
    Http::get('http://example.com/users', ['page' => 1]); // Query Params
    Http::withQueryParameters(['page' => 1])->get('http://example.com/users');
    Http::dd()->get('http://example.com'); //dumping request.
    Http::withUrlParameters(['endpoint' => 'https://laravel.com', 'page' => 'docs', 'version' => '12.x', 'topic' => 'validation',])->get('{+endpoint}/{page}/{version}/{topic}');
    
    Http::post('http://example.com/users', ['name' => 'Steve', 'role' => 'Network Administrator']); // Request Data
    Http::asForm()->post('http://example.com/users', ['name' => 'Sara']); // Sending mail or data.
    
    Http::withBody(base64_encode($photo), 'image/jpeg')->post('http://example.com/photo'); // Sending raw request body.
    // Send multi part requests: Http::attach()

    Http::withHeaders(['X-First' => 'foo'])->post('http://example.com/users'); // Its merges with existingheaders.
    Http::withHeaders([])->replaceHeaders([])->post();
    
    Http::accept('application/json')->get('http://example.com/users');
    Http::acceptJson()->get('http://example.com/users');

    Http::withBasicAuth('taylor@laravel.com', 'secret')->post(/* ... */);
    Http::withDigestAuth('taylor@laravel.com', 'secret')->post(/* ... */);
    Http::withToken('token')->post(/* ... */); // with bearer token.

    Http::timeout(3)->get(/* ... */); // Maximum number of seconds to wait for a response.
    // connetTimeout()
    
    //* Http::retry(3, 100): maximum number of times the request should be attempted and the number of milliseconds that Laravel should wait in between attempts.
    Http::retry(3, function (int $attempt, Exception $exception) {}); // manually calculate the number of milliseconds to sleep between attempts
    Http::retry([100, 200])->post(/* ... */); // array will be used to determine how many milliseconds to sleep between subsequent attempts
    Http::retry(3, 100, function (Exception $exception, PendingRequest $request) { return $exception instanceof ConnectionException;})->post(); // Retry based on condition, here: retry the request if the initial request encounters an ConnectionException.
    // Can modify the request in that callback also.
    Http::retry(3, 100, throw: false)->post(/* ... */); // No request exception.

    // Immediately execute the given callback if there was a client or server error...
    $response->onError(callable $callback);
    //$response->throw(), throw(function(){}), throwIf(), throwIf(fn (Response $response) => true), throwUnless($condition), hrowUnless(fn (Response $response) => false)
    // throwIfStatus(403), throwUnlessStatus(200).
    Http::post(/* ... */)->throw()->json();

    // By default, RequestException for Http Client messages are truncated to 120 characters when logged or reported.
    Http::truncateExceptionsAt(240)->post(/* ... */);
    // Can do in app/bootsrap.php also, see registered() in bootsrap file.

    // We can use guzzle middleware to manipulate or inspect incoming or outgoing request:
    // withRequestMiddleware(), withResponseMiddleware(), globalRequestMiddleware(), globalResponseMiddleware()
    // Guzzle Options: Http::withOptions([])

    //* Concurrent Request (parallely, not sequntially)
    Http::pool(fn (HttpClientPool $pool) => [
        $pool->get('http://localhost/first'), // $responses[0]
        $pool->get('http://localhost/second'), // $responses[1]

        // Rather than accessing [0] like that ordering, we can give name:
        $pool->as('third')->get('http://localhost/third'), // $responses['first']->ok()

        // pool cant be chained with other methods, can mention here directly:
        $pool->withHeaders($headers)->get('http://laravel.test/test'),
    ], concurrency: 5); // concurrency is optional which determines max num of request in parallel.

    //* Request Batching:
    // Do the same thing as pooling,  but it also allows us to define completion callbacks.
    $batch = Http::batch(fn (Batch $batch) => [
        $batch->get('http://localhost/first'),
        $batch->as('first')->get('http://localhost/first'),
    ])->before()->progress()->then()->catch()->finally()->->concurrency(5)->send();
    // Rather than send(), we can use ->defer() to make it deferrable to gain performance.
    // Inspecting batches: $batch->totalRequests, pendingRequests, failedRequests, processedRequests(), finished(), hasFailures()

    //* Macro:
    // Laravel http Client is macroable, see boot() of appserviceprovider, we made a method github(), use it:
    Http::github()->get('/');

    //* Testing:
    // https://laravel.com/docs/12.x/http-client#testing
   }

   public function process(){
        // Interact with operating system.
        // Laravel provides an expressive, minimal API around the Symfony Process component, allowing you to conveniently invoke external processes from your Laravel application. 
        $result = Process::run('ls -la');
        $result->output(); // command(), successful(), failed(0), errorOutput(), exitCode()
        // throw(): ProcessFailedException if the exit code is greater than zero, throwIf($condition)
        // Process::run('ls -la', function (string $type, string $output){}: Output by callback.
        // Process::run('ls -la')->seeInOutput('laravel'): if contained in the process output.

        Process::path(__DIR__)->run('ls -la'); // Working directory of the process.
        Process::input('Hello World')->run('cat');
        Process::timeout(120)->run('bash import.sh'); // ProcessTimedOutException if exceeds, by default 60s.
        Process::forever()->run('bash import.sh'); // Timeout disabled.
        Process::timeout(60)->idleTimeout(30)->run('bash import.sh'); // Run max 30s before showing output.
        Process::forever()->env(['IMPORT_PATH' => __DIR__])->run('bash import.sh'); // Environment var
        Process::forever()->tty()->run('vim'); // Enable tty mode, not supported on Windows.
        Process::quietly()->run('bash import.sh'); // Output disabled.

        // Pipelines: Output of the one process is the input of the next process
        Process::pipe(function (Pipe $pipe) {
            $pipe->command('cat example.txt');
            $pipe->command('grep -i "laravel"');
            // $pipe->as('first')->command('cat example.txt');
        });
        Process::pipe(['cat example.txt', 'grep -i "laravel"',]); // If do not need to customize the individual processes 
        // Second argument can be a callback for output.

        // Asynchronous Process: 
        // start() method may be used to invoke a process asynchronously. instead of run().
        Process::timeout(120)->start('bash import.sh');
        // Return process id: $process->id();
        // Send a signal to the running process: $process->signal(SIGUSR2);
        // $process->running(), latestOutput(), latestErrorOutput(), $process->wait(), waitUntil(), ensureNotTimedOut()

        // Concurrent Process:
        Process::pool(function (ProcessPool $pool) {
            $pool->path(__DIR__)->command('bash import-1.sh');
             $pool->as('second')->command('bash import-2.sh'); // naming pool process.
        })->start(function (string $type, string $output, int $key) {});
        while ($pool->running()->isNotEmpty()) {}
        $results = $pool->wait();
        // Process::concurrently(function (Pool $pool) {})
        // $pool->running()->each->id(); $pool->signal(SIGUSR2);

        // Testing:
        // https://laravel.com/docs/12.x/processes#testing
    }        
}
