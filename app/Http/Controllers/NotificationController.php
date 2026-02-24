<?php

namespace App\Http\Controllers;

use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Notification;

class NotificationController {

    public function notification(){
        // Sending notifications across a variety of delivery channels, like email, database, broadcast, sms (vonage), and slack.
        // Notifications may also be stored in a database so they may be displayed in your web interface.
        // Notifications should be short, informational messages that notify users of something that occurred in your application.
        $user = User::findOrfail(1);
        $users = User::all();
        $invoice = 2;

        //* Generate Notification: php artisan make:notification InvoicePaid. (see app\Noifications)
        
        //* Sending Notificatin: Using notify() of Notifiable trait or using Notification facade.
        // Using facade is useful when we wanna send notification for multiple entities such as a collection of users.
        // Call the trait in the model: use Notifiable.
        $user->notify(new InvoicePaid($invoice));
        Notification::send($users, new InvoicePaid($invoice));
        // Notification::sendNow(): send immediately even if the model has shouldQueue interface.

        //* Queuing Notification:
        // Configure queue and start a worker.
        // Sending notifications can take time, especially if the channel needs to make an external API call to deliver the notification.
        // To speed up, we can add shouldQueue interface and Queueable trait to the notification class. See the notification class.
        // Six jobs will be dispatched to the queue if your notification has three recipients and two channels.
        // Send notification normally.
        $user->notify(new InvoicePaid($invoice))->delay(now()->plus(minutes: 10)); // delay the delivery of the notification.
        // Delay amount for specific channels: delay(['mail' => now()->plus(minutes: 5), 'sms' => now()->plus(minutes: 10),])
        // or, call withDelay in Notification class.
        $user->notify((new InvoicePaid($invoice))->afterCommit()); // If queued notification in a db transaction, or call it in class constructor.

        //* On Demand Notification:
        // Send a notification to a guest user who is not in database.
        Notification::route('mail', 'taylor@example.com')
                      ->route('vonage', '5555555555')
                      ->route('slack', '#slack-channel')
                      ->route('broadcast', [new Channel('channel-name')])
                      ->notify(new InvoicePaid($invoice));
        // Giving reciepent name: 'mail', ['barrett@example.com' => 'Barrett Blair']
        // ad-hoc routing info for multiple routes: routes(['mail' => ['barrett@example.com' => 'Barrett Blair'], 'vonage' => '5555555555'])

        //* Database Notification:
        // stores the notification information in a database table.
        // can query the table to display the notifications in your application's user interface.
        // php artisan make:notifications-table
        // If your notifiable models are using UUID or ULID primary keys, you should replace the morphs method with uuidMorphs or ulidMorphs in the notification table migration.
        // See InvoicePaid class.
        // The Notifiable trait include a relationship called notifications. We can access it:
        foreach ($user->notifications as $notification) {echo $notification->type;}
        foreach ($user->unreadNotifications as $notification) {} // Only unread notifications.
        foreach ($user->readNotifications as $notification) {} // Only read notifications.
        // Marking notification as read when user views: $notification->markAsRead();
        // collections mark as read at once: $user->unreadNotifications->markAsRead();
        // Using mass assign: $user->unreadNotifications()->update(['read_at' => now()]);
        // Remove from database: $user->notifications()->delete();

        //* Broadcast Notification:
        // See InvoicePaid class.

        //* Mail Notification:
        // Use a Mailable when your primary goal is to send a high-quality, custom-branded email. It is focused entirely on the email's content, layout, and attachments.
        // Use a Notification when you want to tell a user "Something happened!" and you might want to tell them via multiple channels at once.
        // Best for: "You have a new comment," "Your order has shipped," or "Suspicious login detected."
        // The "Magic" Power: A single Notification class can send an Email, a Database alert (for a bell icon), a Slack message, and an SMS—all from one file.
        // See InvoicePaid class.
        // Previewing rendered mail in browser: Take a Route then:
        // return (new InvoicePaid($invoice))->toMail($invoice->user);

        //* SMS Notification:
        // Sending SMS notifications in Laravel is powered by Vonage (formerly known as Nexmo).
        // Install Two packages: composer require laravel/vonage-notification-channel guzzlehttp/guzzle
        // Env variables: VONAGE_KEY, VONAGE_SECRET, VONAGE_SMS_FROM (hone number that your SMS messages should be sent from by default).
        // See InvoicePaid class.

        //* Slack Notifications:
        // composer require laravel/slack-notification-channel
        // Create a Slack App for your Slack workspace.
        // https://laravel.com/docs/12.x/notifications#slack-notifications.

        //* Other Notifications:
        // If we want other channels like telegram, apple push etc: 
        // https://laravel-notification-channels.com/about/#viability.

        //* Localizing Notification:
        $user->notify((new InvoicePaid($invoice))->locale('es'));
        Notification::locale('es')->send($users, new InvoicePaid($invoice));
        // If user can prefer locale, we can do it in model: 1. implements HasLocalePreference 2. implement preferredLocale(). See Flight Class. Now no need to call locale() method here.

        //* Testing:
        // https://laravel.com/docs/12.x/notifications#testing

        //* Notification Events:
        // NotificationSending, NotificationSent
        // In listener (Exm- CheckNotificationStatus) we can use it:
        // public function handle(NotificationSending $event): void.
        // We can access for sending event: $event->channel, $event->notifiable, $event->notification.
        // Access for sent event: $event->channel, $event->notifiable, $event->notification, $event->response.

        //* Custom Channels:
        // may want to write your own drivers to deliver notifications via other channels.
        // https://laravel.com/docs/12.x/notifications#custom-channels
    }

