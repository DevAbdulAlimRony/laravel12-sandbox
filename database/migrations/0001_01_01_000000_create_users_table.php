<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//* See database.php config file at first.
//* Inspecting database:
// php artisan db:show
// php artisan db:show --database=pgsql
// php artisan db:show --counts --views
// Table Overview: php artisan db:table users
// $tables = Schema::getTables();
// $views = Schema::getViews();
// $columns = Schema::getColumns('users');
// $indexes = Schema::getIndexes('users');
// $foreignKeys = Schema::getForeignKeys('users');
// If a connection is not default connection:  Schema::connection('sqlite')->getColumns('users');

//* Monitoring Database:
// php artisan db:monitor --databases=mysql,pgsql --max=100
// should listen for this event within application's AppServiceProvider in order to send a notification to the development team.
//  Event::listen(function (DatabaseBusy $event) {   Notification::route('mail', 'dev@example.com')...

//* Migration:
// Migrations are like version control for our database.
// Allowing your team to define and share the application's database schema definition.
// For creating and manipulating tables across all of Laravel's supported database systems. 
// php artisan make:migration create_flights_table
// If want custom path: php artisan make:migration create_flights_table --path=
// We can make a single schema from all migrations i database/schema directory.: php artisan schema:dump

//* Dump Migration to sql Schema:
// php artisan schema:dump --prune : Schema created and migrations deleted.
// For different database connection test: php artisan schema:dump --database=testing --prune

// If migration use different database connection rather than default connection:
protected $connection = 'pgsql';

