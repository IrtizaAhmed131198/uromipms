<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1a1f2e">
    <title>Install Innfusion App</title>
    @laravelPWA
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f1219 0%, #1a1f2e 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .install-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 24px;
            padding: 40px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        /* Subtle glowing orb in background */
        .install-card::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 50%);
            z-index: -1;
            pointer-events: none;
        }

        .app-icon {
            width: 96px;
            height: 96px;
            border-radius: 22px;
            background: #fff;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            padding: 15px;
        }
        
        .app-icon img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
        }

        p {
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .btn-install {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            padding: 16px 32px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 14px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        .btn-install:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(37, 99, 235, 0.4);
        }

        .btn-install:active {
            transform: translateY(1px);
        }

        .instructions {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 20px;
            text-align: left;
            margin-top: 24px;
            display: none;
            animation: fadeIn 0.4s ease-out;
        }

        .instructions.show {
            display: block;
        }

        .instructions h3 {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            margin-bottom: 12px;
        }

        .instructions ol {
            padding-left: 20px;
            color: #cbd5e1;
            font-size: 14px;
            line-height: 1.8;
        }

        .instructions li span {
            font-weight: 600;
            color: #fff;
        }

        .badge {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 20px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .footer-link {
            display: block;
            margin-top: 24px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }
        
        .footer-link:hover {
            color: #94a3b8;
        }

        /* Status indicators */
        #status-area {
            display: none;
        }
        .success-check {
            width: 60px; height: 60px;
            background: #10b981;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            color: white;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>

    <div class="install-card">
        <div id="main-content">
            <div class="app-icon">
                @if(file_exists(public_path('uploads/logo.png')))
                    <img src="{{ asset('uploads/logo.png') }}" alt="Logo">
                @elseif(file_exists(public_path('images/icons/icon-192x192.png')))
                    <img src="{{ asset('images/icons/icon-192x192.png') }}" alt="Logo">
                @elseif(file_exists(public_path('img/logo-small.png')))
                    <img src="{{ asset('img/logo-small.png') }}" alt="Logo">
                @else
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18"></path>
                        <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                        <path d="M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"></path>
                        <path d="M10 9h4"></path>
                        <path d="M10 13h4"></path>
                    </svg>
                @endif
            </div>
            
            <div class="badge">Edric Tech</div>
            
            <h1>Innfusion Mobile</h1>
            <p>Get the complete Innfusion experience right on your device. Faster loading, offline support, and quick access.</p>
            
            <button id="install-button" class="btn-install" style="display: none;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Install Application
            </button>

            <!-- Instructions for unsupported/iOS -->
            <div id="instructions" class="instructions">
                <h3>Installation Guide</h3>
                <div id="ios-guide" style="display: none;">
                    <ol>
                        <li>Tap the <span>Share</span> button at the bottom of Safari.</li>
                        <li>Scroll down and tap <span>Add to Home Screen</span>.</li>
                        <li>Tap <span>Add</span> in the top right corner.</li>
                    </ol>
                </div>
                <div id="desktop-guide" style="display: none;">
                    <ol>
                        <li>Look for the <span>Install icon</span> in your browser's address bar.</li>
                        <li>Click it and select <span>Install</span>.</li>
                    </ol>
                </div>
            </div>
        </div>

        <div id="status-area">
            <div class="success-check">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h1>Installed!</h1>
            <p>The app has been successfully added to your device. You can now launch it from your home screen.</p>
        </div>

        <a href="{{ url('/') }}" class="footer-link">Continue to website instead</a>
    </div>

    <script>
        let deferredPrompt;
        const installButton = document.getElementById('install-button');
        const instructionsPanel = document.getElementById('instructions');
        const iosGuide = document.getElementById('ios-guide');
        const desktopGuide = document.getElementById('desktop-guide');
        
        const mainContent = document.getElementById('main-content');
        const statusArea = document.getElementById('status-area');

        // Detect Platform
        const userAgent = window.navigator.userAgent.toLowerCase();
        const isIos = /iphone|ipad|ipod/.test(userAgent);
        const isMacSafari = /macintosh/.test(userAgent) && /safari/.test(userAgent) && !/chrome/.test(userAgent);
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        if (isStandalone) {
            // Already installed
            mainContent.style.display = 'none';
            statusArea.style.display = 'block';
            statusArea.innerHTML = `
                <div class="success-check">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h1>Already Installed</h1>
                <p>You're currently using the installed app.</p>
            `;
        } else if (isIos || isMacSafari) {
            // Apple devices don't support beforeinstallprompt
            instructionsPanel.classList.add('show');
            iosGuide.style.display = 'block';
        } else {
            // Wait for Chrome/Edge prompt
            setTimeout(() => {
                if (!deferredPrompt) {
                    instructionsPanel.classList.add('show');
                    desktopGuide.style.display = 'block';
                }
            }, 3000);
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            installButton.style.display = 'flex';
            instructionsPanel.classList.remove('show');
        });

        installButton.addEventListener('click', async () => {
            if (!deferredPrompt) return;
            
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                installButton.style.display = 'none';
            }
            deferredPrompt = null;
        });

        window.addEventListener('appinstalled', (evt) => {
            mainContent.style.display = 'none';
            statusArea.style.display = 'block';
        });
    </script>
</body>
</html>
