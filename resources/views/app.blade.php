<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @vite('resources/js/app.js')
        <x-inertia::head />
        <!-- or, @inertiaHead -->

        <!-- For React applications, it’s recommended to include the @viteReactRefresh directive before the @vite directive to enable Fast Refresh in development. -->
        
    </head>
    <body>
        <!-- It renders a <div> element with an id of app -->
        <x-inertia::app />
        <!-- or, @inertia -->
        <!-- <x-inertia::app id="custom-app-id" /> or using blade directive @inertia('custom-app-id'),  be sure to update custom id in client-side as well. -->
    </body>
</html>