<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stub for documentation. Audit log for context/trail examples.
 */
class AuditLog extends Model
{
    protected $fillable = ['user_id', 'source', 'changes'];
}
