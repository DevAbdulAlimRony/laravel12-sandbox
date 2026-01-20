// Vite is a modern frontend build tool that provides an extremely fast developent environment addEventListener,
// budles our application's css and javascript files into production-ready assets
// if we reference assets with an absolute path, Vite will not include the asset in the build
// We should ensure that the asset is available in your public directory. 

// Nodejs and NPM must be installed to run vite.
// Running the vite: npm run dev, npm run build.

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        // Entry Point
        // If we build spa using inertia, dont need css entry point, just js. and Import css in app.js.
        // The Laravel plugin also supports multiple entry points and advanced configuration options such as SSR entry points.
        // laravel(['resources/js/app.js'])
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],

            // Server Side Rendering (SSR) entry point:
            ssr: 'resources/js/ssr.js',
            // Can add in package.json: "build": "vite build && vite build --ssr"
            // Then to build ssr server: npm run build, node bootstrap/ssr/ssr.js
            // If inertia ssr: php artisan inertia:start-ssr

            // If we use traditional blade based server side rendering, can add this to refresh when new update automatically.
            refresh: true,
            // After new save, browser will perform full page refresh for livewire, view/componets, lang, views and routes.

            // If we use ziggy package to use laravel route in javascript, we can use specify our own custom lists to refresh if necessary:
            // refresh: ['resources/views/**']

            // More customization for refresh:
            // refresh: [{
            //     paths: ['resources/views/**', 'and-others/**'],
            //     config: { delay: 300 }
            // }],

            // Some package like vite-imgtool cant properly define dev server, so we can specify:
            transformOnServe: (code, devServerUrl) => code.replaceAll('/@imagetools', devServerUrl + '/@imagetools'),
        }),
        // Now, we can call them in our blade view: @vite(['resources/css/app.css', 'resources/js/app.js'])
        // We can also specify where to build: @vite('resources/js/app.js', 'vendor/courier/build')
        // Raw conten of asset rather than linking to the versioned URL using Facade: {!! Vite::content('resources/css/app.css') !!}

        tailwindcss(),

        // If we want to use vue, Install @vitejs/plugin-vue: npm install --save-dev @vitejs/plugin-vue
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        // Laravel's starter kits already include the proper Laravel, Vue, and Vite configuration.

        // Same goes for react
        // Also provides a convenient resolvePageComponent function to help you resolve your Inertia page components.
        // We can do code split for vite.config.js with inertia, configuring asset prefetching.
        // Laravel's starter kits already include the proper Laravel, Inertia, and Vite configuration.

        // If we want to process and version static assets like image or fonts for blade,
        // Add in app.js: import.meta.glob([../images/**',  '../fonts/**',])
        // Can access in blade: <img src="{{ Vite::asset('resources/images/logo.png') }}">
        // We can defne a macro in a service provider for the path as an alias in boot method:
        // Vite::macro('image', fn (string $asset) => $this->asset("resources/images/{$asset}"));
        // Now, can use: <img src="{{ Vite::image('logo.png') }}" alt="Laravel Logo">
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },

        // Add it When running the Vite development server within Laravel Sail on Windows Subsystem for Linux 2 (WSL2)
        hmr: {
            host: 'localhost',
        },
    },

    // By default laravel has this alias:  '@' => '/resources/js'
    // If we want to change this:
    resolve: {
        alias: {
            // '@': '/resources/ts',
        },
    },

    //* Using CDN:
    // If compiled assets ae deployed to a domain separate from the application like a CDN
    // Specify CDN Link as ASSET_URL in .env

    // If we want to write any env variable for javascript, we can prefix with VITE_
    // VITE_SENTRY_DSN_PUBLIC=http://example.com
    // Then access it in js: import.meta.env.VITE_SENTRY_DSN_PUBLIC

    // During testing we can disable vite: $this->withoutVite();

    //* Security: Content Security Policy (CSP) Nonce
    // A Content Security Policy (CSP) is a security layer that helps detect and mitigate certain types of attacks, including Cross-Site Scripting (XSS) and data injection.
    // A Nonce (number used once) is a unique, cryptographically strong random string generated for every single page request.
    // When you implement a strict CSP, the browser blocks all inline scripts (<script>...</script>) by default because it can't tell the difference between your code and a hacker's malicious script.
    // To tell the browser "this specific inline script is safe," you attach a nonce attribute to your script tag.
    // We can add none for our js and css assets, and also for route in ziggy package if we use it.
    // Take a custom middleware and call: Vite::useCspNonce();  return $next($request)->withHeaders(['Content-Security-Policy' => "script-src 'nonce-".Vite::cspNonce()."'",)... in handle method.
    // After invoking the useCspNonce method, Laravel will automatically include the nonce attributes on all generated script and style tags.
    // If we want to specify elsewhere like ziggy: @routes(nonce: Vite::cspNonce()), Vite::useCspNonce($nonce)- use already have none.

    //* Security: Subresource Integrity (SRI):
    // Subresource Integrity (SRI) is a security feature that allows browsers to verify that the files they fetch (usually from a CDN or external server) haven't been tampered with or modified.
    // It works by providing a cryptographic hash of the file. The browser downloads the file, calculates its hash, and compares it to the one you provided. If they don't match, the browser refuses to execute the file.
    // Even if your own server is secure, you likely rely on third-party "subresources" like Google Fonts, Bootstrap, or jQuery. This creates a Supply Chain Risk.
    // If a hacker breaks into a popular CDN (like cdnjs or unpkg) and injects a tiny bit of malicious code into a common library, they can instantly steal data from every website using that library.
    // An attacker on a public Wi-Fi could intercept the request for a JavaScript file and swap it with a malicious version as it travels to the user’s browser.
    // After Using SRI, our script tag will have this attribute: integrity="sha384-Li9vy3DqF8tnMhKPPS09f0FfGCIa4f123abc..." crossorigin="anonymous"
    // By default, Vite does not include the integrity hash, we can use a package:
    // npm install --save-dev vite-plugin-manifest-sri
    // Then here in vite.config, just import and use.
    // import manifestSRI from 'vite-plugin-manifest-sri'; and in plugins: manifestSRI()
    // We can also customize manifest key: Vite::useIntegrityKey('custom-integrity-key')
    // Disable auto detection completely: Vite::useIntegrityKey(false);

    //* Advanced Cusomization:
    // We can customized vite in blade's head tag {{ Vite::useHotFile()->->useBuildDirectory('bundle')...}}
    // Then in vite config we can specify those customization: laravel({uildDirectory: 'bundle'})

    //* Cross Origin Resource Sharing (CORS):
    server: {
        cors: true, // This allows requests from any origin (for development)
        // Or you can specify specific origins:
        // cors: {
        //   origin: 'http://localhost:3000', // Replace with your application's origin
        // or,
        // origin: [
        //     'https://backend.laravel',
        //     'http://admin.laravel:8566',
        //      /^ https ?: \/\/.*\.laravel(:\d+)?$/, - Regex pattern.
        // ],  
        // or, just update APP_URL in env file- easiest and recommended way.
        // },
    },
});
