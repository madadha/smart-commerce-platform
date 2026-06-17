@php
    $type = $product->product_type ?? null;

    if ($type instanceof \BackedEnum) {
        $type = $type->value;
    }

    $type = (string) $type;

    $stockValue = null;

    foreach (['stock_quantity', 'quantity', 'stock'] as $stockColumn) {
        if (isset($product->{$stockColumn}) && $product->{$stockColumn} !== null) {
            $stockValue = (int) $product->{$stockColumn};
            break;
        }
    }

    $isDigitalOrService = in_array($type, ['digital', 'service'], true);

    if ($isDigitalOrService && $stockValue === null) {
        $stockStatus = 'available';
        $stockText = \Illuminate\Support\Facades\Lang::has('storefront.stock.available')
            ? __('storefront.stock.available')
            : 'متوفر';
    } elseif ($stockValue === null) {
        $stockStatus = 'unknown';
        $stockText = \Illuminate\Support\Facades\Lang::has('storefront.stock.available')
            ? __('storefront.stock.available')
            : 'متوفر';
    } elseif ($stockValue <= 0) {
        $stockStatus = 'out';
        $stockText = \Illuminate\Support\Facades\Lang::has('storefront.stock.out_of_stock')
            ? __('storefront.stock.out_of_stock')
            : 'نفذ المخزون';
    } elseif ($stockValue <= 5) {
        $stockStatus = 'low';
        $stockText = \Illuminate\Support\Facades\Lang::has('storefront.stock.low_stock')
            ? __('storefront.stock.low_stock', ['count' => $stockValue])
            : 'كمية قليلة: ' . $stockValue . ' فقط';
    } else {
        $stockStatus = 'available';
        $stockText = \Illuminate\Support\Facades\Lang::has('storefront.stock.available')
            ? __('storefront.stock.available')
            : 'متوفر';
    }
@endphp

<div class="scp-stock-status {{ $stockStatus }}">
    <span></span>
    <strong>{{ $stockText }}</strong>
</div>
