<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

//* Global Scope:
// If we call this scope in any model, that model's every query will get this constraint automatically.
// To use in model, above class definition: #[ScopedBy([AncientScope::class])] or,
// Manually register the global scope by overriding the model's booted method and invoke the model's addGlobalScope method.
class AncientScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('created_at', '<', now()->minus(years: 2000));

        // If adding columns in global scope, use addSelect rather than select.
        // This will prevent the unintentional replacement of the query's existing select clause.
    }
}