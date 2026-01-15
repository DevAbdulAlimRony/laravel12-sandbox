<?php
// View composers are callbacks or class methods that are called when a view is rendered.
// It is useful when we have same data or route for different view to share.

// Register this composer into AppServiceProvider's boot method.
// After registering the composer as 'profile', the compose method will be executed each time the profile.blade.php is rendered.

class ProfileComposer
{
    // All view composers are resolved via the service container.
    // so you may type-hint any dependencies you need within a composer's constructor.
    public function __construct(
        protected User $users,
    ) {}

    public function compose(View $view): void
    {
        $view->with('count', $this->users->count());
    }
}