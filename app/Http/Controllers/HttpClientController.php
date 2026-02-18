<?php

namespace App\Http\Controllers;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Batch;
use Exception;

class HttpClientController{
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
    Http::pool(fn (Pool $pool) => [
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