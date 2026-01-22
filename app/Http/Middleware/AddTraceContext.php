<?php
namespace App\Http\Middleware;

use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

class AddTraceContext{
    public function handle($request, $next){
        Context::add('trace_id', Str::uuid()->toString());
        Context::add('user_id', auth()->id());

        return $next($request);
    }
    // nformation added to the context is automatically appended as metadata to any log entries that are written throughout the request. 
    // Now, if we call: 
    Log::info('Attempting payment');
    // Output: [10:01:02] Attempting payment {"trace_id": "abc-123", "user_id": 45}
    // Information added to the context is also made available to jobs dispatched to the queue. 
    
}