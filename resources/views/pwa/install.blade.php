<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install App</title>
    @laravelPWA
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f4f6f9;
            margin: 0;
            text-align: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 90%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .btn-install {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
            display: none; /* Hidden by default until PWA is ready */
        }
        .btn-install:hover {
            background-color: #0056b3;
        }
        .ios-instructions {
            display: none;
            margin-top: 20px;
            font-size: 14px;
            color: #555;
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Install Our App</h1>
        <p>Install our Progressive Web App for a faster, app-like experience directly on your device.</p>
        
        <button id="install-button" class="btn-install">Install App</button>

        <div id="ios-instructions" class="ios-instructions">
            <strong>iOS Users:</strong> To install, tap the <b>Share</b> icon at the bottom of Safari and select <b>"Add to Home Screen"</b>.
        </div>
    </div>

    <script>
        let deferredPrompt;
        const installButton = document.getElementById('install-button');
        const iosInstructions = document.getElementById('ios-instructions');

        // Detect iOS
        const isIos = () => {
            const userAgent = window.navigator.userAgent.toLowerCase();
            return /iphone|ipad|ipod/.test(userAgent);
        }
        // Detect if already installed (standalone mode)
        const isInStandaloneMode = () => ('standalone' in window.navigator) && (window.navigator.standalone);

        if (isIos() && !isInStandaloneMode()) {
            iosInstructions.style.display = 'block';
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later.
            deferredPrompt = e;
            // Update UI notify the user they can install the PWA
            installButton.style.display = 'block';
        });

        installButton.addEventListener('click', (e) => {
            // Hide the app provided install promotion
            installButton.style.display = 'none';
            // Show the install prompt
            if (deferredPrompt) {
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                        installButton.style.display = 'block';
                    }
                    deferredPrompt = null;
                });
            }
        });

        window.addEventListener('appinstalled', (evt) => {
            console.log('App was successfully installed');
            installButton.style.display = 'none';
        });
    </script>
</body>
</html>
