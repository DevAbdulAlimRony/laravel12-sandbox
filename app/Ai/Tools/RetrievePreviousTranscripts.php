<?php

namespace App\Ai\Tools;

use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Stub for documentation. Retrieves previous conversation transcripts.
 */
class RetrievePreviousTranscripts implements Tool
{
    public function description(): string
    {
        return 'Retrieve previous transcripts';
    }

    public function inputSchema(): array
    {
        return [];
    }

    public function run(Request $request): mixed
    {
        return [];
    }
}
