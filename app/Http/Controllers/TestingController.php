<?php

namespace App\Http\Controllers;
use App\Models\User;

use App\Service;
use Mockery;
use Mockery\MockInterface;
use Illuminate\Support\Facades\Cache;

class TestingController {
    // ## 1. Functional Testing: These tests verify that the software acts according to the business requirements and specifications.
    // Unit Testing: The smallest level of testing. Developers test individual components (functions, classes, or methods) in isolation.
    // Integration Testing: Verifies that different modules or services work together correctly. (e.g., Does the "Order" service talk to the "Inventory" database correctly?)
    // System Testing: Testing the entire integrated software system as a whole to ensure it meets all specified requirements.
    // Acceptance Testing (UAT): The final stage before "Go Live." Real users test the software to see if it actually solves the business problem it was built for.
    // Regression Testing: Re-running previous tests after a code change to ensure that new features haven't broken existing functionality.

    ## 2. Non-Functional Testing: These tests check the "attributes" of the system rather than specific features.
    // Performance Testing:
    // Load Testing: Testing the system under a specific expected load (e.g., 1,000 users).
    // Stress Testing: Pushing the system to its breaking point to see how it fails and recovers.
    // Security Testing: Identifying vulnerabilities, threats, and risks in the software to prevent attacks (e.g., Penetration testing).
    // Usability Testing: Evaluating how easy and intuitive the interface is for a human to use.
    // Compatibility Testing: Ensuring the software works across different browsers (Chrome vs. Safari), OS (iOS vs. Android), and devices.
    // Reliability Testing: Checking if the software can perform a failure-free operation for a specified period of time.
    
    ## 3. Testing by "Accessibility" (The Box Methods)
    // Black Box: Testing without knowing the internal code (Focus: Input/Output).
    // White Box: Testing with full knowledge of the internal logic (Focus: Code paths/Branches).
    // Grey Box: A hybrid approach where the tester has partial knowledge of the internal structures (often used in integration testing).

    ## 4. Specialized Testing Types
    // Smoke Testing: A quick "sanity check" to see if the most crucial functions work (e.g., Does the app even boot up?). If it fails, you stop further testing immediately.
    // End-to-End (E2E) Testing: Testing the entire user journey from start to finish (e.g., A user landing on the home page, adding an item to the cart, and checking out).
    // A/B Testing: Comparing two versions of a webpage or app against each other to determine which one performs better for a specific goal.

    // Laravel is built with testing in mind. In fact, support for testing with Pest and PHPUnit is included out of the box and a phpunit.xml file is already set up for your application.
    // vendor/bin/pest, vendor/bin/phpunit, or php artisan test, php artisan test --testsuite=Feature --stop-on-failure, commands to run your tests.
    // Running tests in parallel: composer require brianium/paratest --dev, php artisan test --parallel, --process=4, --recreate-database.
    // When running tests, Laravel will automatically set the configuration environment to testing because of the environment variables defined in the phpunit.xml file. 
    // You may create a .env.testing file in the root of your project. 

    // php artisan make:test UserTest (tests/Feature)
    // php artisan make:test UserTest --unit
    // Parallel Testing Hooks in boot() of AppServiceProvider.
    // How much application code is used when running test: php artisan test --coverage. Minimum Threshold: --coverage --min=80.3
    // List slowest tests: php artisan test --profile
    // To cache the configurations for boosting performance and using same config again, in Test Class:  use WithCachedConfig

    public function httpTest(){
        // See TestExample in feature tests directory.
    }

    public function browserTest(){
        // Using Dusk.
    }

