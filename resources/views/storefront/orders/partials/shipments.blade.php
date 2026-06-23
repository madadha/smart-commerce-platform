@if($order->shipments->isNotEmpty())
    <div class="scp-order-card scp-shipment-card">
        <div class="scp-order-card-head">
            <h2>{{ $locale === 'ar' ? 'الشحن والتتبع' : ($locale === 'he' ? 'משלוח ומעקב' : 'Shipping & tracking') }}</h2>
            <span>{{ $order->shipments->count() }}</span>
        </div>
        @foreach($order->shipments as $shipment)
            <article style="padding:1rem 0;border-bottom:1px solid var(--scp-border,#e5e7eb)">
                <div style="display:flex;gap:1rem;justify-content:space-between;flex-wrap:wrap">
                    <div><small>{{ $shipment->shipment_number }}</small><h3>{{ $shipment->status->label() }}</h3></div>
                    <div>
                        @if($shipment->tracking_number)<strong>{{ $shipment->tracking_number }}</strong>@endif
                        @if($shipment->tracking_url)<a href="{{ $shipment->tracking_url }}" target="_blank" rel="noopener">{{ $locale === 'ar' ? 'تتبع لدى شركة الشحن' : ($locale === 'he' ? 'מעקב אצל חברת המשלוחים' : 'Track with carrier') }}</a>@endif
                    </div>
                </div>
                @if($shipment->estimated_delivery_at)
                    <p>{{ $locale === 'ar' ? 'الوصول المتوقع' : ($locale === 'he' ? 'מסירה משוערת' : 'Estimated delivery') }}: {{ $shipment->estimated_delivery_at->format('Y-m-d H:i') }}</p>
                @endif
                <div style="margin-top:1rem">
                    @foreach($shipment->events->where('is_customer_visible', true) as $event)
                        <div style="display:grid;grid-template-columns:minmax(110px,auto) 1fr;gap:1rem;padding:.7rem 0;border-top:1px solid var(--scp-border,#e5e7eb)">
                            <small>{{ $event->occurred_at?->format('Y-m-d H:i') }}</small>
                            <div><strong>{{ $event->title }}</strong>@if($event->description)<p>{{ $event->description }}</p>@endif @if($event->location)<small>{{ $event->location }}</small>@endif</div>
                        </div>
                    @endforeach
                </div>
            </article>
        @endforeach
    </div>
@endif
