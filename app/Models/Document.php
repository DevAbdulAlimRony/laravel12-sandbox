<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stub for documentation. Document model for vector/similarity search.
 */
class Document extends Model
{
    protected $fillable = ['team_id', 'embedding'];
}
