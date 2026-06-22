<?php

namespace Webkul\B2BSuite\Mail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class Notification extends Mailable
{
    /**
     * Generic, queued B2B notification. Recipient, subject, template and the (optional)
     * related model are resolved by the Notifier so every B2B email shares one mailable.
     *
     * @param  string  $toEmail  Recipient address.
     * @param  string  $toName  Recipient display name.
     * @param  string  $subjectLine  Already-translated subject line.
     * @param  string  $template  Blade view, e.g. "b2b::emails.quote".
     * @param  array  $payload  View data; may include a "type" discriminator and scalars.
     * @param  ?Model  $model  Related record (quote, company, user) — serialized by id.
     */
    public function __construct(
        public string $toEmail,
        public string $toName,
        public string $subjectLine,
        public string $template,
        public array $payload = [],
        public ?Model $model = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [new Address($this->toEmail, $this->toName)],
            subject: $this->subjectLine,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->template,
            with: array_merge($this->payload, ['model' => $this->model]),
        );
    }
}
