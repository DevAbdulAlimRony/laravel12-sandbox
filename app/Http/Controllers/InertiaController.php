<?php

namespace App\Http\Controllers;

use Inertia\Inertia;


class InertiaController extends Controller
{
    // Approach to build server driven web apps.
    // Inertia allows you to create fully client-side rendered, single-page apps, without the complexity that comes with modern SPAs. We can use server side patterns.
    // Inertia has no client-side routing, nor does it require an API. Simply build controllers and page views.
    // Inertia isn’t a framework, nor is it a replacement for your existing server-side or client-side frameworks. Rather, it’s designed to work with them. 

    // Install: composer require inertiajs/inertia-laravel
    // Root Template for first visit page: resources/views/app.blade.php, see that into the files.
    // By default, Inertia’s Laravel adapter will assume your root template is named app.blade.php. You may change this using the Inertia::setRootView() method.

    // Register Middleware: php artisan inertia:middleware
    // Now, add the middleware in bootsrap/app.php
    // $middleware->web(append: [ HandleInertiaRequests::class])
    // Those are all setup we need for backend, now we can work.

    public function index(Event $event)
    {
        // return inertia('Welcome');

        return Inertia::render('User/Event/Show', ['event' => $event->only('id')]); // So, the frontend should go into resources/js/Pages/User/Event/Show.vue.
        // Transforming component name in client side: Inertia::transformComponentUsing(function (string $name): string { return "{$name}/Page"; });
        // Can use inertia() helper rather than facade.
        
        //* Component names can be from backend enum
        enum Page: string
        {
            case EventShow = 'Event/Show';
            case EventIndex = 'Event/Index';
        }
        // Inertia::render(Page::EventShow, ['event' => $event->only('id')]);

        //* Client Side Setup
        // Install: npm install @inertiajs/vue3 @inertiajs/vite
        // In vite, add inertia() into plugins: [].
        // Vue Main App: import { createInertiaApp } from '@inertiajs/vue3', createInertiaApp().
        // createInertiaApp({pages: './AppPages',}), also can add path, extension, lazy and transform options.
        // Using external plugins: createInertiaApp({ withApp(app) {  app.use(i18n) } }). app.provide(), app.component(). 
        // Using axios: import { axiosAdapter } from '@inertiajs/core', then in createApp: http: axiosAdapter().
        // Defining root element in createApp:  id: "my-app"

        // To see others: See Inertia.vue.

        // If you attempt to render a page that does not exist, the response will typically be a blank screen. To prevent this, you may set the inertia.ensure_pages_exist configuration option to true. The Laravel adapter will then throw an Inertia\ComponentNotFoundException when a page cannot be found.


        //* Passing properties:
        Inertia::render('Dashboard', [
            'title' => 'Dashboard',
            'settings' => ['theme' => 'dark', 'notifications' => true],
            'user' => auth()->user(),
            'profile' => new UserResource(auth()->user()),
            'data' => new JsonResponse(['key' => 'value'])
        ]);

        // Can implements ProvidesInertiaProperty interface and call constructor to pass properties to Inertia::render().
        // public function toInertiaProperty(PropertyContext $context): mixed
        // The PropertyContext gives you access to the property key, which enables powerful patterns like merging with shared data.
        // Inertia::getShared($context->key, []);
        // Inertia::getShared($context->key, []); 
        // 'notifications' => new MergeWithShared(['New message received'])
        // Also can group common properties: 
        public function __construct(#[CurrentUser] protected User $user){}
        public function toInertiaProperties(RenderContext $context): array {
            return [
                'canEdit'   => $this->user->can('edit'),
                'canDelete' => $this->user->can('delete'),
                'canPublish' => $this->user->can('publish'),
                 'isAdmin'   => $this->user->hasRole('admin'),
            ];
        }
        // Inertia::render('UserProfile')->with($permissions);
        
        // Sometimes, we need prop in only view root, not in vue comp:
        Inertia::render('Event', ['event' => $event]) ->withViewData(['meta' => $event->meta]);
        // Use in view: <meta name="description" content="{{ $meta }}">

        // To enable client-side history navigation, all Inertia server responses are stored in the browser’s history state. However, keep in mind that some browsers impose a size limit on how much data can be saved within the history state.
        // For example, Firefox has a size limit of 16 MiB and throws a NS_ERROR_ILLEGAL_VALUE error if you exceed this limit. 


        //* Redirect:
        return to_route('users.index');
        // When redirecting after a PUT, PATCH, or DELETE request, you must use a 303 response code, otherwise the subsequent request will not be treated as a GET request. 

        // Sometimes a user may visit a URL with a fragment, such as /article/old-slug#section, and the server needs to redirect to a different URL. 
        redirect('/article/new-slug')->preserveFragment();

        Inertia::location($url); // Redirect to a external url.

        //* Routing:
        // When using Inertia, all of your application’s routes are defined server-side. This means that you don’t need Vue Router or React Router.
        // If dont need corresponding controller like about page:
        Route::inertia('/about', 'About');

        // Can generate urls:
        return Inertia::render('Users/Index', ['users' => User::all()]), 'create_url' => route('users.create');
        // When using Wayfinder, you can pass the generated TypeScript method directly to the Link component, form helpers, or router methods and Inertia understands how to handle it. 
        // The Ziggy library can make your named, server-side routes available to you via a global route() function.
        // Customize the page URLL in HandleInertiaRequest middleware:  public function urlResolver()..

        // Full documentation from here: https://inertiajs.com/docs/v3/the-basics/title-and-meta
    }
}