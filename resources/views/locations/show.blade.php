<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $location->name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: radial-gradient(circle at top, #1a1f26, #0b0f14);
            color: #fff;
        }

        .container {
            max-width: 1100px;
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

        .card {
            max-width: 480px;
            margin: auto;
            background: #1f252d;
            border-radius: 20px;
            padding: 60px 40px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.45);
        }

        .icon {
            font-size: 54px;
            color: #2f80ff;
            margin-bottom: 20px;
        }

        .card h2 {
            margin-bottom: 18px;
        }

        .items {
            display: inline-block;
            background: rgba(255, 170, 0, 0.15);
            color: #f5a200;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 14px;
            margin-bottom: 35px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #ffb000;
            color: #000;
            padding: 15px 30px;
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

    <h1>{{ $location->name }}</h1>
    <div class="subtitle">
        {{ $location->city }}, {{ $location->state }}
    </div>

    <div class="card">
        <div class="icon">🍽️</div>

        <h2>MENU</h2>

        <div class="items">
            {{ $location->location_id }}
        </div><br>

        <a href="#" class="btn">
            View Menu →
        </a>
    </div>

</div>

</body>
</html>
