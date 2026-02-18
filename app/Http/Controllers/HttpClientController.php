<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;

class HttpClientController{
    // Using Guzzle HTTP client, make outgoing HTTP requests to communicate with other web applications. 
    $response = Http::get('http://example.com');

    
}