<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    // php artisan make:factory PostFactory
    // Define trait in Model: use HasFactory
    // The HasFactory trait's factory method will use conventions to determine the proper factory for the model the trait is assigned to.
    // If factory class not in database/factories directly, specify above model class: #[UseFactory(FlightFactory::class)], dont need HasFactory trait then.
    // Or, Just implement newFactory() method in model, See Model. and define model here:
    protected $model = Flight::class;

    // The current password being used by the factory.
    protected static ?string $password;

    // The definition method returns the default set of attribute values that should be applied when creating a model using the factory.
    // Via the fake helper, factories have access to the Faker PHP library, which allows you to conveniently generate various kinds of random data for testing and seeding.
    // Change faker locale: config/app.php -> faker_locale
    public function definition(): array
    {
        return [
            // Faker is a PHP library that generates fake data 
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),

            // Relationship:
            'flight_id' => Flight::factory(),
            'user_type' => function (array $attributes) {
                return User::find($attributes['user_id'])->type;
            }

            // Recycling an Existing Model for Relationships
            // If you have models that share a common relationship with another model
            // Lets say, Ticket belongs to an Airline and a Flight and Flight also belongs to Airline
            Ticket::factory()->recycle(Airline::factory()->create())->create();

            //* Available facker formatters in FackerPHP:
            // title($gender = null|'male'|'female'): Mr, Ms. titleMale(), titleFemale()
            // suffix()- Jr. , name($gender = null|'male'|'female'), firstName(), lastName(), firstNameFemale()
            // cityPrefix(), secondaryAddress(), state(), city(), streetName(), postcode(), country(), latitude(), longitude() etc.
            // phoneNumber(), tollFreePhoneNumber(), company(), jobTitle() etc
            // realText($maxNbChars = 200, $indexSize = 2), realTextBetween($minNbChars = 160, $maxNbChars = 200, $indexSize = 2)
            // randomDigit(), randomDigitNot(2), randomDigitNotNull()- from 1 to 9, randomNumber, randomFloat()
            // numberBetween(), randomLetter(), randomElements(), shuffle()
            // word(), words(), sentence(), sentences(), paragraph(), paragraphs(), text()
            // dateTime(), date(), time(), dateTimeInterval(), dateTimeThisMonth(). amPm(), dayOfMonth(), month(), monthName(), year() etc.
            // email(), safeEmail(), freeEmail(), userName(), password(). companyEmail(), ipv4(), macAddress() etc.
            // creaditCardNumber(), hexColor(), file(), image(), uuid(), boolean(), md5(), randomHtml() etc.
        ];
    }

    //* State Transformation:
    public function suspended(): Factory{
        return $this->state(function (array $attributes){
            return [
                'status' => 'suspended',
                 // Rather than doing every time, for every row, we got status suspended
                 // Just call: User::factory()->suspended()->create();
            ];
        });
    }

    //* Factory Callbacks: Do something after making and creating
    // Can do same in state transformation also.
    public function configure(): static{
        return $this->afterMaking(function(User $user){})->afterCreating(function(User $user){});
    }
    // Example: assign role, or create related model's factory after creating.
    // If you are testing a piece of logic that just calculates a value based on user attributes (like $user->getFullName()), use make(). If you are testing a search filter that queries the database, you must use create().

    // Indicate that the model's email address should be unverified.
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    //* Create in Seeder:
    // Mass assignment protection is automatically disabled when creating models using factories.
    // User::factory()->make();
    // User::factory()->count(3)->make();
    // User::factory()->count(5)->suspended()->make();
    // Created model will already be soft deleted.: User::factory()->trashed()->create();
    // Can override state also: ->state(['status' => 'active'])
    // Override Attributes: User::factory()->make(['name' => 'overridden name']);
    // We can use create() method rather than make it uses save() method of eloquent.

    //* Sequence:
    // Alernate the value of a given model attribute for each created model.
    User::factory()->count(10)->state(new Sequence(['admin' => 'Y'],['admin' => 'N']))->create(); // o,
    User::factory()->count(2) ->sequence( ['name' => 'First User'], ['name' => 'Second User'],)->create();
    // Five users will be created with an admin value of Y and five users will be created with an admin value of N.
    // Can pass closure:
    // new Sequence(fn (Sequence $sequence) => ['role' => UserRoles::all()->random()])
    // Can access $sequence->index within the closure

    //* Relationship:
    User::factory()->has(Post::factory()->count(3))->create();
    // Automatic assumption that, 'posts' is a relationship, Can define explicitly: has(Post::factory()->count(3), 'posts')
    // Can do state manipulation on relations also.
    User::factory()->hasPosts(3)->create(); // Magic factory relationship.
    // Override: ->hasPosts(3, ['published' => false])
    // Can pass closure: hasPosts(3, function(array $attributes, User $user){})

    // Belongs to Relationship:
    Post::factory()->count(3)->for(User::factory()->state([]))->create(); // Or, using magic factory method:
    Post::factory()->count(3)->forUser()->create();
    
    // Many to Many Relationship:
    User::factory()->has(Role::factory()->count(3))->create();
    User::factory()->hasAttached(Role::factory()->count(3),)->create(); // Pivot Table attributes
    // Magic: hasRole()

    // Polymormhic Relationship:
    Post::factory()->hasComments(3)->create();
    Comment::factory()->count(3)->for(Post::factory(), 'commentable')->create();
    // Use hasAttached if many to many polymorphic, magic: ->hasTags(3, ['public' => true])

    // We can define relationship directly on our faker definition.
}
