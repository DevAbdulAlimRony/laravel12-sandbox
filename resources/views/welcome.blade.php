<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head></head>
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
         <!-- NOw, this is not escaped, if pass html into that variable it will generate html rather than string that is a good candidate for XSS attach -->

        <!-- We can write regular php code in blade syntax also -->
         {{ date('d/m/Y') }}

         <!-- HTML syntax for comment will be rendered in browser's html -->
         {{-- But this blade syntax for comment wont render, which is recommended. --}}

         <!-- View Composers: -->
         <!-- View composers are callbacks or class methods that are called when a view is rendered. -->
         <!-- It is useful when we have same data or route for different view to share -->
         <!-- See app/Views/Composers directory -->

         <!-- All Blade Directives: -->
    </body>
</html>
