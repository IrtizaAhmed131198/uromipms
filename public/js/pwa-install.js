/**
 * PWA Install Prompt Handler
 * Handles the "Install App" functionality for Progressive Web App
 */

(function() {
    'use strict';

    // Prevent duplicate execution
    if (window.__PWA_INSTALL_INITIALIZED__) {
        console.log('PWA Install script already initialized, skipping...');
        return;
    }
    window.__PWA_INSTALL_INITIALIZED__ = true;

    let deferredPrompt;
    let installButton = null;
    let installBanner = null;
    const INSTALL_STORAGE_KEY = 'pwa_install_dismissed';
    const INSTALL_DISMISS_DURATION = 7 * 24 * 60 * 60 * 1000; // 7 days in milliseconds

    // Check if app is already installed
    function isAppInstalled() {
        // Check if running as standalone
        if (window.matchMedia('(display-mode: standalone)').matches) {
            return true;
        }
        // Check if running in iOS standalone mode
        if (window.navigator.standalone === true) {
            return true;
        }
        return false;
    }

    // Check if user is authenticated (logged in)
    // Hide PWA prompt on all authenticated pages (after login), show only on public pages (login, register, etc.)
    function isUserAuthenticated() {
        const path = window.location.pathname;
        
        // List of public pages where PWA prompt should show (before login)
        const publicPages = [
            '/login', 
            '/register', 
            '/business/register', 
            '/pricing', 
            '/docs',
            '/',
            ''
        ];
        
        // Check if current path is a public page
        const isPublicPage = publicPages.some(page => {
            if (page === '/' || page === '') {
                // For root path, check if user is authenticated
                return path === '/' || path === '';
            }
            return path === page || path.startsWith(page + '/');
        });
        
        // If it's a public page, check if user is actually logged in
        if (isPublicPage) {
            // Check if APP.USER_ID exists and is not empty (set by Laravel for authenticated users)
            if (typeof APP !== 'undefined' && APP.USER_ID && APP.USER_ID !== '') {
                // User is logged in, don't show prompt
                return true;
            }
            // User is not logged in, show prompt on public pages
            return false;
        }
        
        // For all other pages (not in public list), assume user is authenticated - don't show prompt
        return true;
    }

    // Check if install was dismissed recently
    function wasInstallDismissed() {
        const dismissedTime = localStorage.getItem(INSTALL_STORAGE_KEY);
        if (!dismissedTime) {
            return false;
        }
        const now = Date.now();
        const dismissed = parseInt(dismissedTime, 10);
        return (now - dismissed) < INSTALL_DISMISS_DURATION;
    }

    // Create install banner
    function createInstallBanner() {
        console.log('createInstallBanner called');
        console.log('isAppInstalled:', isAppInstalled());
        console.log('wasInstallDismissed:', wasInstallDismissed());
        console.log('isUserAuthenticated:', isUserAuthenticated());
        
        if (isAppInstalled() || wasInstallDismissed() || isUserAuthenticated()) {
            console.log('Banner creation skipped - conditions not met');
            return;
        }

        // Check if banner already exists
        if (document.getElementById('pwa-install-banner')) {
            console.log('Banner already exists');
            return;
        }
        
        console.log('Creating banner...');

        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.className = 'pwa-install-banner';
        // Use PWA icon from manifest or fallback to logo
        const appIcon = `${window.location.origin}/images/icons/icon-192x192.png`;
        const appName = 'Innfusion';
        
        banner.innerHTML = `
            <div class="pwa-install-content">
                <div class="pwa-install-icon">
                    <img src="${appIcon}" alt="${appName} Icon" onerror="this.src='${window.location.origin}/img/logo-small.png'" />
                </div>
                <div class="pwa-install-text">
                    <h4>Install ${appName}</h4>
                    <p>Add to your home screen for quick access</p>
                </div>
                <div class="pwa-install-actions">
                    <button class="pwa-install-btn" id="pwa-install-button">
                        <i class="fas fa-download"></i> Install
                    </button>
                    <button class="pwa-dismiss-btn" id="pwa-dismiss-button" title="Dismiss">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;

        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            .pwa-install-banner {
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                padding: 16px;
                z-index: 10000;
                max-width: 400px;
                width: calc(100% - 40px);
                display: none;
                animation: slideUp 0.3s ease-out;
            }
            
            @keyframes slideUp {
                from {
                    transform: translateX(-50%) translateY(100px);
                    opacity: 0;
                }
                to {
                    transform: translateX(-50%) translateY(0);
                    opacity: 1;
                }
            }
            
            .pwa-install-content {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            
            .pwa-install-icon {
                flex-shrink: 0;
            }
            
            .pwa-install-icon img {
                width: 48px;
                height: 48px;
                border-radius: 10px;
                object-fit: contain;
            }
            
            .pwa-install-text {
                flex: 1;
                min-width: 0;
            }
            
            .pwa-install-text h4 {
                margin: 0 0 4px 0;
                font-size: 16px;
                font-weight: 600;
                color: #333;
            }
            
            .pwa-install-text p {
                margin: 0;
                font-size: 13px;
                color: #666;
            }
            
            .pwa-install-actions {
                display: flex;
                gap: 8px;
                flex-shrink: 0;
            }
            
            .pwa-install-btn {
                background: #3c8dbc;
                color: white;
                border: none;
                border-radius: 8px;
                padding: 8px 16px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: background 0.2s;
            }
            
            .pwa-install-btn:hover {
                background: #2e6da4;
            }
            
            .pwa-install-btn:active {
                transform: scale(0.98);
            }
            
            .pwa-dismiss-btn {
                background: transparent;
                border: none;
                color: #999;
                cursor: pointer;
                padding: 8px;
                font-size: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                transition: background 0.2s;
            }
            
            .pwa-dismiss-btn:hover {
                background: #f0f0f0;
            }
            
            @media (max-width: 480px) {
                .pwa-install-banner {
                    bottom: 10px;
                    left: 10px;
                    right: 10px;
                    transform: none;
                    width: auto;
                    max-width: none;
                }
                
                .pwa-install-content {
                    flex-wrap: wrap;
                }
                
                .pwa-install-text {
                    flex: 1 1 100%;
                }
                
                .pwa-install-actions {
                    flex: 1 1 100%;
                    justify-content: space-between;
                }
                
                .pwa-install-btn {
                    flex: 1;
                    justify-content: center;
                }
            }
        `;

        document.head.appendChild(style);
        document.body.appendChild(banner);
        
        console.log('Banner element created and added to DOM');

        installBanner = banner;
        installButton = document.getElementById('pwa-install-button');
        const dismissButton = document.getElementById('pwa-dismiss-button');
        
        console.log('Install button found:', !!installButton);
        console.log('Dismiss button found:', !!dismissButton);

        // Install button click handler
        if (installButton) {
            installButton.addEventListener('click', handleInstallClick);
        }

        // Dismiss button click handler
        if (dismissButton) {
            dismissButton.addEventListener('click', () => {
                dismissBanner();
            });
        }
    }

    // Show install banner
    function showInstallBanner() {
        console.log('=== showInstallBanner called ===');
        console.log('installBanner exists:', !!installBanner);
        console.log('isAppInstalled:', isAppInstalled());
        console.log('wasInstallDismissed:', wasInstallDismissed());
        console.log('isUserAuthenticated:', isUserAuthenticated());
        
        if (!installBanner) {
            console.error('ERROR: installBanner is null!');
            return;
        }
        
        if (isAppInstalled()) {
            console.log('Banner not shown - App already installed');
            return;
        }
        
        if (wasInstallDismissed()) {
            console.log('Banner not shown - Was dismissed recently');
            return;
        }
        
        if (isUserAuthenticated()) {
            console.log('Banner not shown - User is authenticated');
            return;
        }
        
        // Show the banner
        installBanner.style.display = 'block';
        console.log('✓ Banner displayed successfully');
        console.log('Banner element:', installBanner);
        console.log('Banner display style:', installBanner.style.display);
    }

    // Dismiss banner
    function dismissBanner() {
        if (installBanner) {
            installBanner.style.display = 'none';
            localStorage.setItem(INSTALL_STORAGE_KEY, Date.now().toString());
        }
    }

    // Handle install button click
    function handleInstallClick() {
        if (deferredPrompt) {
            // Show the install prompt
            deferredPrompt.prompt();

            // Wait for the user to respond to the prompt
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                    // Hide the banner
                    dismissBanner();
                } else {
                    console.log('User dismissed the install prompt');
                }
                // Clear the deferredPrompt
                deferredPrompt = null;
            });
        } else {
            // For iOS or browsers that don't support beforeinstallprompt
            showIOSInstructions();
        }
    }

    // Show iOS installation instructions
    function showIOSInstructions() {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        
        if (isIOS && isSafari) {
            alert('To install this app:\n\n1. Tap the Share button\n2. Scroll down and tap "Add to Home Screen"\n3. Tap "Add"');
            dismissBanner();
        } else {
            // Show generic instructions
            alert('To install this app, look for the install option in your browser\'s menu.');
            dismissBanner();
        }
    }

    // Service worker is already registered by Laravel PWA package (@laravelPWA directive)
    // No need to register again here to avoid duplicate registration
    function registerServiceWorker() {
        // Service worker registration is handled by @laravelPWA directive in layout files
        // This function is kept for compatibility but does nothing
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            console.log('Service Worker already registered by Laravel PWA package');
        }
    }

    // Listen for beforeinstallprompt event (for browsers that support it)
    // Use named function to allow removal if needed
    function handleBeforeInstallPrompt(e) {
        console.log('beforeinstallprompt event fired');
        // Don't show if user is authenticated (logged in)
        if (isUserAuthenticated()) {
            console.log('User authenticated, skipping install prompt');
            return;
        }
        
        // Prevent the default install prompt
        e.preventDefault();
        // Store the event for later use
        deferredPrompt = e;
        console.log('deferredPrompt stored');
        
        // Create and show banner if not already shown
        if (!installBanner && !isAppInstalled() && !wasInstallDismissed()) {
            createInstallBanner();
            showInstallBanner();
        } else {
            console.log('Banner not created - already exists or conditions not met');
        }
    }
    
    // Remove existing listener if any, then add new one
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

    // Listen for app installed event
    window.addEventListener('appinstalled', (evt) => {
        console.log('App was installed');
        deferredPrompt = null;
        dismissBanner();
    });

    // Initialize PWA install banner immediately on page load
    // This ensures the prompt appears as soon as users visit the domain
    // But NOT on authenticated pages (after login)
    function initializePWAInstall() {
        console.log('=== PWA Install Initialization ===');
        console.log('isAppInstalled():', isAppInstalled());
        console.log('wasInstallDismissed():', wasInstallDismissed());
        console.log('isUserAuthenticated():', isUserAuthenticated());
        console.log('Current path:', window.location.pathname);
        console.log('APP.USER_ID:', typeof APP !== 'undefined' ? APP.USER_ID : 'APP not defined');
        
        // Don't show if already installed, dismissed, or user is authenticated
        if (isAppInstalled() || wasInstallDismissed() || isUserAuthenticated()) {
            console.log('Banner initialization skipped - conditions not met');
            return;
        }

        console.log('Creating and showing banner...');
        // Create banner immediately
        createInstallBanner();
        
        // Show banner immediately (no delay)
        // For browsers that support beforeinstallprompt, the button will work when the event fires
        // For other browsers (iOS Safari, etc.), we'll show instructions
        showInstallBanner();
    }

    // Initialize when DOM is ready
    function startInitialization() {
        console.log('Starting PWA Install initialization...');
        console.log('Document ready state:', document.readyState);
        
        // Wait a bit for APP object to be defined (set by Laravel)
        setTimeout(function() {
            console.log('Delayed initialization - checking APP object...');
            console.log('APP defined:', typeof APP !== 'undefined');
            if (typeof APP !== 'undefined') {
                console.log('APP.USER_ID:', APP.USER_ID);
            }
            initializePWAInstall();
        }, 500);
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startInitialization);
    } else {
        // DOM is already ready
        startInitialization();
    }

    // Debug: Log initialization status
    console.log('=== PWA Install Script Loaded ===');
    console.log('Script version: 2.0');
    console.log('App Installed:', isAppInstalled());
    console.log('Install Dismissed:', wasInstallDismissed());
    console.log('User Authenticated:', isUserAuthenticated());
    console.log('Current Path:', window.location.pathname);
    console.log('APP object exists:', typeof APP !== 'undefined');
    if (typeof APP !== 'undefined') {
        console.log('APP.USER_ID:', APP.USER_ID);
    }

})();

