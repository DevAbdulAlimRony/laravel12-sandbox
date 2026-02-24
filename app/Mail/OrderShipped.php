<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Symfony\Component\Mime\Email;

class OrderShipped extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct()
    {
        public User $user; // Now, the user will be available in view file of mail.
        // We can in blade or html: $user->name.

        // If wewant full control for passing data, we can use with in Content.
        // should set this data to protected or private properties so the data is not automatically made available to the template.
        protected Order $order;
    }

    // It defines the subject and sometimes the receipents.
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('jeffrey@example.com', 'Jeffrey Way'), // We can specify global from address in config/mail.php.
            replyTo: [new Address('taylor@example.com', 'Taylor Otwell'),], // We can specify global reply_to address within mail.php.
            subject: 'Order Shipped',

            // Some third-party email providers such as Mailgun and Postmark support message "tags" and "metadata":
            tags: ['shipment'],
            metadata: [ 'order_id' => $this->order->id, ],
            using: [ function (Email $message) { }, ] //  register custom callbacks that will be invoked with the Symfony Message instance before sending the message.
        );
    }

    // Which view file will be used.
    public function content(): Content
    {
        return new Content(
            view: 'mail.orders.shipped', // can use html: as alias for view also
            text: 'mail.orders.shipped-text' // plain text version of view.
            with: ['orderName' => $this->order->name,], // Now, we can access $orderPrice in view file.
            // When the email lands in an inbox, the user's email client (like Gmail or Outlook) looks at both and decides which one to show.
            // If you skip the text version, your "Spam Score" increases, making it more likely your email ends up in the junk folder.
            // Good for screen readers, good for non traditional device.
            // We can use markdown mail instead, When you use the markdown key instead of view, Laravel does the work for you:
            // Render a beautiful HTML version for modern clients, Automatically generate a clean plain-text version from your Markdown for older clients.
            // Generate markdown: php artisan make:mail OrderShipped --markdown=mail.orders.shipped.
            markdown: 'mail.orders.shipped',
            // See how can we write and customize markdown in Notification's class.
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath('/path/to/file')->as('name.pdf')->withMime('application/pdf');
            // name and mimetype is optional.
            // If stored in our filesystem disks: Attachment::fromStorage('/path/to/file')
            // Raw Data (ou have generated a PDF in memory and want to attach it to the email without writing it to disk):
            Attachment::fromData(fn () => $this->pdf, 'Report.pdf')->withMime('application/pdf'),

            //* Attachable Objects:
            // Our file can be a class like a Photo class.
            // So, it will be more convenient if we can just ass the Photo model to the attach method.
            // See Flight model (implements Attachable and toMailAttachment(), and then:
            return [$this->photo]; 
            // Attachment::fromStorage($this->path), fromStorageDisk('s3', $this->path)- if not default disk.
            // fromData(fn () => $this->content, 'Photo Name')
        ];
    }

    //* Inline Attachments:
    // In view: <img src="{{ $message->embed($pathToImage) }}">
    // Laravel automatically makes the $message variable available to all of email templates.
    // The $message variable is not available in plain-text message templates since plain-text messages do not utilize inline attachments.
    // Embed Raw Data: <img src="{{ $message->embedData($data, 'example-image.jpg') }}">

    //* Headers:
    // Sometimes we need to attach additional headers to the outgoing message.
    public function headers(): Headers {
        return new Headers(
            messageId: 'custom-message-id@example.com',
            references: ['previous-message@example.com'],
             text: [  'X-Custom-Header' => 'Custom Value', ],
        );
    }

}