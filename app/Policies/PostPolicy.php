<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    // We can give any name for the method.
    // If we use --model prefix when gereating, we will get: viewAny, view, create, update, delete, restore, forceDelete.
    // We can type-hint any relative dependencies within those methods.

    public function create(User $user): bool {
        return $user->role == 'market_incharge';
    }

    // Determine if the given post can be updated by the user.
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): Response
    {
        return $user->id === $post->user_id ? Response::allow() : Response::deny('You do not own this post.');
        // denyWithStatus(404), denyAsNotFound().
    }

    // Access to the Guset user also, using null default value:
    public function view(?User $user, Post $post): bool{
        return $user?->id === $post->user_id;
    }

    // Give all acees to specific user
    public function before(User $user, string $ability): bool|null{
        if ($user->isAdministrator()) {
            return true;
        }
        return null;
    }

    // Can supply additional context as we did for gate:
    public function viewAny(User $user, Post $post, int $category): bool{
        return $user->id === $post->user_id && $user->canUpdateCategory($category);
        // Call in controller: Gate::authorize('update', [$post, $request->category]);
    }
}