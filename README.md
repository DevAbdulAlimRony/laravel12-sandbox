# Laravel Sandbox Learning Roadmap

This is an **open-source Laravel documentation** repository—anyone can learn from it, improve it, and contribute back.

---

## Table of learning path (complete order — zero to advanced)

Follow this table step by step. Every topic in the project is listed; do not skip. Each step tells you what to learn and exactly which files or classes to study.

| Step | Topic | Files / classes to study |
| --- | --- | --- |
| **1** | Installation, editor tooling, and environment | `app/1. Install.txt`, `composer.json`, `.env.example`, `docker/` (Dockerfiles, php.ini, supervisord, start-container), `compose.yaml` |
| **2** | JavaScript / PHP package manifests and scripts | `app/2. json.txt`, `composer.json`, `package.json`, `package-lock.json` |
| **3** | Support and public files that power requests | `app/3. other-files.txt`, `public/.htaccess`, `public/robots.txt`, `public/index.php`, `.gitignore`, `.gitattributes`, `.editorconfig` |
| **4** | Configuration, helpers, and environment | `app/config.txt`, `config/app.php`, `config/auth.php`, `config/cache.php`, `config/database.php`, `config/filesystems.php`, `config/logging.php`, `config/mail.php`, `config/queue.php`, `config/services.php`, `config/session.php` |
| **5** | Request lifecycle, directory layout, and service providers | `app/4. Lifecycle and Directory.txt`, `bootstrap/app.php`, `bootstrap/providers.php`, `routes/web.php`, `routes/console.php` |
| **6** | Artisan, Tinker, and console-first flows | `app/5. artisan and tinker.txt`, `artisan` (root script) |
| **7** | Service container and dependency injection | `app/Http/Controllers/BasicController.php`, `app/Providers/AppServiceProvider.php` (bindings, singleton) |
| **8** | Routing — basics (verbs, closure, redirect, view, fallback, parameters) | `routes/web.php` (Route::get, match, any, redirect, view, fallback, route parameters, optional params, where, Route::pattern) |
| **9** | Routing — route model binding and Enums | `routes/web.php` (implicit binding, slug, scopeBindings, withTrashed), `app/Enums/FileType.php` |
| **10** | Routing — groups, prefix, name, controller | `routes/web.php` (prefix, name, controller, domain, middleware groups) |
| **11** | Routing — resource, apiResource, singleton, nested, shallow | `routes/web.php` (resource, apiResource, apiResources, singleton, creatable, destroyable, photos.comments, shallow, parameters, names, missing, withTrashed, middlewareFor, scoped) |
| **12** | Controllers — basic and invokable | `app/Http/Controllers/BasicController.php`, `app/Http/Controllers/MainController.php` (overview), `app/Http/Controllers/InvokableController.php` |
| **13** | Request and Response objects | `app/Http/Controllers/RequestResponseController.php` (Request, UploadedFile, response helpers, cookies, redirect) |
| **14** | Middleware | `app/Http/Middleware/EnsureTokenIsValid.php`, `app/Http/Middleware/AddTraceContext.php`, `app/Ai/Middleware/LogPrompts.php` |
| **15** | Validation — rules and Form Requests | `app/Http/Controllers/ValidationController.php`, `app/Http/Requests/StorePostRequest.php`, `app/Rules/UpperCase.php` |
| **16** | Views and Blade | `resources/views/welcome.blade.php`, `resources/views/components/alert.blade.php` |
| **17** | View composers | `app/Views/Composers/ProfileComposer.php` |
| **18** | Frontend assets (Vite, JS, CSS) | `resources/js/app.js`, `resources/js/bootstrap.js`, `resources/css/app.css`, `vite.config.js`, `package.json` |
| **19** | Database migrations | `database/migrations/0001_01_01_000000_create_users_table.php`, `database/migrations/0001_01_01_000001_create_cache_table.php`, `database/migrations/0001_01_01_000002_create_jobs_table.php` |
| **20** | Seeders and factories | `database/seeders/DatabaseSeeder.php`, `database/factories/UserFactory.php` |
| **21** | Eloquent models — basics (table, key, fillable, timestamps) | `app/Models/User.php`, `app/Models/Flight.php` (table name, primary key, HasFactory, timestamps) |
| **22** | Eloquent — relationships (hasMany, belongsTo, morphTo, etc.) | `app/Models/Flight.php` (relationships section), `app/Models/Order.php` |
| **23** | Eloquent — global and local scopes | `app/Models/Scopes/AncientScope.php`, `app/Scopes/DestinationFilter.php`, `app/Scopes/Paginate.php`, `app/Models/Flight.php` (scopes) |
| **24** | Eloquent — attribute casting and custom casts | `app/Casts/AsJson.php`, `app/Casts/AsAddress.php`, `app/Models/Flight.php` (casts, AsCollection) |
| **25** | Value objects | `app/ValueObjects/Money.php`, `app/ValueObjects/Option.php` |
| **26** | Enums | `app/Enums/FileType.php` |
| **27** | Eloquent — observers and model events | `app/Observers/UserObserver.php`, `app/Models/Flight.php` (ObservedBy, ShouldHandleEventsAfterCommit) |
| **28** | Eloquent — pruning, soft deletes, pivot | `app/Models/Flight.php` (pruning, soft deletes, Pivot, morphable) |
| **29** | Authentication | `app/Http/Controllers/AuthController.php`, `app/Helpers/Auth.php`, `config/auth.php` |
| **30** | Authorization — Gates and Policies | `app/Http/Controllers/AuthController.php` (Gate), `app/Policies/PostPolicy.php` |
| **31** | API Resources (JSON transformation) | `app/Http/Resources/UserResource.php`, `app/Http/Controllers/MainController.php` (toArray, toResource, With, UseResource) |
| **32** | Contracts and service binding (payment example) | `app/Contracts/PaymentProcessor.php`, `app/Services/Bkash.php`, `app/Services/BkashProcessor.php`, `app/Services/RocketProcessor.php`, `app/Providers/AppServiceProvider.php` (bindings) |
| **33** | Deferrable service providers | `app/Providers/PaymentServiceProvider.php` |
| **34** | Custom Facades | `app/Facades/Payment.php`, `app/Providers/AppServiceProvider.php` |
| **35** | HTTP Client | `app/Http/Controllers/HttpClientController.php` |
| **36** | Caching | `app/Http/Controllers/CacheController.php`, `config/cache.php` |
| **37** | Logging | `app/Http/Controllers/LoggingController.php`, `config/logging.php` |
| **38** | Mail (Mailable) | `app/Mail/OrderShipped.php`, `config/mail.php` |
| **39** | Notifications (database, mail, channels) | `app/Notifications/InvoicePaid.php`, `app/Http/Controllers/NotificationController.php` |
| **40** | Events (event classes) | `app/Events/PodcastProcessed.php`, `app/Events/OrderShipped.php` (ShouldBroadcast, ShouldDispatchAfterCommit) |
| **41** | Listeners and event subscribers | `app/Listeners/SendPodcastNotification.php`, `app/Listeners/SendShipmentNotification.php`, `app/Listeners/OrderEventSubscriber.php` |
| **42** | Event flow and dispatching | `app/Http/Controllers/EventListenerController.php` |
| **43** | Broadcasting (channels, presence) | `app/Http/Controllers/BroadcastController.php`, `app/Http/Controllers/BroadcastingController.php`, `app/Broadcasting/OrderChannel.php`, `routes/channels.php` |
| **44** | Queues and Jobs | `app/Http/Controllers/QueueController.php`, `app/Jobs/ProcessPodcast.php` (ShouldQueue, ShouldBeUnique, WithoutRelations), `config/queue.php` |
| **45** | Console commands (Artisan) | `app/Console/Commands/SendEmails.php` (Isolatable, PromtsForMissingInput) |
| **46** | Helpers — Array, Auth, Basic, resolve | `app/Helpers/Array.php`, `app/Helpers/Auth.php`, `app/Helpers/Basic.php` |
| **47** | Helpers — Benchmarking, Sleep, TimeBox | `app/Helpers/Benchmarking.php`, `app/Helpers/Sleep.php`, `app/Helpers/TimeBox.php` |
| **48** | Helpers — DateTime, Number, String, URL, Path | `app/Helpers/DateTime.php`, `app/Helpers/Number.php`, `app/Helpers/String.php`, `app/Helpers/URL.php`, `app/Helpers/Path.php` |
| **49** | Helpers — Pipeline, DeferredFunction, Lottery, Specials | `app/Helpers/Pipeline.php`, `app/Helpers/DeferredFunction.php`, `app/Helpers/Lottery.php`, `app/Helpers/Specials.php` (Tappable, Literal) |
| **50** | Helpers — Debugging | `app/Helpers/Debugging.php` |
| **51** | Database transactions | `app/Http/Controllers/TransactionController.php` |
| **52** | Concurrency and context | `app/Http/Controllers/ConcurrencyContextController.php` |
| **53** | Custom exceptions | `app/Exceptions/InvalidOrderException.php` |
| **54** | Localization | `app/Http/Controllers/LocalizationController.php` |
| **55** | Packages and third-party integration | `app/Http/Controllers/PackageController.php` |
| **56** | Testing (HTTP, feature, unit) | `app/Http/Controllers/TestingController.php`, `tests/TestCase.php`, `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`, `phpunit.xml` |
| **57** | AI — agents, tools, middleware | `app/Http/Controllers/AiController.php`, `app/Ai/Agents/SalesCoach.php`, `app/Ai/Tools/RandomNumberGenerator.php`, `app/Ai/Middleware/LogPrompts.php` |
| **58** | User and CRUD controller example | `app/Http/Controllers/UserController.php` |
| **59** | MainController — collections, lazy collections, pipes | `app/Http/Controllers/MainController.php` (Collection, LazyCollection, Pipe, morphWith, loadMorph) |
| **60** | Docker and database testing scripts | `docker/8.0/`, `docker/8.1/`, `docker/8.2/`, `docker/8.3/`, `docker/8.4/`, `docker/8.5/` (Dockerfile, php.ini, supervisord.conf, start-container), `docker/mysql/create-testing-database.sh`, `docker/mariadb/create-testing-database.sh`, `docker/pgsql/create-testing-database.sql`, `compose.yaml` |