// A migration file is just a PHP's annonymous class.
// A migration can have one table or multiple tables.
return new class extends Migration
{
    //* Conditional Migration:
    // Determine if this migration should run. If retrun false, then migration will be skipped.
    public function shouldReturn(){
        return User::active(Flight::class);
    }

    //* Checking Existence:
    Schema::hasTable('users');
    Schema::hasColumn('users', 'email'); // The "users" table exists and has an "email" column
    Schema::hasIndex('users', ['email'], 'unique'); // The "users" table exists and has a unique index on the "email" column.
    // If not default database:
    Schema::connection('sqlite')->create('users', function(Blueprint $table){});

    //* Rename Table:
    Schema::rename('old_table', 'new_table'); // Before renaming, see foreign key constraints at first.

    //* Dropping Table:
    Schema::drop('users');
    Schema::dropIfExists('users2');

    Schema::enableForeignKeyConstraints();
    Schema::disableForeignKeyConstraints();
    Schema::withoutForeignKeyConstraints(function () {});
    // SQLite disables foreign key constraints by default.
    // When using SQLite, make sure to enable foreign key support in your database configuration before attempting to create them in your migrations.

    public function up(): void
    {
        //* Creating Tables:
        // To create a table use create method of Schema facade.
        // Two arguments: table name, closure which recives a Blueprint object.
        Schema::create('users', function (Blueprint $table) {
            // We can use any Schema Builder's column method to build the table.
            // The schema builder blueprint offers a variety of methods that correspond to the different types of columns .
            $table->id(); // id is a method of Blueprint.php's instance to make auto incremented unsignedBigInteger column as primary key automatically.
            // $table->increments('id');

            //* UUID:
            // A UUID (Universally Unique Identifier) is a 128-bit label used to identify information in computer systems.
            // Unlike a standard auto-incrementing integer (1, 2, 3...), a UUID looks like a long string of hexadecimal characters, such as 550e8400-e29b-41d4-a716-446655440000.
            // The main draw of a UUID is that it is statistically unique. You can generate one on any device without checking a central database, and the odds of generating the same one twice are virtually zero.
            // Use standard IDs for internal tables (like settings, categories, or roles) to keep performance high. Use UUIDs for user-facing resources (like orders, profiles, or shared_links).
            // Exmp: https://www.youtube.com/watch?v=3wVTmlD86a . So, public link is not exposed by id anymore.
            // Other use cases: same table in two databases to cluster.
            // Drawbacks: Big size in database, Insert operation is costly.
            $table->uuid('id')->primary();

            //* ULID:
            // A ULID is also a 128-bit identifier, but it is "Lexicographically Sortable."
            // This means that unlike UUID (which is random), ULIDs are generated based on the time they were created.
            // A ULID looks like a string of 26 characters (Base32), such as: 01ARZ3NDEKTSV4RRFFQ69G5FAV.
            // Why use it over UUID? 
            // 1. Sortability: Because they contain a timestamp, they are naturally ordered by creation date. 
            // 2. Database Performance: Unlike random UUIDs which cause "index fragmentation" (making the DB slow over time), 
            //    ULIDs are inserted at the end of the index, keeping the database fast even with millions of rows.
            // 3. Readability: They are shorter (26 chars vs 36 for UUID) and don't have dashes, making them cleaner for URLs.
            // Use Cases: High-traffic transaction tables (orders, messages, logs) where you need unique IDs 
            // but don't want to sacrifice the insert speed of a standard integer.
             $table->ulid('id')->primary(); 
            // In the Model:
            // use Illuminate\Database\Eloquent\Concerns\HasUlids;
            // use HasUlids;

            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            //* Foreign Key Constraints:
            $table->foreign('user_id')->references('id')->on('users'); // or, recommended way:
            $table->foreignId('user_id')->constrained(); // If table and column is not convenient for auto detecting, use:
            $table->foreignId('user_id')->constrained(
                table: 'users', indexName: 'posts_user_id'
            );
            // Can add: onUpdate('cascade'), onDelete('cascade')
            // cascadeOnUpdate(), restrictOnUpdate(), ullOnUpdate(), noActionOnUpdate(),
            // cascadeOnDelete(), restrictOnDelete(), nullOnDelete(), noActionOnDelete()
            // Addition column modifiers must be called before constrained() chain.

            //* More methods:
            // boolean(), char('name', length: 100), integer(), decimal('amount', total: 8, places: 2), double()
            // enum('difficulty', ['easy', 'hard']), enum('difficulty', Difficulty::cases()), $table->set('flavors', ['strawberry', 'vanilla']);
            // foreignId('user_id'), foreignIdFor(User::class), foreignUuid('user_id'): laravel will detect table name or column name automatically.
            // ipAddress() - creates a VARCHAR equivalent column, macAddress()- Postgres have this type. Other database will take as string.
            // json() - json equivalent column.
            // jsonb() (JSON Binary) is a database column type that allows you to store unstructured data (like a JavaScript object) directly in a relational database, but in a compressed, binary format that the database can search and index.
            // text(), longText()
            // vector()

            // date(), dateTime('created_at', precision: 0), time(), timeTz()
            $table->timestamps();
            $table->timestampsTz(precision: 0);
            $table->timestamp('added_at', precision: 0);
            $table->timestampTz('added_at', precision: 0); // with timezone.
            $table->year('birth_year');

            $table->rememberToken(); // Creates a nullable, VARCHAR(100) equivalent column that is intended to store the current "remember me" authentication token

            //* Morph:
            $table->morphs('taggable'); // It will create taggable_id and taggable_type.
            $table->nullableMorphs('taggable');
            $table->uuidMorphs('taggable');
            $table->ulidMorphs('taggable');
            $table->nullableUlidMorphs('taggable');
            $table->nullableUuidMorphs('taggable');

            $table->comment('Business calculations'); // Adding comment to the table
            // $table->temporary(); - Only visible to the current connection's database session, droppped when connection closed.
            // Useful when generating high performance report, dashboard data temporary save etc.

            //* Spatial Types Column
            // For postgres, must install the PostGIS extension before the geography method may be used.
            $table->geometry('positions', subtype: 'point', srid: 0); // Treats the world as a flat, 2D surface (Cartesian plane). It uses simple math (Pythagoras) to calculate distance.

            $table->geography('coordinates', subtype: 'point', srid: 4326);
            // High-precision GPS locations (Latitude and Longitude) in a way that the database understands they represent a round earth, not a flat map.
            // SRID stands for Spatial Reference Identifier.
            // 4326 is the industry-standard ID for WGS 84. This is the exact coordinate system used by GPS, Google Maps, and almost every smartphone on Earth.
            // By specifying point, you tell Laravel and the database that this column will only store single specific locations (a single Lat/Long pair).
            // Imagine you are building a delivery app. You need to find all the Drivers within 5 kilometers of a Restaurant.
            // If you store coordinates as simple floats (lat/long columns), your database query has to do a massive, slow math calculation for every single driver in your database to see if they are close.
            // As you specifies column as geography, we can use ST_Distance and ST_DWithin
            // We can use Laravel Spatial package to make everything easier when working with location coordinates (But supports only MySQL)
            // For real time location tracking, WebSocket helps. Example:
            // The driver's Uber app constantly listens to the phone's GPS. Every few seconds (or every few meters moved), the app sends a small packet of data to the server.
            // The Payload: { driver_id: 123, lat: 40.712, lng: -74.006, heading: 90 }
            // Google Map API or Leaflet package do animation and it seems like real time moving.
            // Stack we can use: Laravel Reverb or Pusher, Laravel Echo, Google Map JS SDK or Mapbox GL JS, Vuejs for state manage.

            //* For MariaDB or MySQL: 
            $table->engine('InnoDB'); // specify table's storage engine
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');
            $table->longText('data')->charset('binary'); // Exmp: Encrypted string or small image
            // A and a might be treated as the same thing; in a binary column, they are strictly different.
            $table->after('password', function (Blueprint $table) {//...columns});

            //* Performance- Data Type optimization:
            // Choosing the smallest possible type that fits your data provides significant performance gains when your application scales to millions of rows.
            // When you search for a record (e.g., WHERE age = 25), the database compares the values. Comparing a 1-byte integer is faster for the CPU than comparing an 8-byte integer.
            // A table with 100 million rows of bigInteger will cost significantly more in storage and backup costs than one optimized with mediumInteger or integer.
            // mediumIncrements(), mediumInteger(), mediumText() - A standard integer goes up to about 2.1 billion. A mediumInteger is smaller.
            // smallIncrements(), smallInteger(): max values 65,535
            // tinyIncrements(), tinyText(), tinyInteger() :-128 to 127, unsignedTinyInteger: 0 to 255
            // If your column is set to unsignedTinyInteger (which ranges from 0 to 255) and you try to save the number 256:
            // Strict Mode (Default in Laravel): The database will throw a "Numerical value out of range" error and the data will not be saved.
            // Non-Strict Mode (Old/Rare): The database might "truncate" the value and just save 255, which leads to incorrect data.
            // Perfct Use Case: $table->unsignedTinyInteger('rating'); // Perfect! 10 is way below 255.
            // Only positive: unsignedBigInteger(), unsignedInteger(), unsignedMediumInteger(), unsignedSmallInteger(), unsignedTinyInteger().

            //* Soft Deleting:
            $table->softDeletes('deleted_at', precision: 0);
            $table->softDeletesTz('deleted_at', precision: 0); // Nullable deleted at (with timezone) with optional second precision.
            // Laravel will save the date time and user's current timezone also so that we can show the local time in user panel- It is really useful.
            // 2024-12-31 23:00:00-05 (The -05 shows it was New York time).
            // When Laravel reads this, it can perfectly convert it to the viewer's local time, ensuring everyone sees the exact same moment in history.
            // We should alwas use 'timezone' => 'UTC' in config file, if server changed it can be a mess.
            // Then we can convert it in any time zone based on user's location when created or updated or deleted.

            //* Creating Indexes:
            $table->string('email')->unique(); // or,
            $table->unique('email');
            $table->index(['account_id', 'created_at']);
            // When creating an index, Laravel will automatically generate an index name based on the table, column names, and the index type
            // But we can specify:
            $table->unique('email', 'unique_email');

            //* Available Indexes (second argument for index name)
            // primary('id'), primary(['id', 'parent_id'])- composite keys, unique('email')
            // index('state'), fullText('body'), fullText('body')->language('english'), >spatialIndex('location')
            
            // Creating an index on a large table can lock the table and block reads or writes when index built.
            // chain online to solve it:
            $table->string('email')->unique()->online();

            //* Column Modifiers:
            // ->nullable(), ->default($value), ->autoIncrement(), ->unsigned(),
            // ->nullable($value = true): Allow NULL values to be inserted into the column.
            // ->charset('utf8mb4'), ->collation('utf8mb4_unicode_ci'), ->comment('my comment'),
            // ->after('column')[only for mariadb or mysql], ->first() [place as the first column only in mysql or mariadb], ->from($integer) [starting value- mysql or mariadb]
            // ->instant(): You run the migration. The database simply makes a note in its header that "Posts now have a sponsored column." The migration finishes in 0.1 seconds. Your users never notice a thing.
            // ->invisible() [make invisible for select * queries]
            // ->lock($mode)[mysql]: For handling race condition. Like multiple customer ordering.
            // ->useCurrent(): Set TIMESTAMP columns to use CURRENT_TIMESTAMP as default value, ->useCurrentOnUpdate()
            // ->default(new Expression('(JSON_ARRAY())'))
            
            //* GENERATED & IDENTITY COLUMNS
            // ->storedAs($expression):  (MariaDB / MySQL / PostgreSQL / SQLite)
            // Create a "Stored Generated Column." The database calculates the value once and physically 
            // saves it to the disk. Use this when you need to INDEX the result for fast searching.
            // Real Case: A 'search_vector' column that combines 'title' and 'body' for full-text search, 
            // or a 'slug' generated from a 'title' that must be indexed.
            $table->string('full_name')->storedAs("first_name || ' ' || last_name");

            // ->virtualAs($expression): (MariaDB / MySQL / SQLite).
            // Create a "Virtual Generated Column." The value is calculated in RAM every time you read the row. 
            // It uses NO disk space but costs a tiny bit of CPU. Use for values you don't need to index.
            // Real Case: Calculating 'total_price' from 'price' and 'tax' for display only, 
            // or extracting the 'year' from a 'date' column for a simple filter.
            $table->decimal('total_price')->virtualAs("unit_price * quantity");

            // ->generatedAs($expression): 
            // (PostgreSQL Specific) Creates an "Identity Column." It is the modern SQL standard replacement 
            // for 'SERIAL'. It uses a sequence generator to create unique IDs.
            // Real Case: When building high-compliance enterprise apps where you need strict 
            // control over how IDs are incremented or if they can be restarted.
            $table->integer('invoice_number')->generatedAs();

            // ->always(): 
            // (PostgreSQL Specific) Used with generatedAs(). It tells the database to REJECT any 
            // manual input for this column. The database "always" wins.
            // Real Case: A 'security_audit_id' that a user or hacker must NEVER be able to manually 
            // overwrite or skip. It guarantees the sequence integrity.
            $table->integer('audit_id')->generatedAs()->always();
        });

        //* Updating Tables:
        // Use table method rather than create method.
        Schema::table('users', function(Blueprint $table){
            $table->integer('votes');
            $table->string('name', 50)->change(); // Modify the type and attributes of existing columns.
            $table->integer('votes')->unsigned()->default(1)->comment('my comment')->change(); // Add extra attributes and apply change method.
            // change method does not change the index of the column, but if we want:
            $table->bigIncrements('id')->primary()->change();
            $table->renameColumn('from', 'to'); 
            $table->dropColumn('votes');
            $table->dropColumn(['votes', 'avatar', 'location']);
            $table->renameIndex('from', 'to')
            // dropMorphs('morphable'), dropRememberToken(), dropSoftDeletes()
            // dropSoftDeletesTz(), dropTimestamps(), dropTimestampsTz()
            // dropPrimary(), dropUnique(), dropIndex(), dopFullText(), dropSpatialIndex()
            // dropIndex(['state']); // Drops index 'geo_state_index'
            $table->dropForeign('posts_user_id_foreign');
            $table->dropForeign(['user_id']);
        });        

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }

    //* Migrate:
    // Run all migrations: php artisan migrate
    // Which migration already run and which pending: php artisan migrate:status
    // Just test what SQL statements will be executed: php artisan migrate --pretend
    // In deployment if we have two servers: php artisan migrate --isolated
    // Sometimes to give security for not losing data, laravel can avoid to migrate, but we can force: php artisan migrate --force
    
    //* Rollback:
    // When we run a migration, all migration files will get the same bacth like batch 1 in migrations table to track what to rollback
    // When we rollback normally, it always rollback latest batch. We can change the batch in database table to trick it.
    // Rollback the last migration: php artisan migrate:rollback
    // php artisan migrate:rollback --step=5
    // php artisan migrate:rollback --batch=3
    // See SQL Statement without running the migration: php artisan migrate:rollback --pretend
    // Roll back all migrations: php artisan migrate:reset

    //* Rollback and migrate together:
    // Rollback all migrations and migrate all again: php artisan migrate:refresh
    // Refresh and run all seeds: php artisan migrate:refresh --seed
    // Roll back and re-migrate a limited number of migrations: php artisan migrate:refresh --step=5
    // Drop all tables then execute migrate: php artisan migrate:fresh
    // php artisan migrate:fresh --seed
    // If different databse connection: php artisan migrate:fresh --database=admin
    // Refresh execute down() methods, fresh execute drop command- it drops all table made by migration or not.

    //* Events:
    // Each migration operation will dispatch an event:
    // Illuminate\Database\Events\MigrationsStarted, MigrationsEnded, NoPendingMigrations, SchemaDumped, SchemaLoaded.
};
