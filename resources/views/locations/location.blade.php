<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $location->name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/vendor.css') }}">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: radial-gradient(circle at top, #1a1f26, #0b0f14);
            color: #fff;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 90px 20px;
            text-align: center;
        }

        h1 {
            font-size: 42px;
            margin-bottom: 10px;
            color: #ffffff;
            font-weight: bold;
        }

        .subtitle {
            color: #f5a200;
            font-size: 16px;
            margin-bottom: 50px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }

        .card {
            background: #1f252d;
            border-radius: 20px;
            padding: 45px 30px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.45);
        }

        .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        .btn {
            display: inline-block;
            background: #ffb000;
            color: #000;
            padding: 12px 26px;
            border-radius: 40px;
            font-weight: bold;
            text-decoration: none;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>{{ $location->name }}</h1>
    <div class="subtitle">
        @if(filled($location->description))
            {!! nl2br(e($location->description)) !!}
        @else
            Select a category to view menu
        @endif
    </div>

    <div class="cards">
        @foreach($categories as $category)
            <div class="card">
                @php
                    $icon = !empty($category->icon) ? asset($category->icon) : null;

                    // Keyword → Font Awesome icon map (FA5 compatible — works with local vendor.css & FA6 CDN)
                    $name = strtolower($category->name);
                    $faIcon = 'fa-utensils'; // default
                    $iconMap = [
                        'drink'      => 'fa-cocktail',
                        'cocktail'   => 'fa-cocktail',
                        'shot'       => 'fa-wine-glass',
                        'smoothie'   => 'fa-blender',
                        'juice'      => 'fa-lemon',
                        'coffee'     => 'fa-coffee',
                        'tea'        => 'fa-mug-hot',
                        'water'      => 'fa-water',
                        'beer'       => 'fa-beer',
                        'wine'       => 'fa-wine-bottle',
                        'breakfast'  => 'fa-egg',
                        'brunch'     => 'fa-egg',
                        'toast'      => 'fa-bread-slice',
                        'bagel'      => 'fa-bread-slice',
                        'burger'     => 'fa-hamburger',
                        'pizza'      => 'fa-pizza',
                        'pasta'      => 'fa-bowl-food',
                        'noodle'     => 'fa-bowl-food',
                        'rice'       => 'fa-bowl-rice',
                        'salad'      => 'fa-leaf',
                        'sushi'      => 'fa-fish',
                        'seafood'    => 'fa-fish',
                        'fish'       => 'fa-fish',
                        'soup'       => 'fa-bowl-food',
                        'taco'       => 'fa-utensils',
                        'nacho'      => 'fa-utensils',
                        'platter'    => 'fa-utensils',
                        'protein'    => 'fa-drumstick-bite',
                        'chicken'    => 'fa-drumstick-bite',
                        'meat'       => 'fa-drumstick-bite',
                        'starter'    => 'fa-star',
                        'extra'      => 'fa-plus-circle',
                        'dessert'    => 'fa-ice-cream',
                        'cake'       => 'fa-birthday-cake',
                        'sweet'      => 'fa-candy',
                        'snack'      => 'fa-cookie',
                        'sandwich'   => 'fa-hotdog',
                        'africa'     => 'fa-globe-africa',
                        'electronic' => 'fa-plug',
                        'medicine'   => 'fa-pills',
                        'food'       => 'fa-utensils',
                        'brandy'     => 'fa-wine-glass',
                    ];
                    foreach ($iconMap as $keyword => $faClass) {
                        if (str_contains($name, $keyword)) {
                            $faIcon = $faClass;
                            break;
                        }
                    }
                @endphp
                <div class="icon">
                    @if($icon)
                        <img src="{{ $icon }}" alt="{{ $category->name }}"
                             style="height:80px;width:80px;object-fit:contain;"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                        <i class="fas {{ $faIcon }}" style="display:none;"></i>
                    @else
                        <i class="fas {{ $faIcon }}"></i>
                    @endif
                </div>
                <h3 style="color: #ffffff; font-weight: bold;">{{ $category->name }}</h3>

                <a href="{{ route('locations.category', [$location->location_id, $category->id]) }}"
                   class="btn">
                    View →
                </a>
            </div>
        @endforeach
    </div>

</div>

</body>
</html>
