<?php

namespace App\Mail;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ShipmentStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $mailLocale;

    public string $direction;

    public string $orderUrl;

    public function __construct(public Shipment $shipment)
    {
        $this->shipment->loadMissing(['order.customer', 'order.currency', 'shippingMethod', 'events']);
        $this->mailLocale = in_array($this->shipment->order->locale, ['ar', 'he', 'en'], true)
            ? $this->shipment->order->locale
            : 'ar';
        $this->direction = in_array($this->mailLocale, ['ar', 'he'], true) ? 'rtl' : 'ltr';
        $this->orderUrl = URL::signedRoute('storefront.orders.show', [
            'order' => $this->shipment->order_id,
            'lang' => $this->mailLocale,
        ]);
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->mailLocale) {
            'he' => 'עדכון משלוח להזמנה '.$this->shipment->order->order_number,
            'en' => 'Shipping update for order '.$this->shipment->order->order_number,
            default => 'تحديث شحن الطلب '.$this->shipment->order->order_number,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.storefront.shipment-status-updated');
    }

    public function attachments(): array
    {
        return [];
    }
}