    public function test_console_command(){
        // All terminal commands typically exit with a status code of 0 when they are successful and a non-zero exit code when they are not successful.
        $this->artisan('inspire')->assertExitCode(0);
        $this->artisan('inspire')->assertNotExitCode(1);
        // assertSuccessful(), assertFailed()

        // Mocking user input:
         $this->artisan('question')
              ->expectsQuestion('What is your name?', 'Taylor Otwell')
              ->expectsQuestion('Which language do you prefer?', 'PHP')
              ->expectsOutput('Your name is Taylor Otwell and you prefer PHP.')
              ->doesntExpectOutput('Your name is Taylor Otwell and you prefer Ruby.')
              ->assertExitCode(0);
        // doesntExpectOutput(), expectsOutputToContain('Taylor')
        // expectsConfirmation('Do you really wish to run this command?', 'no')
        // If we use Laravel promts, can use expectsSearch()
        $this->artisan('users:all')->expectsTable(['ID',], [1, 2] );

        // use WithConsoleEvents; CommandStarting and CommandFinished event will be dispatched while running test, by default not dispatched.
    }

    public function test_database(){
        // Reset database after every test so that old data can't interfere:
        // use RefreshDatabase;

        // Insert test data using model factories:
        $user = User::factory()->create();

        // Populate your database using seeder duing feature test:
        $this->seed(); // Run DatabaseSeeder class.
        $this->seed(OrderStatusSeeder::class); // Run a specific seeder class.
        $this->seed([]); // Run multiple specific seeders.

        // Automatically seed database before each test: protected $seed = true;
        // protected $seeder = OrderStatusSeeder::class;

        // Assertions:
        $this->assertDatabaseEmpty('users');
        $this->assertDatabaseCount('users', 5); //  Assert that table contains the given number of records.
        $this->assertDatabaseHas('users', ['email' => 'sally@example.com',]); // assertDatabaseMissing()
        $this->assertSoftDeleted($user); // assertNotSoftDeleted()
        $this->assertModelExists($user); // $user->delete(), assertModelMissing()
        $this->expectsDatabaseQueryCount(5); // Total number of queries epec to run during the test.
    }

    public function mocking(){
        // "mock" certain aspects of your application so they are not actually executed during a given test. 
        // For examples, dont dispatch an event when testing a controller.
        // Laravel provides helpful methods for mocking events, jobs, and other facades out of the box.
        $this->instance(Service::class, Mockery::mock(Service::class, function (MockInterface $mock){
            $mock->expects('process');
        })); // We created mock object for the process method of the service class. or,
        $this->mock(Service::class, function (MockInterface $mock) {
            $mock->expects('process');
        });
        // If need few methods of an object: $this->partialMock()
        
        $this->spy(Service::class); 
        // Spies are similar to mocks; however, spies record any interaction between the spy and the code being tested, allowing you to make assertions after the code is executed.

        // Mocking a Facade:
        Cache::expects('get')->with('key')->andReturn('value'); // or,
        Cache::spy();
        // should not mock Request facade.

        // Interacting with Time: modify the time returned by helpers such as now or Carbon::now().
        $this->travel(5)->milliseconds(); // Travel into the future, now will be the 5 milliseconds later.
        $this->travel(5)->seconds();
        // minutes(), hours(), days(), weeks(), years()
        $this->travel(-5)->hours(); // Travel into the past.
        $this->travelTo(now()->minus(hours: 6));
        $this->travelBack(); // Return back to the travel time.

        $this->travel(5)->days(function () {
            // Test something five days into the future...
        });

        $this->freezeTime(function (Carbon $time) {
            // Freeze the current time
        }); 
        // freezeSecond(): freeze the current time but at the start of the current second.

    }

    public function other_tests(){
         // Event Testing
         // Mail Testing: https://laravel.com/docs/12.x/mail#testing-mailables
         // Queue testing
         // Filesystem Testing: https://laravel.com/docs/12.x/filesystem#testing
         // Http Client Testing: https://laravel.com/docs/12.x/http-client#testing
         // Notification Testing: https://laravel.com/docs/12.x/notifications#testing
         // AI Testing: https://laravel.com/docs/12.x/ai-sdk#testing
    }
}