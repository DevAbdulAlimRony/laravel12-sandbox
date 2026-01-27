<?php
namespace App\Models;

 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\Concerns\HasUuids;
 use Illuminate\Database\Eloquent\Prunable;
 use Illuminate\Database\Eloquent\SoftDeletes;
 use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Casts\AsStringable;

// ORM: Object-relational mapper (ORM) that makes it enjoyable to interact with database.
// Each database table has a corresponding "Model" that is used to interact with that table.
// Eloquent models allow us to insert, update, and delete records from the table as well.
// php artisan make:model Flight, php artisan make:model Flight --migration.
// More options: --factory or -f, --seed or -s, --controller or -c, --controller --resource --requests or -crR, --policy, -mfsc, --pivot or -p
// --all or -a: a model, migration, factory, seeder, policy, controller, and form requests

class Flight extends Model{
    // If we have different database connection rather than default:
    protected $connection = 'pgsql';
    
    // The snake case, plural name of the class will be used as the table name. But we can specify:
    protected $table = 'flights';

    // Model consumes primary key is id and incremented integer, but we can change it:
    protected $primaryKey = 'flight_id';
    protected $incrementing = false;
    protected $keyType = 'string'; // If primary key isnot integer.
    // Composite primary keys are not supported by eloquent model.    

    //* Mass Assignment:
    protected $guarded = []; // all are mass assignable
    protected $fillable = ['name']; // Only name is mass assignable
    protected $fillable = ['options->enabled'];  // Json column options's enabled key is mass assignable.

    //* Soft Deletes:
    use SoftDeletes;
    // will automatically cast the deleted_at attribute to a DateTime / Carbon instance.

    // If we want uuid rather than auto incrementing id, just use a trait:
    use HasUuids; // $article->id will output: "8f8e8478-9035-4d23-b9a7-62f4d2612ce5"
    // We can override the uuid generation process and columns idicated from id to another using these method:
    public function newUniqueId(): string{
        return (string) Uuid::uuid4();
    }
    public function uniqueIds(): array{
        return ['id', 'discount_code']; // id and discount code will receive uuid.
    }

    // To use Ulids:
    use HasUlids;

    // By default, Eloquent expects created_at and updated_at columns to exist on model's corresponding database table.
    // But we can stop that:
    protected $timestamps = false;
    protected $dateFormat = 'U'; // Customize the dateformat to store in database and serialize as array or json.
    public const CREATED_AT = 'creation_date'; // Customize the column name for timestamps
    public const UPDATED_AT = 'modifying_date';
    
    // Newly instantiated model wont have any attribute, but we can set some:
    protected $attributes = ['status' = 1,];

    //* Model Aceess and Modify: See BuilderCollectionController.

    //* Serialization:
    protected $hidden = ['password']; // will not be included in serialized property.
    // Can add relationship methods into hidden also.
    protected $visible = ['first_name', 'last_name']; // should present in array and json representation.
    // But we can override it in controller if need:
    // $user->makeVisible('password')->toArray();
    // $user->mergeVisible(['name', 'email'])->toArray();
    // makeHidden(), mergeHidden(), setVisible(), setHidden()

    // If we want to append a value as attribute which is not a column:
    protected function isAdmin(): Attribute{
        return new Attribute(
            get: fn () => 'yes'
        );
    } // or, getIsAdminAttribute() and return the value, automatically we will get is_admin 
    // Now, it will be appended when we will call it
    // But if want to append always:
    protected $appends = ['is_admin'];
    // Attributes in the appends array will also respect the visible and hidden settings configured on the model.
    // Can append at runtime on demand: $user->append('is_admin')->toArray();
    // mergeAppends(['is_admin', 'status']), setAppends(['is_admin'])

    // We can change default date format using seralization:
    protected function serializeDate(DateTimeInterface $date): string{
        return $date->format('Y-m-d');
    }

    //* Accessor:
    // An accessor transforms an Eloquent attribute value when it is accessed.
    // Method name should correspond to the "camel case" representation of the true underlying model attribute / database column when applicable.
    //* Mutator:
    // Let's say we are doing in controller: $user->name = 'Ronny', so we are setting name attribute here.
    // Mutator will be automatically called when we try to set an attribute.
    protected function firstname(): Attribute{
        // Single attribute:
        return Attribute::make(
            get: fn(string $v) => Str::title($v), // Accessor
            set: fn (string $value) => strtolower($value), // Mutator
            // Can only have set or get.
        );

        // Perform operation on multiple attribute:
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => 
            new Address($attributes['address_line_one'], $attributes['address_line_two']);
            // Same goes for set
        );
        
