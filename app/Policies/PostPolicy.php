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
    // If we dont need any method, we can remove it.

    public function create(User $user): bool {
        return $user->role == 'market_incharge';
    }

    // Determine if the given post can be updated by the user.
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
        // or, $user->is($post->user) - But it will execute a query if not already not loaded, so previous one is good.
        // or, $category->user()->is($user). This wont run any additional query.
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

    // Let's say we have same logic in update and delete. Rather than taking two methods, we can take one method manage().

    // Finally, we can call the policy in controller, or route middleware or in FormRequest's authorize method.
    // Recommended: use in route level as middleware and FormRequest's authorize method if you are using FormRequest. 
    // Using in just one place will work, but in case, so we use in both places.
}