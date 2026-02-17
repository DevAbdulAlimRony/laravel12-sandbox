<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\DripEmailer;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Artisan;

// Artisan is the command line interface included with Laravel.
// console.php stores closure based commands
// Though they are not any http route, but entry point.
// If need more command list for Tinker, add in tinker config file- tinker.php. Can use class to not alias also.

// View all commands: php artisan list
// See command details: php artisan help migrate
// For Sail: ./vendor/bin/sail artisan list
// Make custom command: php artisan make:command SendEmails. See App/Console/Commands.
// By default, laravel automatically scan console/commands directy, but if another directory then add in bootstrap/app.php
// See callback command in console.php

class SendEmails extends Command implements Isolatable, PromtsForMissingInput
{
    // Name and Signature of the command: php artisan mail:send syful.isl
    protected $signature = 'mail:send {user}';
    // optional argument: {user?}
    // optional argument with default value: {user=abdul.alm}
    // Options: 'mail:send {user} {--queue}'. php artisan mail:send 1 --queue. If queue passed value of the option will be true.
    // Options with Values: 'mail:send {user} {--queue=}'. php artisan mail:send 1 --queue=default.
    // Options with default value: 'mail:send {user} {--queue=default}'
    // Options shortcut: 'mail:send {user} {--Q|queue=}'. php artisan mail:send 1 -Qdefault.
    // Input Arrays: 'mail:send {user*}'. php artisan mail:send 1 2.
    // Optional Input Array: 'mail:send {user?*}'
    // Option Arrays: 'mail:send {--id=*}'. php artisan mail:send --id=1 --id=2
    // Input descriptions: 'mail:send {user : The ID of the user} {--queue : Whether the job should be queued}'

    // Description of the command to show in list.
    protected $description = 'Send a marketing email to a user';

    // Execute the command. We can type-hint any dependency.
    public function handle(DripEmailer $drip): void
    {
        $drip->send(User::find($this->argument('user'))); // DripMailer is a service class. We should use a service class for the operation to make the command class clean.
        
        //* Writting Inputs:
        // argument('user'): Return null if not present.
        // Retrive all arguments: $this->arguments()
        // Retrieve Option:  $this->option('queue'), $this->options()
        // If nothing returned and successful, command will exit with 0 exit code. If we return 1 then 1 exit code.
        $this->ask('What is your name?');
        $this->ask('What is your name?', 'Taylor');
        $this->anticipate('What is your name?', ['Taylor', 'Dayle']); // auto completion hints. Can pass closure.
        $this->secret('What is the password?'); // similar to ask, but wont visible.
        $this->choice('What is your name?', ['Taylor', 'Dayle'],  $defaultIndex, $maxAttempts = null,  $allowMultipleSelections = false); // Multiple choice question.
        if($this->confirm('Do you wish to continue?')) {}
        if($this->confirm('Do you wish to continue?', true)){}
        
        //* Writting Outputs:
        $this->error('Something went wrong.');
        $this->fail('Something went wrong.');
        // line(), newLine(), newLine(3), comment(), question(), warn(), alert()
        $this->table(['Name', 'Email'], User::all(['name', 'email'])->toArray()); 
        $this->withProgressBar(User::all(), function (User $user) { $this->performTask($user); }); // Show progressbar for long running task.
        // We can do more using $this->output->createProgressBar. when to start when to finish etc.

        //* Calling another command within a command:
        $this->call('mail:send', [ 'user' => 1, '--queue' => 'default' ]);
        $this->callSilently('mail:send', []); // call and supress all of its outputs.

        //* Signal Handling:
        // Operating systems allow signals to be sent to running processes. 
        // Listen for signals in your Artisan console commands and execute code when they occur.
        // For example, the SIGTERM signal is how operating systems ask a program to terminate.
        $this->trap(SIGTERM, fn () => $this->shouldKeepRunning = false);
    }

    //* Isolatable Command:
    // Ensure that only one instance of a command can run at a time.
    // If we use isolatable commands,  Laravel accomplishes this by attempting to acquire an atomic lock using your application's default cache driver.
    // If already same command running, another instance of SendEmails wont run, will exit successfully with 0 code.
    // To utlize this feature, should use memcached, redis, dynamodb, database, file or array cache driver.
    // implements an interface: Illuminate\Contracts\Console\Isolatable
    // php artisan mail:send syful.isl --isolated (isolated option automatically invoked)
    // Specify exit code: php artisan mail:send 1 --isolated=12
    // For atomic lock, by default, laravel use id. We can override that:
    public function isolatableId(): string{
        return $this->argument('user');
    }
    // If the command is interrupted and unable to finish, the lock will expire after one hour.
    public function isolationLockExpiresAt(): DateTimeInterface|DateInterval
    {
        return now()->plus(minutes: 5);
    }

    //* Prompting for Missing Input:
    // If command cotains required argument let's say userId, if not provided, user will get an error message.
    // But if missing, we can ask for the input
    // Implements the PromptsForMissingInput interface.
    // Laravel will automatically ask for the userId  by intelligently phrasing the question using either the argument name or description, but we can customize the question:
    protected function promptForMissingArgumentsUsing(): array {
        return [
            'user' => 'Which user ID should receive the mail?',
            // Placeholder Text: 'user' => ['Which user ID should receive the mail?', 'E.g. 123'],
            // Can use closure: 'user' => fn() => search(label: , placeholder: , options: );
            // If we want user to select or enter options, we can include promts in handle method.
        ];
    }

    //* Promting for missing argument:
    // Need interface: afterPromptingForMissingArguments
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void{
        $input->setOption('queue', confirm(label: 'Would you like to queue the mail?', default: $this->option('queue') ));
    }

    //* Programmatically Exuting Commands:
    // Sometimes we need to run command outside of the CLI, maybe in route callback or controller.
    // We can use Artisan facade for that:
    Artisan::call('mail:send', [ 'user' => $user, '--queue' => 'default', '--id' => [5, 13], '--force' => true, ]);
    Artisan::queue('mail:send', [ 'user' => $user, '--queue' => 'default' ])->onConnection('redis')->onQueue('commands'); // Run in the background by configured queue worker.

    //* Stub Customization:
    // artisan:make commands are defined in stud files. We can customize it by publishing:
    // php artisan stub:publish

    //* Events: Artisan dispatched three events:
    // ArtisanStarting, CommandStarting, CommandFinished.
}