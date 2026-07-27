<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $location->name }}</title>

    <style>
        body {
            font-family: Inter, Arial, sans-serif;
            background: radial-gradient(circle at top, #1a1f26, #0b0f14);
            color: #fff;
        }

        a { text-decoration: none; }

        .back {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #fff;
        }

        .header {
            text-align: center;
            padding: 40px 20px 10px;
        }

        .header h1 {
            font-size: 34px;
            margin: 0;
        }

        /* TABS */
        .tabs {
            max-width: 1300px;
            margin: 30px auto;
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .tab {
            background: #1f252d;
            padding: 14px 22px;
            border-radius: 14px;
            color: #bfc7d5;
            font-weight: 600;
            display: flex;
            gap: 8px;
            border: 1px solid #2c3440;
        }

        .tab span {
            opacity: 0.6;
        }

        .tab.active {
            background: #ffb000;
            color: #000;
            border-color: #ffb000;
        }

        .section-title {
            text-align: center;
            font-size: 28px;
            margin: 40px 0 30px;
        }

        .products {
            max-width: 1300px;
            margin: auto;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 24px;
        }

        .product {
            background: #1f252d;
            border-radius: 18px;
            padding: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 2px solid #2c3440;
        }

        .product-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .thumb {
            width: 90px;
            height: 90px;
            flex: 0 0 90px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #2c3440;
            background: #0f141b;
        }
        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .price {
            font-size: 20px;
            font-weight: bold;
            color: #ffb000;
        }
        .product.with-image {
            border-color: #ffb000;
        }
    </style>
</head>

<body>

<a class="back" href="{{ route('locations.show', $location->location_id) }}">← Back</a>

<div class="header">
    <h1>{{ strtoupper($category->name) }}</h1>
</div>

<!-- TABS -->
<div class="tabs">

    <!-- ALL ITEMS -->
    <a class="tab {{ is_null($active_subcategory_id) ? 'active' : '' }}"
       href="{{ route('locations.category', [$location->location_id, $category->id]) }}">
        All Items <span>({{ $all_items_count }})</span>
    </a>

    <!-- SUBCATEGORIES -->
    @foreach($subcategories as $sub)
        <a class="tab {{ (int)$active_subcategory_id === (int)$sub->id ? 'active' : '' }}"
           href="{{ route('locations.category', [$location->location_id, $category->id]) }}?sub={{ $sub->id }}">
            {{ strtoupper($sub->name) }}
            <span>({{ $sub->products_count }})</span>
        </a>
    @endforeach
</div>

<div class="section-title">
    {{ is_null($active_subcategory_id) ? 'All Items' : 'Items' }}
</div>

<!-- PRODUCTS -->
<div class="products">
    @forelse($items as $item)
        <div class="product {{ $item->image ? 'with-image' : '' }}">
            <div class="product-left">
                <div class="thumb">
                    <img src="{{ $item->image ? asset('uploads/img/'.$item->image) : asset('img/default.png') }}" alt="{{ $item->name }}">
                </div>
                <div>
                    <strong>{{ $item->name }}</strong>
                     <p>{{ $item->product_description }}</p>
                </div>
                
            </div>
            <div class="price">₦{{ number_format($item->price, 0) }}</div>
        </div>
    @empty
        <p style="text-align:center;">No items found.</p>
    @endforelse
</div>

</body>
</html>
