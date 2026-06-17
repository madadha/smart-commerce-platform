<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class StorefrontOrderCreatedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public Order $order;

    public string $orderUrl;

    public string $invoiceUrl;

    public string $direction;

    protected string $mailLocale;

    public function __construct(Order $order, string $locale = 'ar')
    {
        $this->order = $order;
        $this->mailLocale = in_array($locale, ['ar', 'he', 'en'], true) ? $locale : 'ar';
        $this->direction = in_array($this->mailLocale, ['ar', 'he'], true) ? 'rtl' : 'ltr';

        $this->orderUrl = URL::signedRoute('storefront.orders.show', [
            'order' => $order->id,
            'lang' => $this->mailLocale,
        ]);

        $this->invoiceUrl = URL::signedRoute('storefront.orders.invoice', [
            'order' => $order->id,
            'lang' => $this->mailLocale,
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectText(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.storefront.order-created',
            with: [
                'order' => $this->order,
                'locale' => $this->mailLocale,
                'direction' => $this->direction,
                'orderUrl' => $this->orderUrl,
                'invoiceUrl' => $this->invoiceUrl,
            ],
        );
    }

    private function subjectText(): string
    {
        $orderNumber = $this->order->order_number ?? $this->order->id;

        return match ($this->mailLocale) {
            'he' => 'ההזמנה שלך התקבלה #' . $orderNumber,
            'en' => 'Your order has been received #' . $orderNumber,
            default => 'تم استلام طلبك #' . $orderNumber,
        };
    }
}
