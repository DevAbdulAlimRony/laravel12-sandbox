<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <script>

    </script>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        //* Blade: 
        <!-- If we remove .blade from file name, it will render the page. But blade syntaxes will come as plain html
        After refreshing the page, blade file will be build as html and regular php template into storage/framework/views
        If we refresh, laravel checks if any modification happenned and if then recomile html file again, which is time consuming.
        So, we can cache in production: php artisan view:cache.
        Clear Cache: php artisan view:clear , or, simple delete the cached view files from storage manually. -->
        <!-- Creating View: php artisan make:view home -->
        <!-- Views separate your controller / application logic from your presentation logic -->
        <!-- Rather than using blade, we can use any starter kits like inertia and vue which use shadcdn/vue and tailwindcss -->
        <!-- Blade is the simple, yet powerful templating engine that is included with Laravel.  -->
        <!-- We can use livewire to make our blade modern, reactive and dynamic like vue or react. -->

        <!-- Accessing passed data from route or controller: -->
        <!-- Using Raw php -->
        <h2><?= $name ?></h2>

        <!-- Write raw php using php directive -->
        @php echo $name; @endphp

        <!-- Using Blade Syntax -->
         <h2>{{ $name }}</h2>
        <!-- Its actually use that raw php syntax behind the scene but apply htmlspecialchars($name) to prevent attach using a helper function e -->
        <h2><?php echo e($name); ?></h2>
         <!-- If we dont want that escaping, use this syntax: -->
         {!! $name !!}
         <!-- Now, this is not escaped, if pass html into that variable it will generate html rather than string that is a good candidate for XSS attach -->
         <!-- If we dont want double encoding globally in AppServiceprovider's boot: Blade::withoutDoubleEncoding(); -->

         <!-- If we want escape {{}} itself or any directive: Hello, @{{ name }}- output will be Hello {{name}} -->
         <!-- For directive: @@if(), Output:@if() -->

         <!-- Render an arry as json in order to initialize a JavaScript variable.: -->
        <?php echo json_encode($array); ?>; <!-- Unescaped which is not good. -->
        {{ Illuminate\Support\Js::from($array) }}; <!-- Escaped. or you can use Js::from facade -->

        <!-- If we display javascript variable in a large portion of template, wrap into @verbatim -->
        @verbatim
            <div class="container">
                Hello, {{ name }}.
            </div>
        @endverbatim

        <!-- We can write regular php code in blade syntax also -->
         {{ date('d/m/Y') }}

         <!-- HTML syntax for comment will be rendered in browser's html -->
         {{-- But this blade syntax for comment wont render, which is recommended. --}}

         <!-- View Composers: -->
         <!-- View composers are callbacks or class methods that are called when a view is rendered. -->
         <!-- It is useful when we have same data or route for different view to share -->
         <!-- See app/Views/Composers directory -->

         <!-- All Blade Directives: -->
         <!-- Directives are just shortcuts which provide a very clean, terse way of working with PHP control structures.-->
         <!-- @if @elseif @else @endif -->
         <!-- @unless @endunless -->
         <!-- Records is defined and is not nul: @isset @endisset -->
         <!-- Records is empty: @empty @endempty -->
         <!-- @auth @endauth, @guest @endguest. @auth('admin'), @guest('admin') -->
         <!-- Check if app running in that environment: @production @endproduction, @env('staging') @endenv, @env(['staging', 'production']) -->
         <!-- If a template inheritance section has content: @hasSection('navigation') @endif,  @sectionMissing('navigation') @endif -->
         <!-- If a session value exists: @session('status')  @endsession -->
         <!-- If a context value exists: @context('canonical') @endcontext -->
         <!-- switch, @case, @break, @default, @endswitch -->
         <!-- @for ($i = 0; $i < 10; $i++) @endfor -->
         <!-- Other Loops: @foreach ($users as $user), @forelse ($users as $user) @empty @endforelse, @while (true), @continue, @break -->
         <!-- With condition continue or break: @continue($user->type == 1), @break($user->number == 5) -->
         <!-- In foreach loop, we will get a $loop variable: $loop->first, $loop->last
         If nested loop, access parent's index or first last: $loop->parent->first  -->
         <!-- Other properties of loop variable: index, iteration, remaining, count, first, last, even, odd, depth, parent. -->
         
         <!-- Class Directive to generate class conditionally -->
         @php
            $isActive = false;
            $hasError = true;
        @endphp
        <span @class(['p-4', 'font-bold' => $isActive, 'text-gray-500' => ! $isActive, 'bg-red' => $hasError,)></span>
        <!-- Same goes for @style directory -->

        <!-- @check directive to check if a checkbox is checked:  @checked(old('active', $user->active)) -->
        <!-- Same goes for @selected -->
        <!-- Same goes for  @disabled, @required and @readonly -->
        <!-- @include directive allows you to include a Blade view from within another view. -->
        <!-- Included view will inherit all data available in the parent view, but we can pass any data: @include('view.name', ['status' => 'complete']) -->
        <!-- If you attempt to @include a view which does not exist, Laravel will throw an error.  -->
        <!-- Can use:  @includeIf('view.name', ['status' => 'complete']), dont show error if not present. -->
        <!-- @includeWhen($boolean, 'view.name', ['status' => 'complete']) -->
        <!-- @includeUnless($boolean, 'view.name', ['status' => 'complete']) -->
        <!-- @includeFirst(['custom.admin', 'admin'], ['status' => 'complete']) -->
        <!-- Combine loops and includes into one line: @each('view.name', $jobs, 'job') -->
        <!-- @once  directive allows you to define a portion of the template that will only be evaluated once per rendering cycle.  -->
        <!-- @pushOnce('scripts')  @endPushOnce -->

        <!-- Avoid using the __DIR__ and __FILE__ constants in your Blade views, since they will refer to the location of the cached, compiled view. -->

        <!-- If you only need to use PHP to import a class -->
        @use('App\Models\Flight')
        @use('App\Models\{Flight, Airport}')
        @use(function App\Helpers\format_currency)
        @use(const App\Constants\MAX_ATTEMPTS)
        @use('App\Models\Flight', 'FlightModel') <!-- Aliased -->

        <!-- Dependency Injectin: -->
        <!-- @inject('metrics', 'App\Services\MetricsService') -->

        <!-- Form Handling and Validation -->

        <!-- CSRF Protection -->
        <!-- Cross-site request forgeries are a type of malicious exploit whereby unauthorized commands are performed on behalf of an authenticated user.  -->
        <!-- Imagine your application has a /user/email route that accepts a POST request to change the authenticated user's email address. -->
        <!-- Without CSRF protection, a malicious website could create an HTML form that points to your application's /user/email route and submits the malicious user's own email address -->
        <!-- To prevent this vulnerability, we need to inspect every incoming POST, PUT, PATCH, or DELETE request for a secret session value that the malicious application is unable to access. -->
        <!-- Laravel automatically generates a CSRF "token" for each active user session managed by the application. -->
        <!-- This token is used to verify that the authenticated user is the person actually making the requests to the application.  -->
        <!-- Anytime you define a "POST", "PUT", "PATCH", or "DELETE" HTML form in your application, you should include a hidden CSRF _token field in the form so that the CSRF protection middleware can validate the request. -->
        <form method="POST" action="/profile">
            @csrf
            <!-- Equivalent to... -->
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <!-- ValidateCsrfToken middleware, which is included in the web middleware group by default, will automatically verify that the token in the request input matches the token stored in the session. -->
        </form>
        <!-- For SPA application, it can be handled by Sanctum -->
        <!-- If we want to exclude any url without csrf like Stripr, you should place these kinds of routes outside of the web middleware group or -->
        <!--add  ....$middleware->validateCsrfTokens(except:.. from documentation in bootstrap/app.php -->
        
        <!-- X-CSRF-TOKEN: We can store x-csrf-token in header: <meta name="csrf-token" content="{{ csrf_token() }}"> then can instruct jquery or any library to add the token to all request headers. -->
        <!-- X-XSRF-TOKEN: By default, the resources/js/bootstrap.js file includes the Axios HTTP library which will automatically send the X-XSRF-TOKEN header for you. -->
    </body>
</html>