        // Caching for primitive like integer or string:
        Attribute::make()->shouldCache(); // ->withoutObjectCaching();
    }

    //* Attribute Casting:
    // Similar as accessor and mutator without defining additional methods.
    // Temporary cast at runtime: $user->mergeCasts(['is_admin' => 'integer'])
    // We should never cast when attribute and any relationship name or primary key are the same.
    // serialize specific date columns:
    protected function casts(): array{
        return [
            // We can define in model: protected $dateFormat = 'U';
            'birthday' => 'date:Y-m-d',
            'joined_at' => 'datetime:Y-m-d H:00',
            'directory' => AsStringable::class, // Stringable casting
            'options' => 'array', // Let's say, options column is a json type in datasbse, we casted it array.
            'options' => 'json:unicode', // JSON with unescaped Unicode characters.
            // array cast is problematic, cat access like this: $user->options['key'] = $value;. Solution:
            'options' => AsArrayObject::class,
            'options' => AsCollection::class,
            'options' => AsCollection::using(OptionCollection::class), // Collection class name
            'options' => AsCollection::of(Option::class) // Mapped into option class.
            // See App\ValueObject\Options.php
            'uuid' => AsBinary::uuid(), // Binary casting. when set it will cast as binary, when return will get plain string.
            'ulid' => AsBinary::ulid(),
            'status' => ServerStatus::class, // Enum Casting
            'statuses' => AsEnumCollection::of(ServerStatus::class), // If we need enum valus to store in a single column

        ];
    }
    // We cast at query time on demand using User::all()->withCasts('last_posted_at' => 'datetime')
    // We can make custom Cast: php artisan make:cast AsJson (see app\Casts directoryy)
    // Now can use: 'options' => AsJson::class
    // Can define custom casts that cast values to objects. See AsAddress.php
    // Inbound Casting: Cast only when value set, not when value accessed or get or retrieved.
    // php artisan make:cast AsHash --inbound
    // Cash Parameters: 'secret' => AsHash::class.':sha256',
    // Can compare casted values using ComparesCastableAttributes interface.
    // For value objects, we can make it castable using Castable inteface
    // class Address implements Castable, also can use annonymous cast.

    //* Model Pruning:
    // Periodically delete models that are no longer needed.
    use Prunable;
    public function prunable(): Builder{
        return static::where('created_at', '<=', now()->minus(months: 1)); // Delete if this condition meets when artisan command run.
        // $this refers to one specific record (e.g., User ID #5)
        // static refers to the entire Table/Model (e.g., the User class).
        // Rather than Flight::, using static:: ensures late static binding- uery runs on the child class that is actually being pruned, not the parent.
        // If we change the model name, we dont have to change here for the static keyword's benefit.
    }
    public function pruning(): void{
        // Prepare the model for pruning
        // Run before deleting the model if necessary.
    }
    // Finally, schedule model:prune in routes/console.php.
    // Soft deleting models will be permanently deleted (forceDelete) if they match the prunable query.
    // If we want to delete by miss assignable: use MassPrunable;

    //* Global Scope: Add constraints to all queries for a given model.
    // Soft delete use global scopes behind the scene o only retrieve "non-deleted" models from the database. 
    // php artisan make:scope AncientScope, it will make a file in app\Models\Scopes.
    // In that class, implements Scope interface and code for apply() method.
    // Finally, call in any model above the class: #[ScopedBy([AncientScope::class]), or,
    // Manually register the global scope by overriding the model's booted method and invoke the model's addGlobalScope method.
    public static function booted(): void{
        static::addGlobalScope(new AncientScope);

        //* Anonymous Global Scopes
        // If scope is simple, dont want separate class, implement in a closure:
        static::addGlobalScope('ancient', function(Builder $builder){});
    }
    //* Remove global Scope:
    Flight::withoutGlobalScope(AncientScope::class)->get(); // for class.
    User::withoutGlobalScope('ancient')->get(); // for annonymous.
    User::withoutGlobalScopes()->get(); // Remove all
    User::withoutGlobalScopes([FirstScope::class, SecondScope::class])->get();
    User::withoutGlobalScopesExcept([SecondScope::class])->get();

    //* Local Scopes:
    // Use #[Scope] above the method and implement the method popular(). Or,
    // or just implement scopePopular()
    #[Scope]
    protected function popular(Builder $builder){
        $query->where('votes', '>', 100);

        // Pending Attribute:
        $query->withAttributes(['active' => 1]); 
        // when we call popular()->create(), automatically active column will be 1.
        // If we pass this 2nd arg, asConditions: false, attribute wont be added where condition.

    } // Access: User::popular(), User::popular()->orWhere(function().....)
    // Let's say, we have anothe scope scopeActive(), rather than using callback in orWhere:
    User::popular()->orWhere->active()->get();
    //* Dynamic Scope: If we pass any parameter in pouular($builder, $parameters)

    //* Events:
    // Eloquent model dispatch events: 
    // retrieved, creating, created, updated, updating, saving, saved, deleting,
    // deleted, trashed, forceDeleting, forceDeleted, restoring, restored, replicating.
    // If we want to listen any event:
    protected $dispatchesEvents = ['saved' => UserSaved::class, ]; // Now we can use event lsitener for UserSaved class
    //* or, Use closure rather than event class:
    // In booted(), we can: static::craeted(function (User $user));
    // static::created(queueable(function (User $user):  execute the model event listener in the background using application's queue.
    //* or, use observer class if listening to many events for a model:
    // php artisan make:observer UserObserver --model=User, check app/Observers directory.
    // Register above the Model class Name: #[ObservedBy([UserObserver::class])]
    // or manually register in boot() method of AppServiceProvider: User::observe(UserObserver::class)
    // If we want to execute a observer event only after transaction: class UserObserver implements ShouldHandleEventsAfterCommit
    //* Muting Events: User::withoutEvents(function () {}
    // Mute event for a single model: $user->saveQuietly(), deleteQuietly(), forceDeleteQuietly(), restoreQuietly().
 }