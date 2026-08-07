<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1a1f2e">
    <title>Barcode Scanner — Innfusion</title>
    @laravelPWA
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #0f1219;
            color: #fff;
            height: 100dvh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ───── Header ───── */
        .header {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            z-index: 10;
            gap: 12px;
        }
        .header a {
            color: #7c8db5;
            text-decoration: none;
            font-size: 20px;
            display: flex;
            align-items: center;
        }
        .header-title { flex: 1; font-size: 17px; font-weight: 600; }
        .header-badge {
            background: #3b82f6;
            color: white;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        /* ───── Camera Area ───── */
        .camera-wrapper {
            position: relative;
            flex: 1;
            overflow: hidden;
        }
        #preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .scan-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .scan-frame {
            width: min(70vw, 280px);
            height: min(70vw, 280px);
            position: relative;
        }
        .scan-frame::before {
            content: '';
            position: absolute;
            inset: 0;
            border: 2px solid rgba(255,255,255,0.15);
            border-radius: 12px;
        }
        /* Corner brackets */
        .scan-frame::after {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 12px;
            background: 
                linear-gradient(white, white) top left / 24px 3px,
                linear-gradient(white, white) top left / 3px 24px,
                linear-gradient(white, white) top right / 24px 3px,
                linear-gradient(white, white) top right / 3px 24px,
                linear-gradient(white, white) bottom left / 24px 3px,
                linear-gradient(white, white) bottom left / 3px 24px,
                linear-gradient(white, white) bottom right / 24px 3px,
                linear-gradient(white, white) bottom right / 3px 24px;
            background-repeat: no-repeat;
        }
        /* Laser scan line */
        .scan-line {
            position: absolute;
            left: 6px; right: 6px;
            height: 2px;
            background: linear-gradient(90deg, transparent, #3b82f6, #60a5fa, #3b82f6, transparent);
            border-radius: 2px;
            box-shadow: 0 0 10px #3b82f6, 0 0 20px #3b82f6;
            animation: scan 2s ease-in-out infinite;
        }
        @keyframes scan {
            0%, 100% { top: 10px; }
            50% { top: calc(100% - 12px); }
        }
        .scan-hint {
            margin-top: 24px;
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            text-align: center;
        }

        /* ───── Camera Off Placeholder ───── */
        .camera-placeholder {
            width: 100%; height: 100%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: #131720;
            gap: 16px;
        }
        .camera-placeholder svg { opacity: 0.3; }
        .camera-placeholder p { color: rgba(255,255,255,0.4); font-size: 14px; }

        /* ───── Result Panel ───── */
        .result-panel {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: rgba(15,18,25,0.97);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255,255,255,0.08);
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            border-radius: 20px 20px 0 0;
            z-index: 20;
        }
        .result-panel.show { transform: translateY(0); }
        .result-handle {
            width: 40px; height: 4px;
            background: rgba(255,255,255,0.15);
            border-radius: 4px;
            margin: 0 auto 16px;
        }
        .result-product-name {
            font-size: 18px; font-weight: 700;
            margin-bottom: 4px;
        }
        .result-sku {
            font-size: 13px;
            color: #7c8db5;
            margin-bottom: 16px;
        }
        .result-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }
        .result-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 12px;
            padding: 12px;
        }
        .result-card-label {
            font-size: 11px;
            color: #7c8db5;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
        }
        .result-card-value {
            font-size: 20px;
            font-weight: 700;
        }
        .result-card-value.stock-ok { color: #34d399; }
        .result-card-value.stock-low { color: #fb923c; }
        .result-card-value.stock-out { color: #f87171; }
        .result-actions {
            display: flex; gap: 10px;
        }
        .btn-action {
            flex: 1;
            padding: 13px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-secondary { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.8); }
        .btn-secondary:hover { background: rgba(255,255,255,0.12); }

        /* ───── Bottom Controls ───── */
        .controls {
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 18px 24px;
            background: rgba(255,255,255,0.04);
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .ctrl-btn {
            width: 56px; height: 56px;
            border-radius: 50%;
            border: none;
            background: rgba(255,255,255,0.08);
            color: white;
            font-size: 20px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .ctrl-btn:active { transform: scale(0.9); background: rgba(255,255,255,0.16); }
        .ctrl-btn.danger { background: rgba(239,68,68,0.2); color: #f87171; }
        .scan-trigger {
            width: 70px; height: 70px;
            border-radius: 50%;
            border: 3px solid white;
            background: #3b82f6;
            color: white;
            font-size: 24px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 0 6px rgba(59,130,246,0.2);
            transition: all 0.2s;
        }
        .scan-trigger:active { transform: scale(0.92); }

        /* ───── Manual SKU Input ───── */
        .manual-panel {
            display: none;
            padding: 16px 18px;
            background: rgba(255,255,255,0.04);
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .manual-panel.show { display: flex; gap: 10px; }
        .manual-input {
            flex: 1;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            padding: 11px 14px;
            color: white;
            font-size: 15px;
            outline: none;
        }
        .manual-input::placeholder { color: rgba(255,255,255,0.3); }
        .manual-input:focus { border-color: #3b82f6; }
        .btn-search {
            background: #3b82f6;
            border: none;
            border-radius: 10px;
            padding: 11px 18px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        /* ───── Toasts ───── */
        .toast {
            position: fixed;
            top: 70px; left: 50%; transform: translateX(-50%);
            background: rgba(30,35,48,0.95);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 14px;
            z-index: 100;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
            white-space: nowrap;
        }
        .toast.show { opacity: 1; }
        .toast.error { border-color: #f87171; color: #f87171; }
        .toast.success { border-color: #34d399; color: #34d399; }
    </style>
</head>
<body>

<div class="header">
    <a href="{{ url()->previous() ?? url('/') }}">&#8592;</a>
    <span class="header-title">Barcode Scanner</span>
    <span class="header-badge">LIVE</span>
</div>

<div class="camera-wrapper" id="camera-wrapper">
    <div class="camera-placeholder" id="camera-placeholder">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
            <circle cx="12" cy="13" r="4"/>
        </svg>
        <p>Tap the scan button to start the camera</p>
    </div>
    <video id="preview" autoplay muted playsinline style="display:none;"></video>
    <div class="scan-overlay" id="scan-overlay" style="display:none;">
        <div class="scan-frame">
            <div class="scan-line" id="scan-line"></div>
        </div>
        <p class="scan-hint">Align the barcode within the frame</p>
    </div>

    <!-- Result Panel (slides up from bottom) -->
    <div class="result-panel" id="result-panel">
        <div class="result-handle"></div>
        <div class="result-product-name" id="r-name">-</div>
        <div class="result-sku" id="r-sku">-</div>
        <div class="result-grid">
            <div class="result-card">
                <div class="result-card-label">Category</div>
                <div class="result-card-value" id="r-category" style="font-size:15px;">-</div>
            </div>
            <div class="result-card">
                <div class="result-card-label">Selling Price</div>
                <div class="result-card-value" id="r-price">-</div>
            </div>
            <div class="result-card">
                <div class="result-card-label">Current Stock</div>
                <div class="result-card-value stock-ok" id="r-stock">-</div>
            </div>
            <div class="result-card">
                <div class="result-card-label">Unit</div>
                <div class="result-card-value" style="font-size:16px;" id="r-unit">-</div>
            </div>
        </div>
        <div class="result-actions">
            <button class="btn-action btn-primary" id="btn-view-product">View Product</button>
            <button class="btn-action btn-secondary" id="btn-scan-again">Scan Again</button>
        </div>
    </div>
</div>

<!-- Manual SKU Input -->
<div class="manual-panel" id="manual-panel">
    <input type="text" class="manual-input" id="manual-sku" placeholder="Enter SKU / Barcode manually...">
    <button class="btn-search" id="btn-manual-search">Search</button>
</div>

<div class="controls">
    <button class="ctrl-btn" id="btn-toggle-manual" title="Manual Input">&#9998;</button>
    <button class="scan-trigger" id="btn-scan" title="Start / Stop Scanner">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9V5a2 2 0 0 1 2-2h4M15 3h4a2 2 0 0 1 2 2v4M21 15v4a2 2 0 0 1-2 2h-4M9 21H5a2 2 0 0 1-2-2v-4"/>
            <line x1="8" y1="12" x2="16" y2="12"/>
        </svg>
    </button>
    <button class="ctrl-btn" id="btn-torch" title="Toggle Torch">&#9728;</button>
</div>

<div class="toast" id="toast"></div>

<!-- ZXing barcode library (MIT license, no external CDN risk) -->
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const API_URL = '{{ route("barcode.product-lookup") }}';

    let codeReader = null;
    let scanning = false;
    let lastScanned = null;
    let torchEnabled = false;

    // ── UI Elements ──
    const btnScan = document.getElementById('btn-scan');
    const btnTorch = document.getElementById('btn-torch');
    const btnManual = document.getElementById('btn-toggle-manual');
    const manualPanel = document.getElementById('manual-panel');
    const manualSku = document.getElementById('manual-sku');
    const btnManualSearch = document.getElementById('btn-manual-search');
    const preview = document.getElementById('preview');
    const placeholder = document.getElementById('camera-placeholder');
    const scanOverlay = document.getElementById('scan-overlay');
    const resultPanel = document.getElementById('result-panel');
    const toast = document.getElementById('toast');
    const btnViewProduct = document.getElementById('btn-view-product');
    const btnScanAgain = document.getElementById('btn-scan-again');

    // ── Toast helper ──
    function showToast(msg, type = '') {
        toast.className = 'toast show ' + type;
        toast.textContent = msg;
        setTimeout(() => toast.className = 'toast', 2800);
    }

    // ── Start Scanner ──
    async function startScanner() {
        if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            showToast('Camera requires HTTPS connection', 'error');
            return;
        }
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showToast('Camera API not supported by browser', 'error');
            return;
        }

        if (typeof ZXing === 'undefined') {
            showToast('Scanner library loading... Try again.', 'error');
            return;
        }
        
        codeReader = new ZXing.BrowserMultiFormatReader();
        try {
            // Explicitly request camera permission first to force the browser prompt
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            
            // Stop the initial stream immediately, we just needed the permission prompt
            stream.getTracks().forEach(track => track.stop());

            const devices = await ZXing.BrowserMultiFormatReader.listVideoInputDevices();
            if (devices.length === 0) { showToast('No camera hardware found', 'error'); return; }
            
            // Prefer back camera
            const device = devices.find(d => /back|rear|environment/i.test(d.label)) || devices[devices.length - 1];

            preview.style.display = 'block';
            placeholder.style.display = 'none';
            scanOverlay.style.display = 'flex';
            scanning = true;

            btnScan.innerHTML = `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;

            await codeReader.decodeFromVideoDevice(device.deviceId, 'preview', (result, err) => {
                if (result && result.getText() !== lastScanned) {
                    lastScanned = result.getText();
                    lookupProduct(lastScanned);
                }
            });
        } catch (e) {
            console.error('Scanner Error:', e);
            if (e.name === 'NotAllowedError') {
                showToast('Camera access denied by user', 'error');
            } else if (e.name === 'NotFoundError') {
                showToast('No camera hardware found', 'error');
            } else {
                showToast('Camera access denied or unavailable', 'error');
            }
            stopScanner();
        }
    }

    // ── Stop Scanner ──
    function stopScanner() {
        if (codeReader) { codeReader.reset(); codeReader = null; }
        scanning = false;
        preview.style.display = 'none';
        placeholder.style.display = 'flex';
        scanOverlay.style.display = 'none';
        btnScan.innerHTML = `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9V5a2 2 0 0 1 2-2h4M15 3h4a2 2 0 0 1 2 2v4M21 15v4a2 2 0 0 1-2 2h-4M9 21H5a2 2 0 0 1-2-2v-4"/><line x1="8" y1="12" x2="16" y2="12"/></svg>`;
    }

    btnScan.addEventListener('click', () => { scanning ? stopScanner() : startScanner(); });

    // ── Torch ──
    btnTorch.addEventListener('click', async () => {
        if (!preview.srcObject) { showToast('Start scanner first', 'error'); return; }
        const track = preview.srcObject.getVideoTracks()[0];
        try {
            torchEnabled = !torchEnabled;
            await track.applyConstraints({ advanced: [{ torch: torchEnabled }] });
            btnTorch.style.color = torchEnabled ? '#fbbf24' : '';
        } catch {
            showToast('Torch not supported on this device');
        }
    });

    // ── Manual input toggle ──
    btnManual.addEventListener('click', () => {
        manualPanel.classList.toggle('show');
        if (manualPanel.classList.contains('show')) manualSku.focus();
    });
    btnManualSearch.addEventListener('click', () => lookupProduct(manualSku.value.trim()));
    manualSku.addEventListener('keydown', e => { if (e.key === 'Enter') lookupProduct(manualSku.value.trim()); });

    // ── Product Lookup via API ──
    function lookupProduct(sku) {
        if (!sku) return;
        showToast('Looking up product...', '');
        fetch(API_URL + '?sku=' + encodeURIComponent(sku), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                displayResult(data.product);
                if (scanning) stopScanner();
            } else {
                showToast(data.msg || 'Product not found', 'error');
                lastScanned = null;
            }
        })
        .catch(() => showToast('Network error', 'error'));
    }

    // ── Display Product Result ──
    function displayResult(p) {
        document.getElementById('r-name').textContent = p.name;
        document.getElementById('r-sku').textContent = 'SKU: ' + p.sku;
        document.getElementById('r-category').textContent = p.category || '—';
        document.getElementById('r-price').textContent = p.price;
        document.getElementById('r-unit').textContent = p.unit || '—';

        const stockEl = document.getElementById('r-stock');
        stockEl.textContent = p.stock;
        stockEl.className = 'result-card-value ' + (p.stock > 10 ? 'stock-ok' : p.stock > 0 ? 'stock-low' : 'stock-out');

        btnViewProduct.onclick = () => { window.location.href = p.url; };
        resultPanel.classList.add('show');
    }

    btnScanAgain.addEventListener('click', () => {
        resultPanel.classList.remove('show');
        lastScanned = null;
        startScanner();
    });
</script>
</body>
</html>
