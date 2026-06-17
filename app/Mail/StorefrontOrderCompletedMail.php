<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class StorefrontOrderCompletedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public Order $order;

    public string $mailLocale;

    public string $direction;

    public string $orderUrl;

    public string $invoiceUrl;

    public string $subjectText;

    public function __construct(Order $order, string $mailLocale = 'ar')
    {
        $this->order = $order->loadMissing([
            'customer',
            'items.product',
            'currency',
            'shippingMethod',
        ]);

        $this->mailLocale = in_array($mailLocale, ['ar', 'he', 'en'], true) ? $mailLocale : 'ar';
        $this->direction = in_array($this->mailLocale, ['ar', 'he'], true) ? 'rtl' : 'ltr';

        App::setLocale($this->mailLocale);

        $this->orderUrl = URL::signedRoute('storefront.orders.show', [
            'order' => $this->order->id,
            'lang' => $this->mailLocale,
        ]);

        $this->invoiceUrl = URL::signedRoute('storefront.orders.invoice', [
            'order' => $this->order->id,
            'lang' => $this->mailLocale,
        ]);

        $this->subjectText = match ($this->mailLocale) {
            'he' => 'ההזמנה שלך הושלמה - ' . $this->order->order_number,
            'en' => 'Your order has been completed - ' . $this->order->order_number,
            default => 'تم إكمال طلبك - ' . $this->order->order_number,
        };
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectText,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.storefront.order-completed',
            with: [
                'order' => $this->order,
                'mailLocale' => $this->mailLocale,
                'direction' => $this->direction,
                'orderUrl' => $this->orderUrl,
                'invoiceUrl' => $this->invoiceUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