    public function mail(){
        // Laravel provides a clean, simple email API powered by the popular Symfony Mailer component. 
        // Drivers: SMTP, Mailgun, Postmark, Resend, Amazon SES, and sendmail.
        // The API based drivers such as Mailgun, Postmark, and Resend are often simpler and faster than sending mail via SMTP servers. 
        // To use Mailgun: composer require symfony/mailgun-mailer symfony/http-client
        // In mail config file, make the default mailer. Make sure mailurs array have that driver.
        // Put mailgun credentials like domain, secret, endpoint etc in config/services.php
        // If not using US mailgun region, can use your region using in services: 'endpoint' => env('MAILGUN_ENDPOINT', 'api.eu.mailgun.net'), 
        // See documentations for other drivers configuration.
        // Failover Driver: Sometimes, an external service you have configured to send your application's mail may be down.
        // We can use multiple drivers as failover if mailgun is down in mail config file- 'mailers' => [ 'failover' => [...
        // Have to make the default driver then: MAIL_MAILER=failover. It provides high availability.
        // The roundrobin transport which provides load balancing allows you to distribute your mailing workload across multiple mailers.
        // Define a mailer within your application's mail configuration file that uses the roundrobin transport. and 'default' => env('MAIL_MAILER', 'roundrobin').
        // The round robin transport selects a random mailer from the list of configured mailers and then switches to the next available mailer for each subsequent email. 

        //* Local Development:
        // When developing an application that sends email, you probably don't want to actually send emails to live email addresses. 
        // Can use log driver. Instead of sending your emails, the log mail driver will write all email messages to your log files.
        // Alternatively, you may use a service like HELO or Mailtrap and the smtp driver to send your email messages to a "dummy" mailbox.
        // If you are using Laravel Sail, you may preview your messages using Mailpit- http://localhost:8025.
        // Finally, use alwaysTo() in boot of service provider when developmen. See AppServiceProvider.

        // Create Mailable class: php artisan make:mail OrderShipped 
        // See app/mail.

        //* Sending Email:
        $flight = Flight::findOrFail(1);
        Mail::to(User::find(1))
              ->cc($moreUsers) // cc: carbon copy, you are sending them a "public" copy of the email. It signals that these people should stay informed but aren't necessarily the ones expected to take action.
              ->bcc($evenMoreUsers) // bcc: blind carbon copy. No one in the "To" or "CC" fields can see the people in the BCC field. In fact, BCC recipients cannot even see each other.
              // You put all 50 addresses in the BCC field so that Customer A doesn't see Customer B's personal email address.
              ->send(new OrderShipped($flight));
        // We can loop over multiple reciepents to send mail.
        // If different mailer, not default: Mail::mailer('postmark')
        // Queueing a mail: ->queue(new OrderShipped($order)). Use queue method rather than send().
        // Delay the queue: ->later(now()->plus(minutes: 10), new OrderShipped($order)).
        // Can use ->onConnection('sqs')->onQueue('emails') if not using default.
        // If we want always be queued, just implements ShouldQueue interface in Mailable class. So, even if we use send, it will be queued always.
        // For db transaction: ->afterCommit(). or in Mailable class constructor: $this->afterCommit().
        // Queue failed exception: In mailable class can call failed(Throwable $exception).

        return (new InvoicePaid($invoice))->render(); // Capture the HTML content rather than sending.
        return new InvoicePaid($invoice); // Just show the preview in browser.

        // Localizing: 
        Mail::to($request->user())->locale('es')->send()
        // If user preferred locale then call preferredLocale() in model , implementing HasLocalePreference interface.

        //* Testing:
        // https://laravel.com/docs/12.x/mail#testing-mailables

        //* Events:
        // MessageSending, MessageSent. These events are dispatched when the mail is being sent, not when it is queued.
        
        //* Custom and Additional Symphony Transport:
        // https://laravel.com/docs/12.x/mail#custom-transports
    }
}