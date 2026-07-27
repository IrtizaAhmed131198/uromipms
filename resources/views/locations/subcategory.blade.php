<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $subcategory->name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: radial-gradient(circle at top, #1a1f26, #0b0f14);
            color: #fff;
        }

        .container {
            max-width: 1300px;
            margin: auto;
            padding: 90px 20px;
        }

        h1 {
            text-align: center;
            font-size: 38px;
            margin-bottom: 60px;
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
        .product.with-image {
            border-color: #ffb000;
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
    </style>
</head>
<body>

<div class="container">

    <h1>{{ $subcategory->name }}</h1>

    <div class="products">
        @foreach($items as $item)
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
        @endforeach
    </div>

</div>

</body>
</html>
