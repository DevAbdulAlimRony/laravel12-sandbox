<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Symfony\Component\Mime\Email;
use Illuminate\Notifications\Messages\VonageMessage;

class InvoicePaid extends Notification implements ShouldQueue
{
    //* The must part- Specify Channel:
    public function via(object $notifiable): array{
        return $notifiable->prefer_sms ? ['vonage'] : ['email', 'database'];
    }

    //* Queue Notification:
    use Queueable;

    // If need data privacy and integrity: mplements ShouldQueue, ShouldBeEncrypted.

    // Properties for Queue Notifications can be used:
    public $tries = 5; // Number of times the notification may be attempted.
    public $timeout = 120; // Number of seconds the notification can run before timing out.
    public $maxExceptions = 3; //  Maximum number of unhandled exceptions to allow before failing.

    public function __construct(){
        $this->onConnection('redis'); // If different queue connection rather than default then call it. or use viaConnections.
        $this->afterCommit(); // When notification in db transaction.
    }

    // Delay notification when queable.
    public function withDelay(object $notifiable): array{
        return [
            'mail' => 5,
            'sms' => now()->plus(minutes: 10),
        ];
    }

    // Determine which connections should be used for each notification channel.
    public function viaConnections(): array{
        return [
            'mail' => 'redis', 'database' = 'sync',
        ];
    }

    // Determine which queues should be used for each notification channel.
    public function viaQueue(): array{
        return [
            'mail' => 'mail-queue', 'slack' => 'slack-queue',
        ];
    }

    // Calculate the number of seconds to wait before retrying the notification.
    public function backoff(): int {
        return 3;
    }

    // Determine the time at which the notification should timeout.
    public function retryUntil(): DateTime{
        return now()->plus(minutes: 5);
    }

    // Get the middleware the queued notification job should pass through.
    public function middleware(object $notifiable, string $channel){
        return match($channel){
            'mail' => [new RateLimited('postmark')],
            default => [],
        }
    }

    // Make the final determination on whether the queued notification should be sent after it is being processed by a queue worker.
    public function shouldSend(object $notifiable, string $channel): bool{
        return $this->invoice->isPaid();
    }

    // Execute code after a queued notification has been sent:
    public function afterSending(object $notifiable, string $channel, mixed $response): void{}

    //* Database Notification:
    // Get the array representation of the notification.
    // toArray() is also used for broadcast, if we can use toDatabase() also here.
    public function toArray(object $notifiable): array{
        return [
            'invoice_id' => $this->invoice->id,
        ];
    }

    // Type column will be set to the notification's class name by default, and the read_at column will be null.
    // But can override:
    public function databaseType(object $notifiable): string {
        return 'invoice-paid';
    }
    public function initialDatabaseReadAtValue(): ?Carbon{
        return null;
    }

    //* Broadcast Notification:
    //  If the toBroadcast method does not exist, the toArray method will be used to gather the data that should be broadcast.
    public function toBroadcast(object $notifiable): BroadcastMessage{
        return new BroadcastMessage([
            'invoice_id' => $this->invoice->id,
        ]);
        // All broadcast notifications are queued for broadcasting.
        // Can use ->onConnection() and onQueue().
    }
    // Customize the column type:
    public function broadcastType(): string{
        return 'broadcast.message';
    }
    // Customize channel:
    public function receivesBroadcastNotificationsOn(): string{
        return 'users.'.$this->id;
    }
    // Notifications will broadcast on a private channel formatted using a {notifiable}.{id} convention.
    // We can listen using Ably from frontend or View file: https://laravel.com/docs/12.x/notifications#listening-for-notifications.

    //* Mail Notification:
    public function toMail(object $notifiable): MailMessage{
        return (new MailMessage)
               ->mailer('postmark') // If not provided, by default use mailer of mail config file.
               ->from('barrett@example.com', 'Barrett Blair') // If not provided, by default use mail config file's sender.
               ->subject('Notification Subject') // If not provided, by default class name in title case: Invoice Paid.
               ->greeting('Hello!')
               ->line('One of your invoices has been paid!')
               ->lineIf($this->amount > 0, "Amount paid: {$this->amount}")
               ->action('View Invoice', $url)
               ->line('Thank you for using our application!')
               ->attach('/path/to/file') // Can use attachable object also. Can give multiple file links also.
               ->attach('/path/to/file', [ 'as' => 'name.pdf', 'mime' => 'application/pdf']) // with name and mime type
               ->attachData($this->pdf, 'name.pdf', ['mime' => 'application/pdf']) // raw data attachment.
               ->tag('upvote')
               ->metadata('comment_id', $this->comment->id)
               ->withSymfonyMessage(function (Email $message) {
                    // deeply customize the message before it is delivered
                    $message->getHeaders()->addTextHeader('Custom-Header', 'Header Value');
               })

        // tag and metadata is supported by Mailgun and Postmark which is useful to group and track emails sent by the application.
        // name in config/app.php will be used as header and footer automatically.
        // ->error(): call to action button will be red instead of black.
        // Using view blade file: (new MailMessage)->view( 'mail.invoice.paid', ['invoice' => $this->invoice]);
        // Can use text() method if plain text view. or specify: ['mail.invoice.paid', 'mail.invoice.paid-text']
        // Rather than so many method chaning, we can send a full Mailable object:  return (new InvoicePaidMailable($this->invoice))->to($notifiable->email)
        // If notification is on demand, $notifiable instanceof AnonymousNotifiable ? $notifiable->routeNotificationFor('mail') : $notifiable->
        
        
        //* Customizing recipient:
        // When sending notifications via the mail channel, the notification system will automatically look for an email property on your notifiable entity.
        // Can customize using routeNotificationForMail(), see Flight model.
    }
    // Customizing the html and plain text template: php artisan vendor:publish --tag=laravel-notifications.

    //* Markdown Mail Notification:
    // Laravel is able to render beautiful, responsive HTML templates for the messages while also automatically generating a plain-text counterpart.
    // php artisan make:notification InvoicePaid --markdown=mail.invoice.paid
    // In toMail(): return (new MailMessage)->subject('Invoice Paid')->markdown('mail.invoice.paid', ['url' => $url]);
    // Use component in markdown template: <x-mail::message>, <x-mail::button :url="$url">, <x-mail::panel>, <x-mail::table>.
    // Customizing the components: php artisan vendor:publish --tag=laravel-mail (resources/views/vendor/mail directory.)
    // Customizing css: After exporting the components, the resources/views/vendor/mail/html/themes directory will contain a default.css file.
    // If you would like to build an entirely new theme for Laravel's Markdown components, you may place a CSS file within the html/themes directory. Then,
    // update the theme option of the mail configuration file to match the name of your new theme.

    //* SMS Notification:
    public function toVonage(object $notifiable): VonageMessage {
        return (new VonageMessage)
               ->content('Your SMS message content')
               ->unicode() // Call only if message contains unicode character like 😊, 🚀, ✅, ⚠️, ❤️, 📦
               ->from('15554443333') // If sender number is different from env variable VONAGE_SMS_FROM
               ->clientReference((string) $notifiable->id); // If like to keep track of costs per user and generate report.
    }
    // Route notifications for the Vonage channel. tell the system where to send the message.
    public function routeNotificationForVonage(Notification $notification): string {
        return $this->phone_number;
    }
}