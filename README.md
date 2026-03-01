# Laravel Sandbox Learning Roadmap

This is an **open-source Laravel documentation** repository—anyone can learn from it, improve it, and contribute back.

## Learning path (sequential order)
| Step | What to learn | Files / Classes to study |
| --- | --- | --- |
| 1 | Installation options and editor tooling | `app/1. Install.txt` |
| 2 | JavaScript / PHP package manifests and scripts | `app/2. json.txt` |
| 3 | Support and public files that power requests | `app/3. other-files.txt` plus `public/.htaccess`, `public/robots.txt` (see the same file for coverage) |
| 4 | Configuration patterns, helpers, and environment handling | `app/config.txt` |
| 5 | Request lifecycle, directory definitions, and service providers | `app/4. Lifecycle and Directory.txt`, `bootstrap/app.php`, `routes/web.php` |
| 6 | Artisan, Tinker, and console-first flows | `app/5. artisan and tinker.txt`, `artisan` script |
| 7 | Controllers and utilities for HTTP, caching, broadcasting, etc. | `app/Http/Controllers/HttpClientController.php`, `app/Http/Controllers/CacheController.php`, `app/Http/Controllers/BroadcastController.php` (read them in that order to follow data flow) |
| 8 | Frontend assets, views, and resources | `resources/views`, `vite.config.js`, `resources/js`, `resources/sass` |
| 9 | Database, migrations, and seeders | `database/migrations`, `database/seeders` |
| 10 | Tests, factories, and supporting scripts | `tests`, `phpunit.xml` |

Each step lists the concept to master and the file or class (or folder) where the explanation lives; proceed in order to build a solid foundation before diving deeper.

## Deep dives by topic (after you finish the path)
- **Event broadcasting & listeners**: study `app/Http/Controllers/BroadcastController.php`, then inspect `routes/broadcasting.php`, `app/Broadcasting`, and `app/Listeners`.
- **HTTP helpers & caching**: read `app/Http/Controllers/HttpClientController.php`, followed by `app/Http/Controllers/CacheController.php`, and then `app/Policies` / `app/Helpers` for supporting logic.
- **Config and environment utilities**: revisit `app/config.txt`, then edit `config/*.php` to see how the `Config` facade and helpers drive behavior; use `php artisan config:cache` / `config:clear` to see the lifecycle.
- **Source control hygiene**: use `app/3. other-files.txt` as a reference for `.gitignore`, `.gitattributes`, and why build artifacts stay out of version control.
- **Frontend tooling**: leverage `resources/js`, `resources/sass`, and `vite.config.js` after reading step 8 to understand how Vite, Vue/React, and SCSS are pulled into Laravel.
- **Testing & automation**: explore `tests/Feature` and `tests/Unit` along with `phpunit.xml` to see how Laravel structures assertions and environments.

## How to contribute
1. Fork the repo, update documentation or code, and submit a PR.
2. Keep explanations task-focused and reference the exact file paths (like the ones cited above).
3. Improve or add diagrams, tables, or extra README sections as needed.

## Local setup reminders
- `composer install`, `cp .env.example .env`, `php artisan key:generate`.
- Run `npm install && npm run dev` for HMR or `npm run build` for production assets.
- For Sail-based installs, use `./vendor/bin/sail up`, add the `sail` alias, and access services on the ports outlined in `app/1. Install.txt`.

Made by Abdul Alim.
