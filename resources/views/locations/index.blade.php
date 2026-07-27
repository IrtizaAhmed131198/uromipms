<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Business Locations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
            margin-bottom: 15px;
        }

        .subtitle {
            color: #f5a200;
            font-size: 18px;
            margin-bottom: 60px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }

        .card {
            background: #1f252d;
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.45);
        }

        .icon {
            font-size: 52px;
            color: #2f80ff;
            margin-bottom: 20px;
        }

        .card h2 {
            margin-bottom: 15px;
        }

        .items {
            display: inline-block;
            background: rgba(255, 170, 0, 0.15);
            color: #f5a200;
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #ffb000;
            color: #000;
            padding: 14px 28px;
            border-radius: 40px;
            font-weight: bold;
            text-decoration: none;
        }

        .btn:hover {
            background: #ffc733;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>Our Locations</h1>
    <div class="subtitle">Choose a business location</div>

    <div class="cards">
        @foreach($locations as $location)
            <div class="card">
                <div class="icon">📍</div>

                <h2>{{ $location->name }}</h2>
                <p>{{ $location->description ?? '' }}</p>
                <div class="items">
                    {{ $location->city }}, {{ $location->state }}
                </div><br>

                <a href="{{ url('location/'.$location->location_id . '-' . \Illuminate\Support\Str::slug($location->name)) }}"
                   class="btn">
                    View Details →
                </a>
            </div>
        @endforeach
    </div>

</div>

</body>
</html>