---

## Deep dives by topic (after you finish the path)

- **Event broadcasting & listeners**: `app/Http/Controllers/BroadcastController.php`, `app/Http/Controllers/BroadcastingController.php`, then `routes/channels.php`, `app/Broadcasting/OrderChannel.php`, `app/Listeners/` (SendPodcastNotification, SendShipmentNotification, OrderEventSubscriber).
- **HTTP helpers & caching**: `app/Http/Controllers/HttpClientController.php`, then `app/Http/Controllers/CacheController.php`, and `app/Policies/`, `app/Helpers/` for supporting logic.
- **Config and environment**: Revisit `app/config.txt`, then edit `config/*.php`; use `php artisan config:cache` / `config:clear` to see the lifecycle.
- **Source control**: Use `app/3. other-files.txt` for `.gitignore`, `.gitattributes`, and why build artifacts stay out of version control.
- **Frontend tooling**: `resources/js`, `resources/css`, `vite.config.js` after step 18 to see how Vite and assets are integrated.
- **Testing & automation**: `tests/Feature`, `tests/Unit`, `phpunit.xml` for how Laravel structures tests and environments.

---

## How to contribute

1. Fork the repo, update documentation or code, and submit a PR.
2. Keep explanations task-focused and reference the exact file paths (like the ones in the table).
3. Improve or add diagrams, tables, or extra README sections as needed.

---

## Local setup reminders

- `composer install`, `cp .env.example .env`, `php artisan key:generate`.
- Run `npm install && npm run dev` for HMR or `npm run build` for production assets.
- For Sail-based installs, use `./vendor/bin/sail up`, add the `sail` alias, and follow ports in `app/1. Install.txt`.

---

<div align="center">

### Made by Abdul Alim

*Laravel Sandbox Learning Roadmap — learn in order, leave nothing out.*

</div>
