<html lang="en">
<head>
    <title>Dungeon</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fontdiner+Swanky&display=swap" rel="stylesheet">

    <link rel="apple-touch-icon" sizes="180x180" href="/dungeon/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/dungeon/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/dungeon/favicon/favicon-16x16.png">
    <link rel="manifest" href="/dungeon/favicon/site.webmanifest">

    <x-vite-tags entrypoint="app/Dungeon/Http/dungeon.entrypoint.css"/>

    <style>
        :root {
            --title-font: var(--font-title);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, sans-serif;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background-color: #374151;
        }

        .viewport {
            position: relative;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            cursor: grab;
            user-select: none;
        }

        .viewport.is-dragging {
            cursor: grabbing;
        }

        .viewport.is-interact-mode {
            cursor: default;
        }

        .viewport.is-interact-mode.has-hovered-tile {
            cursor: pointer;
        }

        canvas {
            display: block;
            background: oklch(20% 0 0);
        }

        .artifact-compass {
            position: fixed;
            width: 30px;
            height: 30px;
            border-radius: 999px;
            background: radial-gradient(circle, #f5d0fe 0%, #c084fc 28%, #9333ea 62%, #6b21a8 100%);
            box-shadow: 0 0 20px rgba(192, 132, 252, 1), 0 0 42px rgba(147, 51, 234, 0.92), 0 0 70px rgba(107, 33, 168, 0.72);
            z-index: 2000;
            pointer-events: none;
            display: none;
            transform: translate(-50%, -50%);
        }

        .damage-flash {
            position: fixed;
            inset: 0;
            z-index: 3500;
            pointer-events: none;
            background: rgba(220, 38, 38, 0.22);
            opacity: 0;
        }

        .exit-dungeon-button {
            position: fixed;
            z-index: 2100;
            display: none;
            transform: translateX(-50%);
            padding: 8px 18px;
            border-radius: 10px;
            border: 2px solid rgba(217, 119, 6, 0.65);
            background: rgba(120, 53, 15, 0.92);
            color: #fef3c7;
            font: 700 11px/1 var(--font-title);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            cursor: pointer;
            pointer-events: auto;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .exit-dungeon-button:hover {
            background: rgba(146, 64, 14, 0.95);
            border-color: rgba(245, 158, 11, 0.8);
        }

        .exit-dungeon-button:disabled {
            opacity: 0.55;
            cursor: default;
        }

        .exit-dungeon-button.is-confirming {
            background: rgba(127, 29, 29, 0.92);
            border-color: rgba(239, 68, 68, 0.7);
            color: #fecaca;
        }

        .exit-dungeon-button.is-confirming:hover {
            background: rgba(153, 27, 27, 0.95);
            border-color: rgba(248, 113, 113, 0.85);
        }

        #dungeon-message {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 3000;
            background: rgba(15, 15, 20, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            padding: 10px 20px;
            color: #e2e8f0;
            font: 13px/1.4 var(--font-title);
            letter-spacing: 0.04em;
            text-align: center;
            max-width: min(360px, calc(100vw - 48px));
            backdrop-filter: blur(6px);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        #dungeon-message.is-visible {
            opacity: 1;
        }

        .death-overlay {
            position: fixed;
            inset: 0;
            z-index: 4000;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(69, 10, 10, 0.52);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #fee2e2;
            pointer-events: auto;
        }

        .death-overlay-message {
            font-family: var(--font-title);
            font-size: clamp(34px, 7vw, 68px);
            line-height: 1;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            text-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
        }

        .overlay-coins {
            font: 700 16px/1.2 var(--font-title);
            letter-spacing: 0.03em;
            text-transform: uppercase;
            opacity: 0.95;
        }

        .death-overlay-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }

        .death-overlay-exit {
            border: 2px solid rgba(217, 119, 6, 0.65);
            background: rgba(120, 53, 15, 0.88);
            color: #fef3c7;
            border-radius: 10px;
            padding: 10px 22px;
            font: 700 13px/1 var(--font-title);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .death-overlay-exit:hover {
            background: rgba(146, 64, 14, 0.95);
            border-color: rgba(245, 158, 11, 0.8);
        }

        .exited-overlay {
            position: fixed;
            inset: 0;
            z-index: 4000;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(20, 83, 45, 0.52);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #dcfce7;
            pointer-events: auto;
        }

        .resigned-overlay {
            position: fixed;
            inset: 0;
            z-index: 4000;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #cbd5e1;
            pointer-events: auto;
        }

        .resign-button {
            flex-shrink: 0;
            align-self: center;
            margin-left: 16px;
            padding: 8px 18px;
            border-radius: 10px;
            border: 2px solid rgba(217, 119, 6, 0.65);
            background: rgba(120, 53, 15, 0.92);
            color: #fef3c7;
            font: 700 11px/1 var(--font-title);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            cursor: pointer;
            pointer-events: auto;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .resign-button:hover {
            background: rgba(146, 64, 14, 0.95);
            border-color: rgba(245, 158, 11, 0.8);
        }

        .resign-button.is-confirming {
            background: rgba(127, 29, 29, 0.92);
            border-color: rgba(239, 68, 68, 0.7);
            color: #fecaca;
        }

        .resign-button.is-confirming:hover {
            background: rgba(153, 27, 27, 0.95);
            border-color: rgba(248, 113, 113, 0.85);
        }

        .debug-popup {
            display: none;
            position: fixed;
            right: 12px;
            bottom: 12px;
            z-index: 1000;
            width: min(520px, calc(100vw - 24px));
            max-height: calc(100vh - 24px);
            overflow: auto;
            padding: 10px 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            background: rgba(12, 13, 18, 0.5);
            color: #e5e7eb;
            font: 12px/1.4 ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            white-space: pre-wrap;
            pointer-events: auto;
        }

        .debug-header {
            margin-bottom: 8px;
            font-weight: 700;
        }

        .debug-change {
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 8px;
            margin-bottom: 6px;
            overflow: hidden;
        }

        .debug-change-toggle {
            width: 100%;
            border: 0;
            margin: 0;
            padding: 7px 9px;
            text-align: left;
            background: rgba(255, 255, 255, 0.06);
            color: inherit;
            font: inherit;
            cursor: pointer;
        }

        .debug-change-toggle:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .debug-change-payload {
            display: none;
            margin: 0;
            padding: 8px 9px;
            background: rgba(0, 0, 0, 0.28);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .debug-change.is-open .debug-change-payload {
            display: block;
        }

        .bottom-notch {
            position: fixed;
            left: 50%;
            top: 0;
            transform: translateX(-50%);
            z-index: 900;
            min-width: 620px;
            padding: 12px 24px 12px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-top: none;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
            background: rgba(9, 10, 15, 0.90);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: #e5e7eb;
            font-family: ui-sans-serif, system-ui, sans-serif;
            pointer-events: none;
            display: flex;
            gap: 0;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
        }

        .bottom-notch-stat {
            min-width: 90px;
            padding: 0 16px;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .bottom-notch-stat:last-child {
            border-right: none;
        }

        .bottom-notch-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(156, 163, 175, 0.75);
            font-family: var(--font-title);
        }

        .bottom-notch-value {
            margin-top: 3px;
            font-size: 18px;
            line-height: 1;
            font-weight: 700;
        }

        #coin-counter,
        #shard-counter,
        #victory-point-counter {
            text-align: right;
        }

        #coin-label,
        #shard-label,
        #victory-point-label {
            text-align: right;
        }

        @keyframes stat-pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.45; }
        }

        .stat-critical {
            animation: stat-pulse 1.1s ease-in-out infinite;
        }

        .bottom-notch-stat {
            --stat-color: #e5e7eb;
        }

        .bottom-notch-stat .bottom-notch-value {
            color: var(--stat-color);
        }

        .bottom-notch-stat .bottom-notch-label {
            color: color-mix(in srgb, var(--stat-color) 60%, rgba(156, 163, 175, 0.75));
        }

        .bottom-notch-max {
            margin-left: 6px;
            font-size: 12px;
            line-height: 1;
            font-weight: 500;
            opacity: 0.72;
            vertical-align: baseline;
        }

        .hand-notch {
            position: fixed;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            z-index: 900;
            width: auto;
            border: none;
            padding: 8px 12px 16px;
            background: transparent;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            color: #e5e7eb;
            font-family: ui-sans-serif, system-ui, sans-serif;
            pointer-events: auto;
            box-shadow: none;
        }

        .hand-notch.is-collapsed .hand-layout {
            display: block;
        }

        .toggle-hand-button {
            display: none;
            align-items: center;
            justify-content: center;
            margin: 0 auto 4px;
            padding: 9px 32px;
            border-radius: 10px;
            border: 2px solid rgba(217, 119, 6, 0.65);
            background: rgba(120, 53, 15, 0.92);
            color: #fef3c7;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease;
            line-height: 1;
        }

        .toggle-hand-button:hover {
            background: rgba(146, 64, 14, 0.95);
            border-color: rgba(245, 158, 11, 0.8);
        }

        .toggle-hand-button svg {
            width: 18px;
            height: 18px;
            transition: transform 0.2s ease;
        }

        .hand-notch.is-collapsed .toggle-hand-button svg {
            transform: rotate(180deg);
        }

        .hand-layout {
            display: block;
            position: relative;
            width: 100%;
        }

        .deck-counter {
            flex-shrink: 0;
            align-self: center;
            padding: 0 20px;
            text-align: center;
            font: 700 22px/1 var(--font-title);
            color: rgba(229, 231, 235, 0.7);
            letter-spacing: 0.02em;
        }

        .deck-counter::after {
            display: block;
            content: 'cards remaining';
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(156, 163, 175, 0.6);
            margin-top: 3px;
            font-family: var(--font-title);
        }

        @media (max-width: 1600px) {
            .bottom-notch {
                min-width: unset;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-left: none;
                border-right: none;
                border-radius: 0;
                flex-wrap: nowrap;
                justify-content: safe center;
                transform: none;
                left: 0;
                pointer-events: auto;
            }

            .bottom-notch-stat {
                flex-shrink: 0;
                min-width: 70px;
                padding: 0 10px;
            }

            .bottom-notch-value {
                font-size: 15px;
            }
        }

        @media (pointer: coarse) {
            .death-overlay-content {
                text-align: center;
                padding: 0 24px;
            }

            .bottom-notch {
                min-width: unset;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-left: none;
                border-right: none;
                border-radius: 0;
                flex-wrap: nowrap;
                justify-content: safe center;
                transform: none;
                left: 0;
                pointer-events: auto;
            }

            .bottom-notch-stat {
                flex-shrink: 0;
                min-width: 70px;
                padding: 0 10px;
            }

            .bottom-notch-value {
                font-size: 15px;
            }

            .hand-notch {
                left: 0;
                transform: none;
                width: 100vw;
                border-top: 1px solid rgba(255, 255, 255, 0.08);
                border-top-left-radius: 16px;
                border-top-right-radius: 16px;
                background: rgba(9, 10, 15, 0.88);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.35);
                padding: 12px 12px 10px;
                max-height: 52vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            .hand-notch.is-collapsed {
                padding-bottom: 10px;
            }

            .hand-notch.is-collapsed .hand-layout {
                display: none;
            }

            .toggle-hand-button {
                display: flex;
                margin-bottom: 10px;
            }

            .hand-notch.is-collapsed .toggle-hand-button {
                margin-bottom: 0;
            }

        }
    </style>
</head>

<body>
    <div id="viewport" class="viewport">
        <canvas id="dungeon-canvas"></canvas>
    </div>
    <div id="damage-flash" class="damage-flash"></div>
    <div id="artifact-compass" class="artifact-compass"></div>
    <button id="exit-dungeon-button" class="exit-dungeon-button" type="button">Exit</button>
    <div id="death-overlay" class="death-overlay">
        <div class="death-overlay-content">
            <div class="death-overlay-message">You Died</div>
            <button id="death-overlay-exit-button" class="death-overlay-exit" type="button">Exit</button>
        </div>
    </div>
    <div id="exited-overlay" class="exited-overlay">
        <div class="death-overlay-content">
            <div class="death-overlay-message">You've made it out!</div>
            <div class="overlay-coins">Coins: <span id="exited-overlay-coins">0</span></div>
            <button id="exited-overlay-exit-button" class="death-overlay-exit" type="button">Exit</button>
        </div>
    </div>
    <div id="resigned-overlay" class="resigned-overlay">
        <div class="death-overlay-content">
            <div class="death-overlay-message">You Resigned</div>
            <button id="resigned-overlay-exit-button" class="death-overlay-exit" type="button">Exit</button>
        </div>
    </div>
    <div class="bottom-notch">
        <div class="bottom-notch-stat">
            <div id="health-label" class="bottom-notch-label" style="display:flex;align-items:center;gap:3px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M23 6v5h-1v1h-1v1h-1v1h-1v1h-1v1h-1v1h-1v1h-1v1h-1v1h-1v1h-2v-1h-1v-1H9v-1H8v-1H7v-1H6v-1H5v-1H4v-1H3v-1H2v-1H1V6h1V5h1V4h1V3h6v1h1v1h2V4h1V3h6v1h1v1h1v1z"/></svg>
                Health
            </div>
            <div id="health-counter" class="bottom-notch-value">0</div>
        </div>
        <div class="bottom-notch-stat">
            <div class="bottom-notch-label" style="display:flex;align-items:center;gap:3px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M23 18v2h-2v1h-1v2h-2v-2h-1v-1h-2v-2h2v-1h1v-2h2v2h1v1zm0-14v2h-2v1h-1v2h-2V7h-1V6h-2V4h2V3h1V1h2v2h1v1zm-6 7v2h-2v1h-2v1h-1v1h-1v2h-1v2H8v-2H7v-2H6v-1H5v-1H3v-1H1v-2h2v-1h2V9h1V8h1V6h1V4h2v2h1v2h1v1h1v1h2v1z"/></svg>
                Mana
            </div>
            <div id="mana-counter" class="bottom-notch-value">0</div>
        </div>
        <div class="bottom-notch-stat">
            <div class="bottom-notch-label" style="display:flex;align-items:center;gap:3px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M23.505 17.503v2h-1v1.001h-6.002v1h-1v1h-1v1h-4.001v-1h-1v-1h-1v-1H2.5v-1h-1v-2zm0-2v1h-2v-2h1v1zm-3.001-7.001v8.001h-1v-4h-1v-1h-3v-1.001h3v-1h-3v-2h-1.001v-1h-1V5.5h-1v-2h1v-2h1v1h1v1h1v1h1v3.001h2.001v1zm-14.003 4v4.001H1.5v-1h1v-2h1v-1z"/><path fill="currentColor" d="M18.504 12.503v4H7.5v-4h-1v-1h-2V4.5h1v-3h3v14.003h1V3.5h1.001v1h1v1h1v1.001h1v1h1.001v8.002h1v-3z"/></svg>
                Stability
            </div>
            <div id="stability-counter" class="bottom-notch-value">0</div>
        </div>
        <div class="bottom-notch-stat" style="display:none">
            <div id="coin-label" class="bottom-notch-label">Coins</div>
            <div id="coin-counter" class="bottom-notch-value">0</div>
        </div>
        <div class="bottom-notch-stat" style="display:none">
            <div id="shard-label" class="bottom-notch-label">Shards</div>
            <div id="shard-counter" class="bottom-notch-value">0</div>
        </div>
        <div class="bottom-notch-stat" style="display:none">
            <div id="victory-point-label" class="bottom-notch-label">Victory Points</div>
            <div id="victory-point-counter" class="bottom-notch-value">0</div>
        </div>
        <button id="resign-button" class="resign-button" type="button">Resign</button>
    </div>
    <div id="dungeon-message" aria-live="polite"></div>
    <div class="card-slots-corner">
        <div class="hand-side-slots">
            <div class="hand-side-slot">
                <div id="active-card-slot"></div>
            </div>
            <div class="hand-side-slot">
                <div id="passive-card-slot"></div>
            </div>
        </div>
    </div>
    <div id="hand-notch" class="hand-notch">
        <button id="toggle-hand-button" class="toggle-hand-button" type="button" aria-label="Toggle hand">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 15l-6-6-6 6"/>
            </svg>
        </button>
        <div class="hand-layout">
            <div id="hand-cards" class="hand-cards">
                <div id="deck-counter" class="deck-counter"></div>
            </div>
        </div>
    </div>
    <pre id="debug-popup" class="debug-popup"></pre>

    <script id="dungeon-data" type="application/json">{!! json_encode($dungeon->toArray()) !!}</script>
    <script>
        const dataElement = document.getElementById('dungeon-data');
        const viewport = document.getElementById('viewport');
        const canvas = document.getElementById('dungeon-canvas');
        const damageFlash = document.getElementById('damage-flash');
        const titleFontFamily = getComputedStyle(document.documentElement).getPropertyValue('--title-font').trim() || '"Fontdiner Swanky", serif';
        const artifactCompass = document.getElementById('artifact-compass');
        const exitDungeonButton = document.getElementById('exit-dungeon-button');
        const deathOverlay = document.getElementById('death-overlay');
        const deathOverlayExitButton = document.getElementById('death-overlay-exit-button');
        const exitedOverlay = document.getElementById('exited-overlay');
        const exitedOverlayExitButton = document.getElementById('exited-overlay-exit-button');
        const exitedOverlayCoins = document.getElementById('exited-overlay-coins');
        const resignedOverlay = document.getElementById('resigned-overlay');
        const resignedOverlayExitButton = document.getElementById('resigned-overlay-exit-button');
        const resignButton = document.getElementById('resign-button');
        const handNotch = document.getElementById('hand-notch');
        const toggleHandButton = document.getElementById('toggle-hand-button');

        function isMobile() {
            return window.matchMedia('(pointer: coarse)').matches;
        }
        const debugPopup = document.getElementById('debug-popup');
        const handCards = document.getElementById('hand-cards');
        const deckCounter = document.getElementById('deck-counter');
        const activeCardSlot = document.getElementById('active-card-slot');
        const passiveCardSlot = document.getElementById('passive-card-slot');
        const dungeonMessage = document.getElementById('dungeon-message');
        const statsNotch = document.querySelector('.bottom-notch');
        let messageHideTimeout = null;

        function positionDungeonMessage() {
            if (!dungeonMessage || !statsNotch) return;
            const bottom = statsNotch.getBoundingClientRect().bottom;
            dungeonMessage.style.top = (bottom + 10) + 'px';
        }

        positionDungeonMessage();
        window.addEventListener('resize', positionDungeonMessage);
        const counters = {
            coins: document.getElementById('coin-counter'),
            shards: document.getElementById('shard-counter'),
            victoryPoints: document.getElementById('victory-point-counter'),
            health: document.getElementById('health-counter'),
            mana: document.getElementById('mana-counter'),
            stability: document.getElementById('stability-counter'),
        };
        const context = canvas.getContext('2d');

        const payload = JSON.parse(dataElement.textContent);
        const tiles = [];
        const tileIndex = new Map();
        const dwellers = [];
        const dwellerIndex = new Map();
        const hand = new Map();
        let deckSize = 0;
        let activeCard = null;
        let passiveCard = null;
        let playerPosition = null;
        let artifactLocation = null;
        let visibilityRadius = null;
        let dungeonVersion = null;
        let isPlayerDead = false;
        let hasPlayerExited = false;
        let hasPlayerMoved = false;
        let hasPlayerResigned = false;
        let exitedCoinsAmount = null;
        const stats = {
            coins: 0,
            shards: 0,
            victoryPoints: 0,
            health: 0,
            maxHealth: 0,
            mana: 0,
            maxMana: 0,
            stability: 0,
            maxStability: 0,
        };
        let latestChanges = [];
        const wallSpritePaths = {
            top: '/dungeon/wall-top.png',
            right: '/dungeon/wall-right.png',
            bottom: '/dungeon/wall-bottom.png',
            left: '/dungeon/wall-left.png',
        };
        const floorSpritePath = '/dungeon/tile-floor.png';
        const floorOriginSpritePath = '/dungeon/tile-floor-origin.png';
        const floorSupportSpritePath = '/dungeon/tile-floor-support.png';
        const floorHealthAltarSpritePath = '/dungeon/tile-floor-health.png';
        const floorManaAltarSpritePath = '/dungeon/tile-floor-mana.png';
        const floorStabilityAltarSpritePath = '/dungeon/tile-floor-stability.png';
        const floorHealthAltarCooldownSpritePath = '/dungeon/tile-floor-health-cooldown.png';
        const floorManaAltarCooldownSpritePath = '/dungeon/tile-floor-mana-cooldown.png';
        const floorStabilityAltarCooldownSpritePath = '/dungeon/tile-floor-stability-cooldown.png';
        const floorVictoryPointSpritePath = '/dungeon/tile-victory-point.png';
        const floorShardSpritePath = '/dungeon/tile-shard.png';
        const floorCollapsedSpritePath = '/dungeon/tile-collapsed.png';
        const floorLakeSpritePath = '/dungeon/tile-floor-lake.png';
        const floorLake1SpritePath = '/dungeon/tile-lake-1.png';
        const floorLake2SpritePath = '/dungeon/tile-lake-2.png';
        const floorLake3SpritePath = '/dungeon/tile-lake-3.png';
        const playerSpritePath = '/dungeon/player-avatar.png';
        const dwellerSpritePath = '/dungeon/dweller-avater.png';
        const dwellerFallbackSpritePath = '/dungeon/dweller-avatar.png';
        const coinMarkerSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512"><path fill="#facc15" d="M256 23.05C127.5 23.05 23.05 127.5 23.05 256S127.5 488.9 256 488.9S488.9 384.5 488.9 256S384.5 23.05 256 23.05"/></svg>`;
        const artifactMarkerSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512" style="color:#a855f7"><path fill="currentColor" d="M54.6 25.88L41.4 38.12l62.5 67.28c3.8-6.87 6.5-11.92 9.1-16.72zm386.6.26l-46.6 54.33c2.4 4.36 5.4 9.94 9.2 17.01l51-59.62zm-272.9 2.32l-16.6 7.08l42.1 98.26c6.4 2.1 13.6 3.8 21.8 5.2zm143 1.07l-32 111.57c7.2-.6 13.8-1.5 19.8-2.6l29.6-104.03zM263 47.31L255.7 142h.3c6.3 0 12.2-.2 17.8-.5l7.2-92.81zM129.3 96.47c-6.2 11.73-15.1 28.33-31.76 57.13c-16.63 28.8-26.56 44.8-33.56 56.1c11.59-2.6 27.23-3.5 44.92 3.5c22.8 9 48 30.5 73.4 74.4c25.4 44 31.3 76.6 27.7 100.8c-2.7 18.9-11.4 32-19.4 40.7c13.3-.4 32.1-1 65.4-1c33.2 0 52 .6 65.3 1c-8-8.7-16.6-21.8-19.4-40.7c-3.6-24.2 2.4-56.8 27.8-100.8c25.4-43.9 50.6-65.3 73.4-74.4c17.7-7 33.4-6.1 44.9-3.5c-7-11.3-16.9-27.3-33.5-56.1s-25.5-45.4-31.8-57.08c-3.6 11.28-10.6 25.38-25.6 37.18C338 148.9 306.8 160 256 160s-82-11.2-101.1-26.3c-15-11.9-22-25.9-25.6-37.23m313.5 8.13l-25.3 17.9c2.7 4.8 5.7 10.1 9 15.8l26.7-18.9zM35.03 167.5l-6.06 17l24 8.6c2.77-4.5 6-9.8 9.63-15.8zM256 196a49.98 49.98 0 0 1 50 50a49.98 49.98 0 0 1-50 50a49.98 49.98 0 0 1-50-50a49.98 49.98 0 0 1 50-50m118.9 59.4c-4.6 4.9-9.3 10.6-14.1 17.2l118.6 8.4l1.2-18zm-231.2 7.5L30.73 279.1l2.54 17.8L156 279.4c-4.1-6.2-8.2-11.6-12.3-16.5m18.7 26.4L44.23 343.8l7.54 16.4L171.4 305c-1.5-2.7-3-5.5-4.7-8.4c-1.5-2.5-2.9-5-4.3-7.3m181.3 10.1c-3.1 5.6-5.9 10.9-8.4 16l124 76.3l9.4-15.4zm-166.4 17.3L25.88 457.4l12.24 13.2L184.8 334.4q-3-8.4-7.5-17.7m148.5 21.6q-4.2 12.45-5.7 22.8l88.6 124.1l14.6-10.4zM224.4 446.4c-7 .1-13 .2-18.5.4l-6.7 31.3l17.6 3.8zm77.1.2l9.8 35.8l17.4-4.8l-8.4-30.4c-5.4-.2-11.1-.4-18.8-.6"/></svg>`;
        const trapMarkerPathData = 'M19.188 17.406L74.624 68.72l4.938 2.155L119 88.03l48.844-70.624zm171.406 0L136.5 95.626l38.063 16.53l1.875.813l1.343 1.5l62.157 69.218l25.532 25.968l8-25.53l-.095-.032L303.97 82.72l-19.626-65.314zm113.28 0l17.407 58.063l105.345-21.5l-7.313-36.564H303.875zm134.5 0l8.407 42.032l1.814 9.124l-9.125 1.844l-119.626 24.406l-16.47 54.626l47.72 56.062l142.625-95.938V17.406zM18.907 42.594v168.03l69.03-26.03L68 87.75l-.156.188l-48.938-45.344zm69.72 52.593l18.468 89.626l51.22 19.312l35.624-43.656l-28.72-32l-76.593-33.282zm405.093 36.876L337.53 237.188l-13.717 10.53l169.906 105.845v-221.5zm-396.533 69l-78.28 29.53v166.938l181.5-103.874l47.124-33.344l-37.843-16.968l-.03.094l-57.47-21.657l-4.03-1.155l-2.282-1.22l-48.688-18.343zm242.875 78.78l-5.406 5.782l-44.5 47.625l-4.844 57.313l68.813 92.562L493.72 423v-47.406zm-151.375 42.094l-65.593 37.532L224.28 492.22h113.314l-69.375-93.376l-2.095-2.813l.28-3.53l4.033-47.72l-81.75-22.842zm-81.968 46.875l-87.814 50.25v73.157H200.78L106.72 368.81zm387 74.532L380.25 492.22h113.47v-48.876z';
        const trapMarkerPath = typeof Path2D === 'function' ? new Path2D(trapMarkerPathData) : null;
        const relicMarkerPathData = 'M194.9 21.001c-14.29 0-25.85 10.966-25.85 24.528v46.265c0 7.112 3.284 13.44 8.372 17.92c23.465-12.065 50.224-18.948 78.577-18.948s55.113 6.883 78.578 18.947c5.087-4.478 8.372-10.807 8.372-17.919V45.53c0-13.562-11.56-24.528-25.85-24.528zm-35.838 28.347c-23.533 10.379-45.12 24.461-63.743 41.272l45.53 44.356c5.685-5.173 11.84-9.872 18.213-14.247zm194.021 0v71.38c.084.062.212.087.291.148c3.074 2.121 6.043 4.443 8.96 6.756c.386.304.794.574 1.175.88c2.602 2.1 5.17 4.218 7.638 6.463l45.53-44.356c-18.588-16.778-40.096-30.907-63.596-41.271zM86.36 98.99c-11.365 11.4-21.4 23.985-30.11 37.453l56.4 31.872c1.035-1.607 2.144-3.13 3.232-4.7c.404-.582.765-1.186 1.175-1.762c1.134-1.593 2.336-3.15 3.525-4.7c.404-.526.766-1.095 1.175-1.616c.711-.906 1.471-1.753 2.203-2.644c.92-1.127 1.84-2.275 2.79-3.378c1.354-1.565 2.703-3.187 4.113-4.7c.376-.406.793-.772 1.175-1.175zm339.28 0l-45.678 44.65c.38.403.797.769 1.175 1.175c1.413 1.516 2.759 3.136 4.113 4.7c.385.447.794.872 1.175 1.322a164 164 0 0 1 3.818 4.7c.409.521.773 1.09 1.175 1.616c1.188 1.549 2.392 3.108 3.525 4.7c.409.576.773 1.18 1.175 1.762c1.088 1.57 2.197 3.088 3.232 4.7l56.546-31.872c-8.717-13.504-18.87-26.027-30.256-37.453m-173.018 30.55c-9.008.215-17.799 1.395-26.29 3.231l11.75 43.035c4.653-1.012 9.617-1.566 14.54-1.763zm6.903 0v44.503c4.921.197 9.74.75 14.394 1.763l11.75-43.035c-8.492-1.836-17.135-3.016-26.144-3.23m-39.656 4.847c-8.636 2.311-16.959 5.27-24.822 9.106l23.06 39.51c4.275-2.191 8.833-3.86 13.512-5.288zm72.262 0l-11.75 43.328c4.68 1.429 9.238 3.097 13.513 5.288l23.206-39.51c-7.863-3.835-16.334-6.795-24.969-9.106m-103.106 12.338c-7.858 4.328-15.233 9.275-22.03 14.98L200.92 194.9c3.555-3.195 7.509-6.123 11.603-8.665zm134.097 0l-23.5 39.509c4.094 2.542 8.048 5.47 11.603 8.665l33.781-33.193a135.5 135.5 0 0 0-21.884-14.981m-273.186.146c-7.943 13.862-14.436 28.63-19.241 44.063L94.29 207.53c.08-.25.207-.484.296-.734c3.112-9.728 6.931-19.195 11.75-28.053l-56.4-31.872zm412.129 0l-56.4 31.872c4.804 8.858 8.65 18.325 11.75 28.053c.08.25.211.485.296.734l63.596-16.596c-4.794-15.433-11.313-30.201-19.24-44.063zM161.706 166.26c-12.23 11.365-22.367 25.003-29.669 40.096c19.337-1.527 39.752-2.582 60.953-3.378c.973-1.17 2.045-2.272 3.084-3.378zm188.587 0L316.07 199.6c1.039 1.106 2.112 2.207 3.084 3.378c21.19.795 41.482 1.85 60.806 3.378c-7.302-15.094-17.439-28.73-29.669-40.096m-322.83 36.13c-3.866 15.332-6.03 31.213-6.463 47.588h65.947c.132-3.651.493-7.296.88-10.869c.174-1.636.372-3.225.588-4.846c.05-.2.118-.388.146-.588c.216-1.564.329-3.15.587-4.7c.371-2.135.869-4.204 1.322-6.315c.25-1.192.46-2.343.734-3.525zm457.073 0l-63.744 16.745c.278 1.182.487 2.333.734 3.525c.452 2.11.95 4.18 1.323 6.315c.108.628.201 1.28.29 1.91c.184 1.124.282 2.246.442 3.378c.217 1.621.414 3.21.588 4.846c.39 3.573.75 7.218.88 10.87h65.947c-.432-16.376-2.605-32.257-6.462-47.588zm-228.537 1.763c-28.617 0-51.847 23.23-51.847 51.847s23.23 51.847 51.847 51.847s51.847-23.23 51.847-51.847s-23.23-51.847-51.847-51.847m-102.372 22.178c-16.352 0-29.668 13.316-29.668 29.669s13.316 29.668 29.668 29.668s29.67-13.316 29.67-29.668s-13.317-29.669-29.67-29.669m204.744 0c-16.353 0-29.522 13.316-29.522 29.669s13.17 29.668 29.522 29.668s29.668-13.316 29.668-29.668s-13.316-29.669-29.668-29.669M21 262.021c.45 16.327 2.597 32.14 6.462 47.441l63.743-16.597c-.277-1.173-.483-2.343-.734-3.525c-.587-2.734-1.165-5.448-1.616-8.225a154 154 0 0 1-1.909-19.093zm404.05 0c-.131 3.652-.489 7.296-.88 10.87a186 186 0 0 1-.588 4.846c-.155 1.132-.263 2.254-.442 3.378c-.103.638-.183 1.274-.29 1.91c-.372 2.135-.87 4.205-1.323 6.315c-.25 1.182-.46 2.352-.734 3.525l63.744 16.597q5.783-22.951 6.462-47.44h-65.947zM94.29 304.468l-63.45 16.597c4.808 15.377 11.18 30.092 19.094 43.916l56.546-31.725c-.98-1.799-1.877-3.743-2.79-5.581c-.24-.49-.499-.978-.735-1.47c-.937-1.939-1.783-3.894-2.644-5.874c-.173-.4-.418-.774-.587-1.175c-.888-2.091-1.692-4.182-2.497-6.316a168 168 0 0 1-2.643-7.637c-.08-.247-.212-.488-.297-.735zm323.417 0c-.08.247-.212.488-.296.735a162 162 0 0 1-2.643 7.637c-.805 2.134-1.608 4.225-2.497 6.316c-.17.401-.414.775-.588 1.175c-.862 1.98-1.706 3.935-2.643 5.875c-.235.492-.496.98-.735 1.469c-.913 1.838-1.813 3.782-2.79 5.58l56.546 31.726c7.897-13.824 14.296-28.54 19.094-43.916l-63.45-16.596zm-285.818 1.029c7.314 15.212 17.506 28.95 29.816 40.39l34.369-33.34a75 75 0 0 1-3.085-3.525c-21.258-.798-41.715-1.992-61.1-3.525m248.218 0c-19.373 1.533-39.706 2.727-60.953 3.525a75 75 0 0 1-3.084 3.525l34.222 33.34c12.31-11.44 22.502-25.178 29.815-40.39m-179.187 11.75l-33.928 33.193c6.798 5.706 14.173 10.653 22.031 14.981l23.5-39.509c-4.094-2.542-8.048-5.47-11.603-8.665m110.303 0c-3.555 3.195-7.51 6.123-11.603 8.665l23.5 39.51a135.5 135.5 0 0 0 21.884-14.982zm-93.118 11.896l-23.06 39.51c7.864 3.835 16.186 6.795 24.822 9.106l11.75-43.328c-4.679-1.429-9.237-3.097-13.512-5.288m75.787 0c-4.275 2.191-8.834 3.86-13.513 5.288l11.75 43.328c8.636-2.311 17.106-5.271 24.969-9.106zm-55.812 7.197l-11.75 43.035c8.491 1.836 17.282 3.016 26.29 3.23v-44.502c-4.923-.197-9.887-.75-14.54-1.763m35.837 0c-4.654 1.012-9.472 1.566-14.394 1.763v44.503c9.009-.215 17.652-1.395 26.144-3.231zm-161.268 7.344l-56.4 31.872c8.698 13.463 18.915 25.906 30.256 37.306l45.531-44.503c-4.883-5.15-9.325-10.723-13.512-16.45c-.881-1.181-1.797-2.32-2.644-3.525c-1.096-1.561-2.197-3.098-3.231-4.7m286.699 0c-1.036 1.608-2.143 3.13-3.232 4.7c-.404.583-.763 1.185-1.174 1.763c-1.137 1.6-2.333 3.14-3.526 4.7c-.404.527-.764 1.093-1.175 1.615a168 168 0 0 1-3.818 4.7c-.38.452-.788.874-1.175 1.322c-1.36 1.576-2.695 3.175-4.113 4.7c-.376.402-.795.776-1.175 1.175l45.678 44.65c11.348-11.407 21.413-23.978 30.11-37.453zm-258.5 33.34l-45.53 44.357c18.62 16.837 40.218 30.896 63.743 41.271v-71.234a170 170 0 0 1-18.212-14.393m230.3 0c-2.468 2.246-5.036 4.363-7.638 6.463c-.38.306-.793.578-1.175.881c-3 2.38-6.089 4.726-9.253 6.903v71.381c23.491-10.361 45.012-24.465 63.597-41.271zm-193.727 25.263c-5.122 4.48-8.372 10.747-8.372 17.919v46.265c0 13.562 11.56 24.528 25.85 24.528h122.199c14.29 0 25.85-10.966 25.85-24.528v-46.265c0-7.185-3.343-13.473-8.519-17.92c-23.44 12.028-50.124 18.948-78.43 18.948c-28.355 0-55.114-6.883-78.579-18.947z';
        const relicMarkerPath = typeof Path2D === 'function' ? new Path2D(relicMarkerPathData) : null;
        const coinMarkerSpritePath = `data:image/svg+xml;utf8,${encodeURIComponent(coinMarkerSvg)}`;
        const artifactMarkerSpritePath = `data:image/svg+xml;utf8,${encodeURIComponent(artifactMarkerSvg)}`;
        const wallSprites = {};
        let floorSprite = null;
        let floorOriginSprite = null;
        let floorSupportSprite = null;
        let floorHealthAltarSprite = null;
        let floorManaAltarSprite = null;
        let floorStabilityAltarSprite = null;
        let floorHealthAltarCooldownSprite = null;
        let floorManaAltarCooldownSprite = null;
        let floorStabilityAltarCooldownSprite = null;
        let floorVictoryPointSprite = null;
        let floorShardSprite = null;
        let floorCollapsedSprite = null;
        let floorLakeSprite = null;
        let floorLake1Sprite = null;
        let floorLake2Sprite = null;
        let floorLake3Sprite = null;
        let playerSprite = null;
        let dwellerSprite = null;
        let coinMarkerSprite = null;
        let artifactMarkerSprite = null;

        const bounds = {
            minX: 0,
            minY: 0,
            maxX: 0,
            maxY: 0,
        };
        let lastRenderedMinX = 0;
        let lastRenderedMinY = 0;

        const state = {
            baseTileSize: 20,
            gap: 0,
            minPadding: 20,
            paddingX: 20,
            paddingY: 20,
            scale: 2,
            minScale: 0.35,
            maxScale: 4,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0,
            scrollStartLeft: 0,
            scrollStartTop: 0,
            moveInFlight: false,
            hoveredTileKey: null,
            suppressTileClick: false,
        };

        function getStepSize() {
            return (state.baseTileSize + state.gap) * state.scale;
        }

        function updateDynamicPadding() {
            const tileSize = state.baseTileSize * state.scale;

            state.paddingX = Math.max(state.minPadding, (viewport.clientWidth / 2) - (tileSize / 2));
            state.paddingY = Math.max(state.minPadding, (viewport.clientHeight / 2) - (tileSize / 2));
        }

        function getCanvasSize() {
            const step = getStepSize();
            const width = (bounds.maxX - bounds.minX + 1) * step + (state.paddingX * 2);
            const height = (bounds.maxY - bounds.minY + 1) * step + (state.paddingY * 2);

            return {
                width: Math.ceil(width),
                height: Math.ceil(height),
            };
        }

        function resizeCanvas() {
            updateDynamicPadding();

            const size = getCanvasSize();
            const dpr = window.devicePixelRatio || 1;

            canvas.style.width = `${size.width}px`;
            canvas.style.height = `${size.height}px`;
            canvas.width = Math.max(1, Math.floor(size.width * dpr));
            canvas.height = Math.max(1, Math.floor(size.height * dpr));

            context.setTransform(dpr, 0, 0, dpr, 0, 0);
        }

        function draw() {
            const step = getStepSize();
            const tileSize = state.baseTileSize * state.scale;
            const hoveredTileKey = isTileInteractionEnabled() ? state.hoveredTileKey : null;

            context.clearRect(0, 0, canvas.width, canvas.height);

            const dpr = window.devicePixelRatio || 1;
            const canvasW = canvas.width / dpr;
            const canvasH = canvas.height / dpr;
            const gridOffsetX = state.paddingX % step;
            const gridOffsetY = state.paddingY % step;

            context.strokeStyle = 'rgba(255, 255, 255, 0.06)';
            context.lineWidth = 0.5;
            context.beginPath();
            for (let x = gridOffsetX; x < canvasW; x += step) {
                context.moveTo(x, 0);
                context.lineTo(x, canvasH);
            }
            for (let y = gridOffsetY; y < canvasH; y += step) {
                context.moveTo(0, y);
                context.lineTo(canvasW, y);
            }
            context.stroke();

            drawDistanceDarkening(tileSize, step);

            for (const tile of tiles) {
                const x = state.paddingX + (tile.point.x - bounds.minX) * step;
                const y = state.paddingY + (tile.point.y - bounds.minY) * step;
                const openDirections = new Set(tile.directions ?? []);
                const tileKey = getTileKey(tile.point.x, tile.point.y);
                const isOutsideVisibility = isTileOutsideVisibility(tile);
                const altarGlowColor = getAltarGlowColor(tile);

                drawFloor(tile, x, y, tileSize, tileKey === hoveredTileKey);

                if (!openDirections.has('top')) {
                    drawWall('top', x, y, tileSize);
                }

                if (!openDirections.has('right')) {
                    drawWall('right', x, y, tileSize);
                }

                if (!openDirections.has('bottom')) {
                    drawWall('bottom', x, y, tileSize);
                }

                if (!openDirections.has('left')) {
                    drawWall('left', x, y, tileSize);
                }

                if (Boolean(tile?.isTrapped) && !isOutsideVisibility) {
                    drawTrapMarker(x, y, tileSize);
                }

                if (Boolean(tile?.isRelic) && !isOutsideVisibility) {
                    drawRelicMarker(x, y, tileSize);
                }

                if (isOutsideVisibility && !isArtifactAtPoint(tile?.point) && !tile?.isCollapsed) {
                    drawVisibilityOverlay(x, y, tileSize, step);
                }

                if (isOutsideVisibility && altarGlowColor) {
                    drawAltarVisibilityGlow(x, y, tileSize, altarGlowColor);
                }
            }

            drawDwellers(tileSize, step);
            drawPlayer(tileSize, step);
            drawArtifactAtLocation(step, tileSize);
            drawArtifactDirectionGlow(step, tileSize);
        }

        function drawDistanceDarkening(tileSize, step) {
            if (!bounds || bounds.minX === Infinity) {
                return;
            }

            const dpr = window.devicePixelRatio || 1;
            const canvasW = canvas.width / dpr;
            const canvasH = canvas.height / dpr;

            const originX = state.paddingX + (0 - bounds.minX) * step + tileSize / 2;
            const originY = state.paddingY + (0 - bounds.minY) * step + tileSize / 2;

            const maxDist = Math.sqrt(
                Math.max(originX, canvasW - originX) ** 2 +
                Math.max(originY, canvasH - originY) ** 2
            );

            const gradient = context.createRadialGradient(
                originX, originY, tileSize * 3,
                originX, originY, maxDist,
            );
            gradient.addColorStop(0, 'rgba(0, 0, 0, 0)');
            gradient.addColorStop(1, 'rgba(0, 0, 0, 0.72)');

            context.save();
            context.fillStyle = gradient;
            context.fillRect(0, 0, canvasW, canvasH);
            context.restore();
        }

        function drawFloor(tile, x, y, tileSize, isHovered = false) {
            const hasCoins = Number(tile?.coins ?? 0) > 0;
            const isCollapsed = Boolean(tile?.isCollapsed);
            const isOrigin = Boolean(tile?.isOrigin);
            const isSupported = Boolean(tile?.isSupported);
            const isHealthAltar = Boolean(tile?.isHealthAltar);
            const isManaAltar = Boolean(tile?.isManaAltar);
            const isStabilityAltar = Boolean(tile?.isStabilityAltar);
            const isVictoryPoint = Boolean(tile?.isVictoryPoint);
            const isShard = Boolean(tile?.isShard);
            const altarOnCooldown = numberFrom(tile?.altarCooldown) > 0;
            const altarCooldown = Math.max(0, Math.floor(numberFrom(tile?.altarCooldown)));
            const isLake = Boolean(tile?.isLake);
            const lakeDepth = tile?.depth ?? null;
            const isOutsideVisibility = isTileOutsideVisibility(tile);
            const altarSprite = isHealthAltar
                ? (altarOnCooldown ? floorHealthAltarCooldownSprite : floorHealthAltarSprite)
                : isManaAltar
                ? (altarOnCooldown ? floorManaAltarCooldownSprite : floorManaAltarSprite)
                : isStabilityAltar
                ? (altarOnCooldown ? floorStabilityAltarCooldownSprite : floorStabilityAltarSprite)
                : null;
            const sprite = isCollapsed
                ? (floorCollapsedSprite ?? floorSprite)
                : isVictoryPoint
                ? (floorVictoryPointSprite ?? floorSprite)
                : isShard
                ? (floorShardSprite ?? floorSprite)
                : altarSprite
                ? (altarSprite ?? floorSprite)
                : isOrigin
                ? (floorOriginSprite ?? floorSprite)
                : (isSupported ? (floorSupportSprite ?? floorSprite) : floorSprite);

            if (isLake) {
                const lakeSprite = lakeDepth === 1 ? floorLake1Sprite
                    : lakeDepth === 2 ? floorLake2Sprite
                    : lakeDepth === 3 ? floorLake3Sprite
                    : floorLakeSprite;
                if (lakeSprite) {
                    context.drawImage(lakeSprite, x, y, tileSize, tileSize);
                } else {
                    context.fillStyle = '#3b82f6';
                    context.fillRect(x, y, tileSize, tileSize);
                }
            } else if (sprite) {
                context.drawImage(sprite, x, y, tileSize, tileSize);
            } else {
                context.fillStyle = '#9ca3af';
                context.fillRect(x, y, tileSize, tileSize);
            }

            if ((isHealthAltar || isManaAltar || isStabilityAltar) && altarCooldown > 0 && !isOutsideVisibility) {
                const cooldownColor = isHealthAltar
                    ? '#22c55e'
                    : isManaAltar
                    ? '#3b82f6'
                    : '#f97316';
                drawAltarCooldownBadge(x, y, tileSize, altarCooldown, cooldownColor);
            }

            if (hasCoins && !isOutsideVisibility) {
                const markerSize = Math.max(4, tileSize * 0.25);
                const markerX = x + ((tileSize - markerSize) / 2);
                const markerY = y + ((tileSize - markerSize) / 2);

                if (coinMarkerSprite) {
                    context.drawImage(coinMarkerSprite, markerX, markerY, markerSize, markerSize);
                } else {
                    context.save();
                    context.fillStyle = '#facc15';
                    context.fillRect(markerX, markerY, markerSize, markerSize);
                    context.restore();
                }
            }

            if (!isHovered) {
                return;
            }

            context.save();
            context.fillStyle = 'rgba(250, 204, 21, 0.22)';
            context.strokeStyle = 'rgba(250, 204, 21, 0.9)';
            context.lineWidth = Math.max(1, Math.round(state.scale));
            context.fillRect(x, y, tileSize, tileSize);
            context.strokeRect(x + 0.5, y + 0.5, tileSize - 1, tileSize - 1);
            context.restore();
        }

        function drawAltarCooldownBadge(x, y, tileSize, cooldown, textColor) {
            context.save();
            context.fillStyle = textColor;
            context.textAlign = 'center';
            context.textBaseline = 'middle';
            context.font = `700 ${Math.max(8, Math.floor(tileSize * 0.20))}px ${titleFontFamily}`;
            context.shadowColor = 'rgba(0, 0, 0, 0.48)';
            context.shadowBlur = Math.max(1, tileSize * 0.1);
            context.fillText(String(cooldown), x + (tileSize / 2), y + (tileSize / 2) + 0.5);
            context.restore();
        }

        function drawWall(direction, x, y, tileSize) {
            const sprite = wallSprites[direction];

            if (!sprite) {
                return;
            }

            let drawX = x;
            let drawY = y;
            let drawWidth = tileSize;
            let drawHeight = tileSize;

            if (direction === 'top') {
                drawHeight = tileSize * (sprite.height / sprite.width);
            }

            if (direction === 'bottom') {
                drawHeight = tileSize * (sprite.height / sprite.width);
                drawY = y + tileSize - drawHeight;
            }

            if (direction === 'left') {
                drawWidth = tileSize * (sprite.width / sprite.height);
            }

            if (direction === 'right') {
                drawWidth = tileSize * (sprite.width / sprite.height);
                drawX = x + tileSize - drawWidth;
            }

            context.drawImage(sprite, drawX, drawY, drawWidth, drawHeight);
        }

        function isTileOutsideVisibility(tile) {
            return !isTileWithinVisibility(tile);
        }

        function isTileWithinVisibility(tile) {
            const point = tile?.point ?? tile;

            if (!point || playerPosition === null) {
                return false;
            }

            const dx = Number(point.x) - Number(playerPosition.x);
            const dy = Number(point.y) - Number(playerPosition.y);
            const distance = Math.hypot(dx, dy);

            return distance < visibilityRadius;
        }

        function drawVisibilityOverlay(x, y, tileSize, step) {
            const dpr = window.devicePixelRatio || 1;
            const canvasW = canvas.width / dpr;
            const canvasH = canvas.height / dpr;

            const originX = state.paddingX + (0 - bounds.minX) * step + tileSize / 2;
            const originY = state.paddingY + (0 - bounds.minY) * step + tileSize / 2;

            const maxDist = Math.sqrt(
                Math.max(originX, canvasW - originX) ** 2 +
                Math.max(originY, canvasH - originY) ** 2
            );

            const cx = x + tileSize / 2;
            const cy = y + tileSize / 2;
            const dist = Math.hypot(cx - originX, cy - originY);
            const innerRadius = tileSize * 3;
            const distanceAlpha = Math.max(0, (dist - innerRadius) / (maxDist - innerRadius)) * 0.72;

            const baseAlpha = 0.68;
            const combined = 1 - (1 - baseAlpha) * (1 - distanceAlpha);

            context.save();
            context.fillStyle = `rgba(0, 0, 0, ${combined})`;
            context.fillRect(x, y, tileSize, tileSize);
            context.restore();
        }

        function getAltarGlowColor(tile) {
            if (tile?.isHealthAltar) {
                return '#22c55e';
            }

            if (tile?.isManaAltar) {
                return '#3b82f6';
            }

            if (tile?.isStabilityAltar) {
                return '#f97316';
            }

            return null;
        }

        function drawAltarVisibilityGlow(x, y, tileSize, glowColor) {
            context.save();
            const glowSpread = Math.max(8, tileSize * 0.5);
            const centerX = x + (tileSize / 2);
            const centerY = y + (tileSize / 2);
            const gradient = context.createRadialGradient(
                centerX,
                centerY,
                tileSize * 0.32,
                centerX,
                centerY,
                tileSize * 0.95
            );

            gradient.addColorStop(0, `${glowColor}00`);
            gradient.addColorStop(0.62, `${glowColor}00`);
            gradient.addColorStop(0.86, `${glowColor}cc`);
            gradient.addColorStop(1, `${glowColor}00`);
            context.fillStyle = gradient;
            context.fillRect(
                x - glowSpread,
                y - glowSpread,
                tileSize + (glowSpread * 2),
                tileSize + (glowSpread * 2)
            );
            context.restore();
        }

        function drawTrapMarker(x, y, tileSize) {
            const markerSize = Math.max(8, tileSize * 0.7);
            const markerX = x + ((tileSize - markerSize) / 2);
            const markerY = y + ((tileSize - markerSize) / 2);

            context.save();

            if (trapMarkerPath) {
                context.translate(markerX, markerY);
                context.scale(markerSize / 512, markerSize / 512);
                context.fillStyle = '#9A2B25';
                context.fill(trapMarkerPath);
                context.restore();
                return;
            }

            context.fillStyle = '#9A2B25';
            context.beginPath();
            context.arc(markerX + (markerSize / 2), markerY + (markerSize / 2), markerSize / 4, 0, Math.PI * 2);
            context.fill();
            context.restore();
        }

        function drawRelicMarker(x, y, tileSize) {
            const markerSize = Math.max(8, tileSize * 0.7);
            const markerX = x + ((tileSize - markerSize) / 2);
            const markerY = y + ((tileSize - markerSize) / 2);

            context.save();
            context.translate(markerX, markerY);
            context.scale(markerSize / 512, markerSize / 512);
            context.fillStyle = '#D4AF37';

            if (relicMarkerPath) {
                context.fill(relicMarkerPath);
            }

            context.restore();
        }

        function isArtifactAtPoint(pointValue) {
            const point = toPoint(pointValue);

            if (!point || !artifactLocation) {
                return false;
            }

            return point.x === artifactLocation.x && point.y === artifactLocation.y;
        }

        function getArtifactTargetPoint() {
            return artifactLocation;
        }

        function isArtifactWithinVisibility() {
            if (!artifactLocation || !playerPosition || !Number.isFinite(visibilityRadius)) {
                return false;
            }

            const dx = Number(artifactLocation.x) - Number(playerPosition.x);
            const dy = Number(artifactLocation.y) - Number(playerPosition.y);
            const distance = Math.hypot(dx, dy);

            return distance <= visibilityRadius;
        }

        function drawArtifactAtLocation(step, tileSize) {
            if (!artifactLocation || !artifactMarkerSprite || !isArtifactWithinVisibility()) {
                return;
            }

            const x = state.paddingX + (artifactLocation.x - bounds.minX) * step;
            const y = state.paddingY + (artifactLocation.y - bounds.minY) * step;
            const markerSize = Math.max(8, tileSize * 0.52);
            const markerX = x + ((tileSize - markerSize) / 2);
            const markerY = y + ((tileSize - markerSize) / 2);

            context.drawImage(artifactMarkerSprite, markerX, markerY, markerSize, markerSize);
        }

        function drawArtifactDirectionGlow(step, tileSize) {
            const artifactPoint = getArtifactTargetPoint();

            if (!artifactPoint || !viewport || !playerPosition || !artifactCompass) {
                if (artifactCompass) {
                    artifactCompass.style.display = 'none';
                }
                return;
            }

            if (isArtifactWithinVisibility()) {
                artifactCompass.style.display = 'none';
                return;
            }

            const playerX = state.paddingX + (playerPosition.x - bounds.minX) * step + (tileSize / 2);
            const playerY = state.paddingY + (playerPosition.y - bounds.minY) * step + (tileSize / 2);
            const artifactX = state.paddingX + (artifactPoint.x - bounds.minX) * step + (tileSize / 2);
            const artifactY = state.paddingY + (artifactPoint.y - bounds.minY) * step + (tileSize / 2);
            const viewportLeft = viewport.scrollLeft;
            const viewportTop = viewport.scrollTop;
            const viewportWidth = viewport.clientWidth;
            const viewportHeight = viewport.clientHeight;
            const viewportRight = viewportLeft + viewportWidth;
            const viewportBottom = viewportTop + viewportHeight;
            const viewportRect = viewport.getBoundingClientRect();
            const statsNotchEl = document.querySelector('.bottom-notch');
            const topInset = statsNotchEl
                ? Math.max(0, statsNotchEl.getBoundingClientRect().bottom - viewportRect.top)
                : 70;
            const handNotchEl = document.getElementById('hand-notch');
            const bottomInset = handNotchEl
                ? Math.max(0, viewportRect.bottom - handNotchEl.getBoundingClientRect().top)
                : 0;
            const effectiveTop = viewportTop + topInset;
            const effectiveBottom = viewportBottom - bottomInset;
            const vx = artifactX - playerX;
            const vy = artifactY - playerY;

            if (vx === 0 && vy === 0) {
                artifactCompass.style.display = 'none';
                return;
            }

            const tileDx = Number(artifactPoint.x) - Number(playerPosition.x);
            const tileDy = Number(artifactPoint.y) - Number(playerPosition.y);
            const distanceInTiles = Math.hypot(tileDx, tileDy);
            const minCompassSize = 10;
            const maxCompassSize = 40;
            const maxDistanceForMinSize = 30;
            const distanceRatio = Math.min(1, distanceInTiles / maxDistanceForMinSize);
            const markerSize = maxCompassSize - ((maxCompassSize - minCompassSize) * distanceRatio);

            let hitX = null;
            let hitY = null;
            let bestT = Infinity;

            if (vx !== 0) {
                const tLeft = (viewportLeft - playerX) / vx;
                const yLeft = playerY + (tLeft * vy);

                if (tLeft > 0 && yLeft >= effectiveTop && yLeft <= effectiveBottom && tLeft < bestT) {
                    bestT = tLeft;
                    hitX = viewportLeft;
                    hitY = yLeft;
                }

                const tRight = (viewportRight - playerX) / vx;
                const yRight = playerY + (tRight * vy);

                if (tRight > 0 && yRight >= effectiveTop && yRight <= effectiveBottom && tRight < bestT) {
                    bestT = tRight;
                    hitX = viewportRight;
                    hitY = yRight;
                }
            }

            if (vy !== 0) {
                const tTop = (effectiveTop - playerY) / vy;
                const xTop = playerX + (tTop * vx);

                if (tTop > 0 && xTop >= viewportLeft && xTop <= viewportRight && tTop < bestT) {
                    bestT = tTop;
                    hitX = xTop;
                    hitY = effectiveTop;
                }

                const tBottom = (effectiveBottom - playerY) / vy;
                const xBottom = playerX + (tBottom * vx);

                if (tBottom > 0 && xBottom >= viewportLeft && xBottom <= viewportRight && tBottom < bestT) {
                    bestT = tBottom;
                    hitX = xBottom;
                    hitY = effectiveBottom;
                }
            }

            if (!Number.isFinite(bestT) || hitX === null || hitY === null) {
                artifactCompass.style.display = 'none';
                return;
            }

            const markerX = viewportRect.left + (hitX - viewportLeft);
            const markerY = viewportRect.top + (hitY - viewportTop);

            artifactCompass.style.width = `${markerSize}px`;
            artifactCompass.style.height = `${markerSize}px`;
            artifactCompass.style.left = `${markerX}px`;
            artifactCompass.style.top = `${markerY}px`;
            artifactCompass.style.display = 'block';
        }

        function drawPlayer(tileSize, step) {
            if (!playerPosition || !playerSprite) {
                return;
            }

            const visualPos = getVisualPlayerPosition() ?? playerPosition;
            const tileX = state.paddingX + (visualPos.x - bounds.minX) * step;
            const tileY = state.paddingY + (visualPos.y - bounds.minY) * step;
            const avatarSize = tileSize * 0.6;
            const avatarX = tileX + ((tileSize - avatarSize) / 2);
            const avatarY = tileY + ((tileSize - avatarSize) / 2);
            const avatarRadius = avatarSize / 2;
            const avatarCenterX = avatarX + avatarRadius;
            const avatarCenterY = avatarY + avatarRadius;

            context.save();
            context.shadowColor = 'rgba(0, 0, 0, 0.35)';
            context.shadowBlur = Math.max(3, tileSize * 0.15);
            context.shadowOffsetY = Math.max(1, tileSize * 0.08);

            context.beginPath();
            context.arc(avatarCenterX, avatarCenterY, avatarRadius, 0, Math.PI * 2);
            context.closePath();
            context.clip();
            context.drawImage(playerSprite, avatarX, avatarY, avatarSize, avatarSize);
            context.restore();

            context.save();
            context.beginPath();
            context.arc(avatarCenterX, avatarCenterY, avatarRadius - 1, 0, Math.PI * 2);
            context.closePath();
            context.lineWidth = 2;
            context.strokeStyle = '#3b82f6';
            context.stroke();
            context.restore();
        }

        function isPlayerOnOriginTile() {
            if (!playerPosition) {
                return false;
            }

            const tile = findTileByPoint(playerPosition);
            return Boolean(tile?.isOrigin);
        }

        function updateExitDungeonButton() {
            if (!exitDungeonButton || isGameBlocked() || !playerPosition || !hasPlayerMoved || !isPlayerOnOriginTile()) {
                if (exitDungeonButton) {
                    exitDungeonButton.style.display = 'none';
                }
                return;
            }

            const step = getStepSize();
            const tileSize = state.baseTileSize * state.scale;
            const tileX = state.paddingX + (playerPosition.x - bounds.minX) * step;
            const tileY = state.paddingY + (playerPosition.y - bounds.minY) * step;
            const avatarSize = tileSize * 0.6;
            const avatarX = tileX + ((tileSize - avatarSize) / 2);
            const avatarY = tileY + ((tileSize - avatarSize) / 2);
            const localX = avatarX - viewport.scrollLeft + (avatarSize / 2);
            const localY = avatarY - viewport.scrollTop;

            if (localX < 0 || localX > viewport.clientWidth || localY + avatarSize < 0 || localY > viewport.clientHeight) {
                exitDungeonButton.style.display = 'none';
                return;
            }

            const viewportRect = viewport.getBoundingClientRect();
            const minLeft = viewportRect.left + 12;
            const maxLeft = viewportRect.right - 12;
            const rawLeft = viewportRect.left + localX;
            const left = Math.min(maxLeft, Math.max(minLeft, rawLeft));
            const top = viewportRect.top + localY + avatarSize + Math.max(6, tileSize * 0.15);

            exitDungeonButton.style.left = `${left}px`;
            exitDungeonButton.style.top = `${top}px`;
            exitDungeonButton.style.display = 'inline-flex';
            exitDungeonButton.disabled = state.moveInFlight;
        }

        function updateDeathOverlay() {
            if (!deathOverlay) {
                return;
            }

            deathOverlay.style.display = isPlayerDead && !hasPlayerExited ? 'flex' : 'none';
        }

        function updateExitedOverlay() {
            if (!exitedOverlay) {
                return;
            }

            if (exitedOverlayCoins) {
                const coins = exitedCoinsAmount !== null ? exitedCoinsAmount : numberFrom(stats.coins);
                exitedOverlayCoins.textContent = String(coins);
            }

            exitedOverlay.style.display = hasPlayerExited ? 'flex' : 'none';
        }

        function updateResignedOverlay() {
            if (!resignedOverlay) {
                return;
            }

            resignedOverlay.style.display = hasPlayerResigned ? 'flex' : 'none';
        }

        async function resign() {
            if (state.moveInFlight || isGameBlocked()) {
                return;
            }

            state.moveInFlight = true;

            try {
                const response = await fetch('/dungeon/resign', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    return;
                }

                const resignResult = await response.json();
                dungeonVersion = resignResult.version ?? dungeonVersion;
                latestChanges = Array.isArray(resignResult.changes) ? resignResult.changes : [];
                applyChanges(resignResult.changes);
                render();
                renderDebugPopup();
                renderCounters();
                renderCardSlots();
                renderHand();
            } finally {
                state.moveInFlight = false;
            }
        }

        function drawDwellers(tileSize, step) {
            if (!dwellerSprite) {
                return;
            }

            for (const dweller of dwellers) {
                if (!dweller.isVisible) {
                    continue;
                }

                const tileX = state.paddingX + (dweller.x - bounds.minX) * step;
                const tileY = state.paddingY + (dweller.y - bounds.minY) * step;
                const avatarSize = tileSize * 0.6;
                const avatarX = tileX + ((tileSize - avatarSize) / 2);
                const avatarY = tileY + ((tileSize - avatarSize) / 2);
                const avatarRadius = avatarSize / 2;
                const avatarCenterX = avatarX + avatarRadius;
                const avatarCenterY = avatarY + avatarRadius;

                context.save();
                context.shadowColor = 'rgba(0, 0, 0, 0.35)';
                context.shadowBlur = Math.max(3, tileSize * 0.15);
                context.shadowOffsetY = Math.max(1, tileSize * 0.08);

                context.beginPath();
                context.arc(avatarCenterX, avatarCenterY, avatarRadius, 0, Math.PI * 2);
                context.closePath();
                context.clip();
                context.drawImage(dwellerSprite, avatarX, avatarY, avatarSize, avatarSize);
                context.restore();

                context.save();
                context.beginPath();
                context.arc(avatarCenterX, avatarCenterY, avatarRadius - 1, 0, Math.PI * 2);
                context.closePath();
                context.lineWidth = 2;
                context.strokeStyle = '#7f1d1d';
                context.stroke();
                context.restore();
            }
        }

        function loadImage(src) {
            return new Promise((resolve) => {
                const image = new Image();
                image.onload = () => {
                    resolve(image);
                };
                image.onerror = () => {
                    resolve(null);
                };
                image.src = src;
            });
        }

        function getTileKey(x, y) {
            return `${x}:${y}`;
        }

        function numberFrom(value, fallback = 0) {
            const normalized = Number(value);
            return Number.isFinite(normalized) ? normalized : fallback;
        }

        function clampStatMinimum(statKey, value) {
            const normalized = numberFrom(value);

            if ((statKey === 'health' || statKey === 'stability') && normalized < 0) {
                return 0;
            }

            return normalized;
        }

        function recomputeBoundsFromTiles() {
            if (tiles.length === 0) {
                bounds.minX = 0;
                bounds.minY = 0;
                bounds.maxX = 0;
                bounds.maxY = 0;
                return;
            }

            const nextBounds = tiles.reduce((acc, tile) => {
                const x = tile.point.x;
                const y = tile.point.y;

                acc.minX = Math.min(acc.minX, x);
                acc.minY = Math.min(acc.minY, y);
                acc.maxX = Math.max(acc.maxX, x);
                acc.maxY = Math.max(acc.maxY, y);

                return acc;
            }, { minX: Infinity, minY: Infinity, maxX: -Infinity, maxY: -Infinity });

            bounds.minX = nextBounds.minX;
            bounds.minY = nextBounds.minY;
            bounds.maxX = nextBounds.maxX;
            bounds.maxY = nextBounds.maxY;
        }

        function updateBoundsForPoint(point) {
            bounds.minX = Math.min(bounds.minX, point.x);
            bounds.minY = Math.min(bounds.minY, point.y);
            bounds.maxX = Math.max(bounds.maxX, point.x);
            bounds.maxY = Math.max(bounds.maxY, point.y);
        }

        function upsertTile(rawTile) {
            const tilePayload = rawTile?.tile ?? rawTile;
            const [tile] = normalizeTiles([tilePayload]);

            if (!tile) {
                return;
            }

            const tileKey = getTileKey(tile.point.x, tile.point.y);
            const existingTile = tileIndex.get(tileKey);

            if (existingTile) {
                const index = tiles.indexOf(existingTile);

                if (index !== -1) {
                    tiles[index] = tile;
                }
            } else {
                tiles.push(tile);
            }

            tileIndex.set(tileKey, tile);
            updateBoundsForPoint(tile.point);
        }

        function normalizeTiles(rawTiles) {
            const normalized = [];
            collectTiles(rawTiles, normalized, null, null);

            return normalized;
        }

        function collectTiles(node, out, fallbackX, fallbackY) {
            if (!node || typeof node !== 'object') {
                return;
            }

            if (node.point && typeof node.point.x !== 'undefined' && typeof node.point.y !== 'undefined') {
                out.push({
                    ...node,
                    point: {
                        x: Number(node.point.x),
                        y: Number(node.point.y),
                    },
                });
                return;
            }

            // Support keyed tile payloads where x/y are provided by array keys.
            const looksLikeTile = (
                fallbackX !== null
                && fallbackY !== null
                && (Array.isArray(node.directions) || typeof node.color !== 'undefined')
            );

            if (looksLikeTile) {
                out.push({
                    ...node,
                    point: {
                        x: Number(fallbackX),
                        y: Number(fallbackY),
                    },
                });
                return;
            }

            for (const [key, value] of Object.entries(node)) {
                if (fallbackX === null) {
                    collectTiles(value, out, Number(key), fallbackY);
                    continue;
                }

                if (fallbackY === null) {
                    collectTiles(value, out, fallbackX, Number(key));
                    continue;
                }

                collectTiles(value, out, fallbackX, fallbackY);
            }
        }

        function hydrateFromPayload(nextPayload) {
            const normalizedTiles = normalizeTiles(nextPayload?.tiles ?? []);
            const normalizedDwellers = normalizeDwellers(nextPayload?.dwellers ?? []);
            const normalizedHand = normalizeHand(nextPayload?.hand ?? []);
            const normalizedActiveCard = normalizeCard(nextPayload?.activeCard ?? null);
            const normalizedPassiveCard = normalizeCard(nextPayload?.passiveCard ?? null);

            tiles.length = 0;
            tileIndex.clear();
            dwellers.length = 0;
            dwellerIndex.clear();
            hand.clear();

            for (const tile of normalizedTiles) {
                tiles.push(tile);
                tileIndex.set(getTileKey(tile.point.x, tile.point.y), tile);
            }

            for (const card of normalizedHand) {
                hand.set(card.id, card);
            }

            for (const dweller of normalizedDwellers) {
                const dwellerKey = getTileKey(dweller.x, dweller.y);
                dwellers.push(dweller);
                dwellerIndex.set(dwellerKey, dweller);
            }

            activeCard = normalizedActiveCard;
            passiveCard = normalizedPassiveCard;
            state.hoveredTileKey = null;

            recomputeBoundsFromTiles();
            playerPosition = toPoint(nextPayload?.playerPosition);
            artifactLocation = toPoint(nextPayload?.artifactLocation);
            visibilityRadius = Number.isFinite(Number(nextPayload?.visibilityRadius))
                ? Number(nextPayload.visibilityRadius)
                : null;
            dungeonVersion = nextPayload?.version ?? null;
            stats.coins = numberFrom(nextPayload?.coins);
            stats.shards = numberFrom(nextPayload?.shards);
            stats.victoryPoints = numberFrom(nextPayload?.victoryPoints);
            stats.health = clampStatMinimum('health', nextPayload?.health);
            stats.maxHealth = numberFrom(nextPayload?.maxHealth);
            stats.mana = numberFrom(nextPayload?.mana);
            stats.maxMana = numberFrom(nextPayload?.maxMana);
            stats.stability = clampStatMinimum('stability', nextPayload?.stability);
            stats.maxStability = numberFrom(nextPayload?.maxStability);
            const hasEnded = Boolean(nextPayload?.hasEnded);
            isPlayerDead = hasEnded && numberFrom(nextPayload?.health) <= 0;
            hasPlayerExited = hasEnded && !isPlayerDead;
            exitedCoinsAmount = hasPlayerExited ? numberFrom(nextPayload?.coins) : null;
            deckSize = nextPayload?.deck ? Object.keys(nextPayload.deck).length : 0;
            latestChanges = [];
        }

        function normalizeCard(value) {
            if (!value || typeof value !== 'object') {
                return null;
            }

            if (typeof value.id !== 'string') {
                return null;
            }

            return {
                id: value.id,
                name: typeof value.name === 'string' ? value.name : 'Unknown Card',
                description: typeof value.description === 'string' ? value.description : '',
                image: typeof value.image === 'string' ? value.image : '',
                rarity: typeof value.rarity === 'string' ? value.rarity : '',
                type: typeof value.type === 'string' ? value.type : '',
                mana: Number.isFinite(Number(value.mana)) ? Number(value.mana) : 0,
                canInteractWithTile: Boolean(value.canInteractWithTile),
                label: typeof value.label === 'string' ? value.label : '',
            };
        }

        function collectDwellers(node, out, fallbackX, fallbackY) {
            if (!node) {
                return;
            }

            if (Array.isArray(node)) {
                for (const item of node) {
                    collectDwellers(item, out, fallbackX, fallbackY);
                }
                return;
            }

            if (typeof node !== 'object') {
                return;
            }

            if (typeof node.x !== 'undefined' && typeof node.y !== 'undefined') {
                out.push({
                    ...node,
                    x: Number(node.x),
                    y: Number(node.y),
                    isVisible: typeof node.isVisible === 'boolean' ? node.isVisible : Boolean(node.isVisible ?? true),
                });
                return;
            }

            if (node.point && typeof node.point.x !== 'undefined' && typeof node.point.y !== 'undefined') {
                out.push({
                    ...node,
                    x: Number(node.point.x),
                    y: Number(node.point.y),
                    isVisible: typeof node.isVisible === 'boolean' ? node.isVisible : Boolean(node.isVisible ?? true),
                });
                return;
            }

            if (fallbackX !== null && fallbackY !== null) {
                out.push({
                    ...node,
                    x: Number(fallbackX),
                    y: Number(fallbackY),
                    isVisible: typeof node.isVisible === 'boolean' ? node.isVisible : Boolean(node.isVisible ?? true),
                });
                return;
            }

            for (const [key, value] of Object.entries(node)) {
                if (fallbackX === null) {
                    collectDwellers(value, out, Number(key), fallbackY);
                    continue;
                }

                if (fallbackY === null) {
                    collectDwellers(value, out, fallbackX, Number(key));
                    continue;
                }

                collectDwellers(value, out, fallbackX, fallbackY);
            }
        }

        function normalizeDwellers(value) {
            const normalized = [];
            collectDwellers(value, normalized, null, null);

            return normalized
                .filter((dweller) => Number.isFinite(dweller.x) && Number.isFinite(dweller.y))
                .map((dweller) => ({
                    ...dweller,
                    isVisible: typeof dweller.isVisible === 'boolean' ? dweller.isVisible : true,
                }));
        }

        function upsertDweller(pointValue) {
            const point = toPoint(pointValue?.point ?? pointValue);

            if (!point) {
                return;
            }

            const dwellerKey = getTileKey(point.x, point.y);
            const isVisible = Boolean(pointValue?.isVisible ?? pointValue?.point?.isVisible ?? true);
            const existingDweller = dwellerIndex.get(dwellerKey);

            if (existingDweller) {
                Object.assign(existingDweller, pointValue ?? {}, {
                    x: point.x,
                    y: point.y,
                    isVisible,
                });
                return;
            }

            const dweller = {
                ...(pointValue ?? {}),
                x: point.x,
                y: point.y,
                isVisible,
            };

            dwellers.push(dweller);
            dwellerIndex.set(dwellerKey, dweller);
        }

        function removeDweller(pointValue) {
            const point = toPoint(pointValue?.point ?? pointValue);

            if (!point) {
                return;
            }

            const dwellerKey = getTileKey(point.x, point.y);
            const existingDweller = dwellerIndex.get(dwellerKey);
            dwellerIndex.delete(dwellerKey);

            if (existingDweller) {
                const index = dwellers.indexOf(existingDweller);

                if (index !== -1) {
                    dwellers.splice(index, 1);
                    return;
                }
            }

            // Keep array and index in sync even when index lookup missed.
            for (let i = dwellers.length - 1; i >= 0; i -= 1) {
                const dweller = dwellers[i];

                if (Number(dweller?.x) === point.x && Number(dweller?.y) === point.y) {
                    dwellers.splice(i, 1);
                }
            }
        }

        function applyDwellerSpawned(payload) {
            const dweller = payload?.dweller ?? payload?.point ?? payload?.position ?? payload;

            if (dweller && typeof dweller === 'object' && typeof dweller.isVisible !== 'undefined' && !Boolean(dweller.isVisible)) {
                removeDweller(dweller);
                return;
            }

            upsertDweller(dweller);
        }

        function applyDwellerMoved(payload) {
            const from = payload?.from ?? payload?.oldPosition ?? payload?.previousPosition ?? null;
            const to = payload?.dweller ?? payload?.to ?? payload?.position ?? payload?.point ?? null;

            removeDweller(from);

            if (to && typeof to === 'object' && typeof to.isVisible !== 'undefined' && !Boolean(to.isVisible)) {
                removeDweller(to);
                return;
            }

            upsertDweller(to);
        }

        function applyDwellerUpdated(payload) {
            const from = payload?.from ?? payload?.oldPosition ?? payload?.previousPosition ?? null;
            const dweller = payload?.dweller ?? payload?.to ?? payload?.position ?? payload?.point ?? payload;

            if (from) {
                removeDweller(from);
            }

            if (dweller && typeof dweller === 'object' && typeof dweller.isVisible !== 'undefined' && !Boolean(dweller.isVisible)) {
                removeDweller(dweller);
                return;
            }

            upsertDweller(dweller);
        }

        function applyDwellerDespawned(payload) {
            const point = payload?.dweller ?? payload?.point ?? payload?.position ?? payload;
            removeDweller(point);
        }

        function isTileInteractionEnabled() {
            return !isGameBlocked() && Boolean(activeCard?.canInteractWithTile);
        }

        function isGameBlocked() {
            return isPlayerDead || hasPlayerExited || hasPlayerResigned;
        }

        function updateViewportCursorState() {
            const isInteractMode = isTileInteractionEnabled();
            const hasHoveredTile = isInteractMode && Boolean(state.hoveredTileKey);

            viewport.classList.toggle('is-interact-mode', isInteractMode);
            viewport.classList.toggle('has-hovered-tile', hasHoveredTile);
        }

        function setHoveredTile(tile) {
            const nextKey = tile ? getTileKey(tile.point.x, tile.point.y) : null;

            if (state.hoveredTileKey === nextKey) {
                updateViewportCursorState();
                return;
            }

            state.hoveredTileKey = nextKey;
            updateViewportCursorState();
            render();
        }

        function clearHoveredTile() {
            setHoveredTile(null);
        }

        function getTileAtViewportPosition(clientX, clientY) {
            const rect = viewport.getBoundingClientRect();
            const viewportX = clientX - rect.left;
            const viewportY = clientY - rect.top;
            const step = getStepSize();
            const worldX = viewport.scrollLeft + viewportX - state.paddingX;
            const worldY = viewport.scrollTop + viewportY - state.paddingY;
            const tileX = Math.floor(worldX / step) + bounds.minX;
            const tileY = Math.floor(worldY / step) + bounds.minY;

            return tileIndex.get(getTileKey(tileX, tileY)) ?? null;
        }

        function getCardRarityClass(rarity) {
            if (typeof rarity !== 'string' || rarity.length === 0) {
                return 'hand-card-rarity-common';
            }

            return `hand-card-rarity-${rarity.toLowerCase()}`;
        }

        function collectCards(value, out) {
            if (!value) {
                return;
            }

            if (Array.isArray(value)) {
                for (const item of value) {
                    collectCards(item, out);
                }

                return;
            }

            if (typeof value !== 'object') {
                return;
            }

            const normalized = normalizeCard(value);

            if (normalized) {
                out.push(normalized);
                return;
            }

            for (const nested of Object.values(value)) {
                collectCards(nested, out);
            }
        }

        function normalizeHand(value) {
            const cards = [];
            collectCards(value, cards);

            return cards;
        }

        function applyCardDrawn(payload) {
            const card = normalizeCard(payload?.card);

            if (!card) {
                return;
            }

            hand.set(card.id, card);
            deckSize = Math.max(0, deckSize - 1);
        }

        function applyCardPlayed(payload) {
            const card = normalizeCard(payload?.card);

            if (!card) {
                return;
            }

            hand.delete(card.id);
        }

        function applyActiveCardSet(payload) {
            const card = normalizeCard(payload?.card);

            if (!card) {
                return;
            }

            activeCard = card;
            hand.delete(card.id);
            state.hoveredTileKey = null;
        }

        function applyActiveCardUnset() {
            activeCard = null;
            state.hoveredTileKey = null;
        }

        function applyPassiveCardSet(payload) {
            const card = normalizeCard(payload?.card);

            if (!card) {
                return;
            }

            passiveCard = card;
            hand.delete(card.id);
        }

        function applyPassiveCardUnset() {
            passiveCard = null;
        }

        function applyCardUpdated(payload) {
            const card = normalizeCard(payload?.card);

            if (!card) {
                return;
            }

            if (hand.has(card.id)) {
                hand.set(card.id, card);
            }

            if (activeCard?.id === card.id) {
                activeCard = card;
            }

            if (passiveCard?.id === card.id) {
                passiveCard = card;
            }
        }

        function applyHandUpdated(payload) {
            hand.clear();

            for (const card of normalizeHand(payload?.hand ?? payload)) {
                hand.set(card.id, card);
            }
        }

        function applyDeckUpdated(payload) {
            deckSize = normalizeHand(payload?.deck ?? payload?.hand ?? payload).length;
        }

        function getCardImageUrl(image) {
            if (!image) {
                return '';
            }

            if (image.startsWith('/dungeon/')) {
                return image;
            }

            if (image.startsWith('/')) {
                return `/dungeon${image}`;
            }

            return `/dungeon/${image}`;
        }

        function createCardElement(card, options = {}) {
            const article = document.createElement('article');
            article.className = 'hand-card';
            article.dataset.cardId = card.id;
            article.classList.add(getCardRarityClass(card.rarity));

            if (options.small) {
                article.classList.add('hand-card-small');
            }

            if (options.unplayable) {
                article.classList.add('hand-card-unplayable');
            }

            const mana = document.createElement('div');
            mana.className = 'hand-card-mana';
            const manaSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            manaSvg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            manaSvg.setAttribute('viewBox', '0 0 24 24');
            manaSvg.setAttribute('aria-hidden', 'true');
            const manaPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            manaPath.setAttribute('fill', 'currentColor');
            manaPath.setAttribute('d', 'M23 18v2h-2v1h-1v2h-2v-2h-1v-1h-2v-2h2v-1h1v-2h2v2h1v1zm0-14v2h-2v1h-1v2h-2V7h-1V6h-2V4h2V3h1V1h2v2h1v1zm-6 7v2h-2v1h-2v1h-1v1h-1v2h-1v2H8v-2H7v-2H6v-1H5v-1H3v-1H1v-2h2v-1h2V9h1V8h1V6h1V4h2v2h1v2h1v1h1v1h2v1z');
            manaSvg.appendChild(manaPath);
            mana.appendChild(manaSvg);
            mana.appendChild(document.createTextNode(String(card.mana)));
            article.appendChild(mana);

            if (options.showTypeBadge && (card.type === 'active' || card.type === 'passive')) {
                const type = document.createElement('div');
                type.className = `hand-card-type hand-card-type-${card.type}`;
                type.appendChild(createSlotIcon(card.type));
                article.appendChild(type);
            }

            if (card.image) {
                const image = document.createElement('img');
                image.className = 'hand-card-image';
                image.alt = card.name;
                image.src = getCardImageUrl(card.image);
                article.appendChild(image);
            }

            const content = document.createElement('div');
            content.className = 'hand-card-content';

            const title = document.createElement('div');
            title.className = 'hand-card-name';
            title.textContent = card.name;

            const description = document.createElement('div');
            description.className = 'hand-card-description';
            description.textContent = card.description;

            content.appendChild(title);
            content.appendChild(description);
            article.appendChild(content);

            if (options.small && card.label) {
                const label = document.createElement('div');
                label.className = 'hand-card-label';
                label.textContent = card.label;
                article.appendChild(label);
            }

            if (typeof options.onClick === 'function') {
                article.addEventListener('click', options.onClick);
            }

            return article;
        }

        function renderSideCardSlot(slotElement, card) {
            if (!slotElement) {
                return;
            }

            slotElement.innerHTML = '';

            if (!card) {
                const empty = document.createElement('div');
                empty.className = 'hand-slot-empty';
                empty.appendChild(createSlotIcon(slotElement === activeCardSlot ? 'active' : 'passive'));
                slotElement.appendChild(empty);
                return;
            }

            slotElement.appendChild(createCardElement(card, { small: true }));
        }

        function createSlotIcon(slotType) {
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            svg.setAttribute('viewBox', '0 0 24 24');
            svg.setAttribute('aria-hidden', 'true');

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('fill', 'currentColor');

            if (slotType === 'active') {
                path.setAttribute('d', 'M15.75 16v-2.8l-1.9-3.475q-.5.25-.8.725t-.3 1.05v8l1.425 2.5H22L21 9.5l-7-8l-.325.325q-.725.725-.862 1.7t.362 1.85L17.25 12.8V16zm-9 0v-3.2l4.075-7.425q.5-.875.338-1.85t-.863-1.7L10 1.5l-7 8L2 22h7.825l1.425-2.5v-8q0-.575-.312-1.05t-.788-.725L8.25 13.2V16z');
            } else {
                path.setAttribute('d', 'M20.5 22L7.4 9.5H1.9l5.8-7.775l3.3 1.65V6.65l3.675-.875l2.2 6.675L22 17.575L21.25 22zm-7.875 0L1.075 11.5h5.55L17.575 22z');
            }

            svg.appendChild(path);

            return svg;
        }

        function renderCardSlots() {
            renderSideCardSlot(activeCardSlot, activeCard);
            renderSideCardSlot(passiveCardSlot, passiveCard);
        }

        function renderHand() {
            if (!handCards) {
                return;
            }

            handCards.innerHTML = '';

            if (hand.size === 0) {
                const empty = document.createElement('div');
                empty.className = 'hand-empty';
                handCards.appendChild(empty);
            } else {
                for (const card of hand.values()) {
                    const isPlayable = card.mana <= stats.mana;
                    const article = createCardElement(card, {
                        unplayable: !isPlayable,
                        showTypeBadge: true,
                        onClick: isPlayable ? () => playCard(card.id) : null,
                    });
                    handCards.appendChild(article);
                }
            }

            if (deckCounter) {
                deckCounter.textContent = deckSize;
                handCards.appendChild(deckCounter);
            }
        }

        function toPoint(value) {
            if (!value || typeof value.x === 'undefined' || typeof value.y === 'undefined') {
                return null;
            }

            return {
                x: Number(value.x),
                y: Number(value.y),
            };
        }

        function findTileByPoint(pointValue) {
            const point = toPoint(pointValue);

            if (!point) {
                return null;
            }

            return tileIndex.get(getTileKey(point.x, point.y)) ?? null;
        }

        function applyTileCoinsAdded(payload) {
            const tileFromPayload = payload?.tile ?? null;

            if (tileFromPayload) {
                upsertTile(tileFromPayload);
                return;
            }

            const tile = findTileByPoint(payload?.point ?? payload?.position ?? payload?.to);

            if (!tile) {
                return;
            }

            if (typeof payload?.coins !== 'undefined') {
                tile.coins = numberFrom(payload.coins);
                return;
            }

            if (typeof payload?.addedCoins !== 'undefined') {
                tile.coins = numberFrom(tile.coins) + numberFrom(payload.addedCoins);
            }
        }

        function applyTileCoinsCollected(payload) {
            const tileFromPayload = payload?.tile ?? null;
            const collectedAmount = numberFrom(payload?.amount);
            const hasTotal = typeof payload?.total !== 'undefined';
            const totalCoins = numberFrom(payload?.total);

            if (tileFromPayload) {
                upsertTile({
                    ...tileFromPayload,
                    coins: 0,
                });
                stats.coins = hasTotal ? totalCoins : stats.coins + collectedAmount;
                return;
            }

            const tile = findTileByPoint(payload?.point ?? payload?.position ?? payload?.to);

            if (!tile) {
                return;
            }

            tile.coins = 0;
            stats.coins = hasTotal ? totalCoins : stats.coins + collectedAmount;
        }

        function applyArtifactSpawned(payload) {
            artifactLocation = toPoint(
                payload?.artifactLocation
                ?? payload?.artifactPoint
                ?? payload?.artifact
                ?? payload?.location
                ?? payload?.point
                ?? payload?.position
                ?? payload?.to
                ?? payload?.tile?.point
                ?? payload
            );
        }

        function applyArtifactCollected(payload) {
            artifactLocation = toPoint(payload?.artifactLocation ?? payload?.location ?? null);

            if (payload?.tile) {
                upsertTile(payload.tile);
            }
        }

        function applySignedStatChange(payload, statKey, isDecrease) {
            if (typeof payload?.total !== 'undefined') {
                stats[statKey] = clampStatMinimum(statKey, payload.total);
                return;
            }

            const absoluteValue = payload?.[statKey];

            if (typeof absoluteValue !== 'undefined') {
                stats[statKey] = clampStatMinimum(statKey, absoluteValue);
                return;
            }

            const amount = numberFrom(payload?.amount);
            const nextValue = stats[statKey] + (isDecrease ? -amount : amount);
            stats[statKey] = clampStatMinimum(statKey, nextValue);
        }

        function triggerDamageFlash() {
            if (!damageFlash) {
                return;
            }

            damageFlash.style.transition = 'none';
            damageFlash.style.opacity = '0.22';
            void damageFlash.offsetWidth;
            damageFlash.style.transition = 'opacity 2s ease-out';
            damageFlash.style.opacity = '0';
        }

        function applyChanges(changes) {
            if (!Array.isArray(changes)) {
                return;
            }

            for (const change of changes) {
                if (change?.payload?.message) {
                    showDungeonMessage(change.payload.message);
                }

                if (change?.name === 'player.died') {
                    isPlayerDead = true;
                    activeCard = null;
                    state.hoveredTileKey = null;
                    continue;
                }

                if (change?.name === 'player.exited' || change?.name === 'dungeon.exited') {
                    hasPlayerExited = true;
                    exitedCoinsAmount = typeof change?.payload?.coins !== 'undefined'
                        ? numberFrom(change.payload.coins)
                        : numberFrom(stats.coins);
                    activeCard = null;
                    state.hoveredTileKey = null;
                    continue;
                }

                if (change?.name === 'player.resigned') {
                    hasPlayerResigned = true;
                    activeCard = null;
                    state.hoveredTileKey = null;
                    continue;
                }

                if (change?.name === 'player.moved') {
                    playerPosition = toPoint(change.payload?.to) ?? playerPosition;
                    hasPlayerMoved = true;
                    continue;
                }

                if (
                    change?.name === 'tile.generated'
                    || change?.name === 'tile.collapsed'
                    || change?.name === 'tile.updated'
                ) {
                    upsertTile(change.payload);
                    continue;
                }

                if (change?.name === 'tile.coinsAdded') {
                    applyTileCoinsAdded(change.payload);
                    continue;
                }

                if (change?.name === 'tile.coinsCollected') {
                    applyTileCoinsCollected(change.payload);
                    continue;
                }

                if (change?.name === 'artifact.spawned') {
                    applyArtifactSpawned(change.payload);
                    continue;
                }

                if (change?.name === 'artifact.collected') {
                    applyArtifactCollected(change.payload);
                    continue;
                }

                if (change?.name === 'relic.collected') {
                    if (change.payload?.tile) {
                        upsertTile(change.payload.tile);
                    }
                    continue;
                }

                if (change?.name === 'player.coinsIncreased') {
                    if (typeof change?.payload?.total !== 'undefined') {
                        stats.coins = numberFrom(change.payload.total);
                        continue;
                    }

                    stats.coins += numberFrom(change?.payload?.amount);
                    continue;
                }

                if (change?.name === 'player.shardsIncreased') {
                    if (typeof change?.payload?.total !== 'undefined') {
                        stats.shards = numberFrom(change.payload.total);
                        continue;
                    }

                    stats.shards += numberFrom(change?.payload?.amount);
                    continue;
                }

                if (change?.name === 'player.victoryPointsIncreased') {
                    if (typeof change?.payload?.total !== 'undefined') {
                        stats.victoryPoints = numberFrom(change.payload.total);
                        continue;
                    }

                    stats.victoryPoints += numberFrom(change?.payload?.amount);
                    continue;
                }

                if (change?.name === 'player.maxHealthIncreased') {
                    if (typeof change.payload?.total !== 'undefined') {
                        stats.maxHealth = numberFrom(change.payload.total);
                        continue;
                    }

                    if (typeof change.payload?.maxHealth !== 'undefined') {
                        stats.maxHealth = numberFrom(change.payload.maxHealth);
                        continue;
                    }

                    stats.maxHealth += numberFrom(change.payload?.amount);
                    continue;
                }

                if (change?.name === 'player.maxManaIncreased') {
                    if (typeof change.payload?.total !== 'undefined') {
                        stats.maxMana = numberFrom(change.payload.total);
                        continue;
                    }

                    if (typeof change.payload?.maxMana !== 'undefined') {
                        stats.maxMana = numberFrom(change.payload.maxMana);
                        continue;
                    }

                    stats.maxMana += numberFrom(change.payload?.amount);
                    continue;
                }

                if (change?.name === 'card.drawn') {
                    applyCardDrawn(change.payload);
                    continue;
                }

                if (change?.name === 'card.played') {
                    applyCardPlayed(change.payload);
                    continue;
                }

                if (change?.name === 'card.activeSet') {
                    applyActiveCardSet(change.payload);
                    continue;
                }

                if (change?.name === 'card.activeUnset') {
                    applyActiveCardUnset();
                    continue;
                }

                if (change?.name === 'card.passiveSet' || change?.name === 'card.passsiveSet') {
                    applyPassiveCardSet(change.payload);
                    continue;
                }

                if (change?.name === 'card.passiveUnset' || change?.name === 'card.passsiveUnset') {
                    applyPassiveCardUnset();
                    continue;
                }

                if (change?.name === 'card.updated') {
                    applyCardUpdated(change.payload);
                    continue;
                }

                if (change?.name === 'hand.updated') {
                    applyHandUpdated(change.payload);
                    continue;
                }

                if (change?.name === 'deck.updated') {
                    applyDeckUpdated(change.payload);
                    continue;
                }

                if (change?.name === 'dweller.spawned') {
                    applyDwellerSpawned(change.payload);
                    continue;
                }

                if (change?.name === 'dweller.moved') {
                    applyDwellerMoved(change.payload);
                    continue;
                }

                if (change?.name === 'dweller.updated') {
                    applyDwellerUpdated(change.payload);
                    continue;
                }

                if (change?.name === 'dweller.despawned') {
                    applyDwellerDespawned(change.payload);
                    continue;
                }

                if (change?.name === 'visibility.changed') {
                    const nextRadius = Number(change?.payload?.visibilityRadius);

                    visibilityRadius = Number.isFinite(nextRadius) ? nextRadius : visibilityRadius;
                    continue;
                }

                if (change?.name === 'player.manaIncreased') {
                    if (typeof change.payload?.total !== 'undefined') {
                        stats.mana = numberFrom(change.payload.total);
                        continue;
                    }

                    if (typeof change.payload?.mana !== 'undefined') {
                        stats.mana = numberFrom(change.payload.mana);
                        continue;
                    }

                    if (typeof change.payload?.manaGained !== 'undefined') {
                        stats.mana = numberFrom(change.payload.manaGained);
                        continue;
                    }

                    stats.mana += numberFrom(change.payload?.amount);
                    continue;
                }

                if (change?.name === 'player.manaDecreased') {
                    applySignedStatChange(change.payload, 'mana', true);
                    continue;
                }

                if (
                    change?.name === 'player.stabilityChanged'
                    || change?.name === 'player.stabilityGained'
                    || change?.name === 'player.stabilityLost'
                    || change?.name === 'player.stabilityIncreased'
                    || change?.name === 'player.stabilityDecreased'
                ) {
                    const decreasesStability = change?.name === 'player.stabilityLost' || change?.name === 'player.stabilityDecreased';
                    applySignedStatChange(change.payload, 'stability', decreasesStability);
                    continue;
                }

                if (
                    change?.name === 'player.healthChanged'
                    || change?.name === 'player.healthGained'
                    || change?.name === 'player.healthLost'
                    || change?.name === 'player.healthIncreased'
                    || change?.name === 'player.healthDecreased'
                ) {
                    const previousHealth = numberFrom(stats.health);
                    const decreasesHealth = change?.name === 'player.healthLost' || change?.name === 'player.healthDecreased';
                    applySignedStatChange(change.payload, 'health', decreasesHealth);

                    if (numberFrom(stats.health) < previousHealth) {
                        triggerDamageFlash();
                    }
                }
            }
        }

        function showDungeonMessage(text) {
            if (!dungeonMessage || !text) {
                return;
            }

            clearTimeout(messageHideTimeout);
            dungeonMessage.textContent = text;
            dungeonMessage.classList.add('is-visible');

            messageHideTimeout = setTimeout(() => {
                dungeonMessage.classList.remove('is-visible');
            }, 10000);
        }

        function renderDebugPopup() {
            if (!debugPopup || !new URLSearchParams(window.location.search).has('debug')) {
                return;
            }

            debugPopup.style.display = 'block';

            debugPopup.innerHTML = '';

            const header = document.createElement('div');
            header.className = 'debug-header';
            header.textContent = `version: ${dungeonVersion ?? 'n/a'}`;
            debugPopup.appendChild(header);

            const playerCoordinates = document.createElement('div');
            const playerX = Number.isFinite(playerPosition?.x) ? playerPosition.x : 'n/a';
            const playerY = Number.isFinite(playerPosition?.y) ? playerPosition.y : 'n/a';
            playerCoordinates.textContent = `player: (${playerX}, ${playerY})`;
            debugPopup.appendChild(playerCoordinates);

            if (!Array.isArray(latestChanges) || latestChanges.length === 0) {
                const empty = document.createElement('div');
                empty.textContent = 'No changes';
                debugPopup.appendChild(empty);
                return;
            }

            for (const change of latestChanges) {
                const item = document.createElement('div');
                item.className = 'debug-change';

                const toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.className = 'debug-change-toggle';
                toggle.textContent = change?.name ?? 'unknown';

                const payload = document.createElement('pre');
                payload.className = 'debug-change-payload';
                payload.textContent = JSON.stringify(change?.payload ?? null, null, 2);

                toggle.addEventListener('click', () => {
                    item.classList.toggle('is-open');
                });

                item.appendChild(toggle);
                item.appendChild(payload);
                debugPopup.appendChild(item);
            }
        }

        function getStatColor(statKey, ratio, current) {
            const r = Math.max(0, Math.min(1, ratio));

            if (statKey === 'health') {
                if (r <= 0.25) return '#ef4444';
                if (r <= 0.5)  return '#facc15';
                if (r <= 0.75) return '#86efac';
                return '#4ade80';
            }

            if (statKey === 'stability') {
                if (r <= 0.25) return '#ef4444';
                if (r <= 0.5)  return '#f97316';
                if (r <= 0.75) return '#fdba74';
                return '#fb923c';
            }

            if (statKey === 'mana') {
                if (current <= 20) return '#e5e7eb';
                if (r >= 0.75) return '#38bdf8';
                if (r >= 0.5)  return '#60a5fa';
                return '#93c5fd';
            }

            return null;
        }

        function applyStatColor(element, statKey, current, max) {
            if (!element) {
                return;
            }

            const ratio = max > 0 ? current / max : 0;
            const color = getStatColor(statKey, ratio, current);
            const stat = element.closest('.bottom-notch-stat');

            if (stat) {
                stat.style.setProperty('--stat-color', color ?? '#e5e7eb');
                const isCritical = (statKey === 'health' || statKey === 'stability') && ratio <= 0.25;
                stat.classList.toggle('stat-critical', isCritical);
            }

            if (statKey === 'mana' && stat) {
                stat.style.filter = current > 125
                    ? 'drop-shadow(0 0 6px rgba(56, 189, 248, 0.75)) drop-shadow(0 0 16px rgba(56, 189, 248, 0.35))'
                    : '';
            }
        }

        function renderCurrentMaxCounter(element, current, max) {
            if (!element) {
                return;
            }

            const safeMax = Number.isFinite(max) ? max : 0;
            element.textContent = String(current);

            const maxSpan = document.createElement('span');
            maxSpan.className = 'bottom-notch-max';
            maxSpan.textContent = `/ ${safeMax}`;
            element.appendChild(maxSpan);
        }

        function renderCounters() {
            if (counters.coins) {
                counters.coins.textContent = String(stats.coins);
                counters.coins.closest('.bottom-notch-stat').style.display = stats.coins > 0 ? '' : 'none';
            }

            if (counters.shards) {
                counters.shards.textContent = String(stats.shards);
                counters.shards.closest('.bottom-notch-stat').style.display = stats.shards > 0 ? '' : 'none';
            }

            if (counters.victoryPoints) {
                counters.victoryPoints.textContent = String(stats.victoryPoints);
                counters.victoryPoints.closest('.bottom-notch-stat').style.display = stats.victoryPoints > 0 ? '' : 'none';
            }

            renderCurrentMaxCounter(counters.health, stats.health, stats.maxHealth);
            applyStatColor(counters.health, 'health', stats.health, stats.maxHealth);

            renderCurrentMaxCounter(counters.mana, stats.mana, stats.maxMana);
            applyStatColor(counters.mana, 'mana', stats.mana, stats.maxMana);

            renderCurrentMaxCounter(counters.stability, stats.stability, stats.maxStability);
            applyStatColor(counters.stability, 'stability', stats.stability, stats.maxStability);
        }

        async function movePlayer(direction) {
            if (state.moveInFlight || isGameBlocked()) {
                return;
            }

            state.moveInFlight = true;
            updateExitDungeonButton();
            const positionBeforeMove = playerPosition ? { ...playerPosition } : null;

            try {
                const response = await fetch('/dungeon/move', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ direction }),
                });

                if (!response.ok) {
                    draw();
                    return;
                }

                const moveResult = await response.json();
                dungeonVersion = moveResult.version ?? dungeonVersion;
                latestChanges = Array.isArray(moveResult.changes) ? moveResult.changes : [];
                applyChanges(moveResult.changes);
                const playerMoved = playerPosition !== null && (
                    positionBeforeMove === null ||
                    playerPosition.x !== positionBeforeMove.x ||
                    playerPosition.y !== positionBeforeMove.y
                );
                if (playerMoved && positionBeforeMove) {
                    startPlayerMoveAnimationFromTo(positionBeforeMove, playerPosition);
                }
                render();
                renderDebugPopup();
                renderCounters();
                renderCardSlots();
                renderHand();
            } finally {
                state.moveInFlight = false;
                updateExitDungeonButton();
            }
        }

        async function playCard(cardId) {
            if (state.moveInFlight || isGameBlocked()) {
                return;
            }

            state.moveInFlight = true;
            updateExitDungeonButton();

            try {
                const response = await fetch('/dungeon/play-card', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ card: cardId }),
                });

                if (!response.ok) {
                    return;
                }

                const playResult = await response.json();
                dungeonVersion = playResult.version ?? dungeonVersion;
                latestChanges = Array.isArray(playResult.changes) ? playResult.changes : [];
                applyChanges(playResult.changes);
                render();
                renderDebugPopup();
                renderCounters();
                renderCardSlots();
                renderHand();
                nudgeCameraToPlayer();
            } finally {
                state.moveInFlight = false;
                updateExitDungeonButton();
            }
        }

        async function interactWithTile(point) {
            if (state.moveInFlight || isGameBlocked()) {
                return;
            }

            const tile = findTileByPoint(point);

            if (!isTileWithinVisibility(tile)) {
                return;
            }

            state.moveInFlight = true;
            updateExitDungeonButton();

            try {
                const response = await fetch('/dungeon/interact-with-tile', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        x: point.x,
                        y: point.y,
                    }),
                });

                if (!response.ok) {
                    return;
                }

                const interactResult = await response.json();
                dungeonVersion = interactResult.version ?? dungeonVersion;
                latestChanges = Array.isArray(interactResult.changes) ? interactResult.changes : [];
                applyChanges(interactResult.changes);
                render();
                renderDebugPopup();
                renderCounters();
                renderCardSlots();
                renderHand();
                nudgeCameraToPlayer();
            } finally {
                state.moveInFlight = false;
                updateExitDungeonButton();
            }
        }

        async function exitDungeon() {
            if (state.moveInFlight || isGameBlocked()) {
                return;
            }

            state.moveInFlight = true;
            updateExitDungeonButton();

            try {
                const response = await fetch('/dungeon/exit', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                if (response.redirected) {
                    window.location.assign(response.url);
                    return;
                }

                if (!response.ok) {
                    return;
                }

                const contentType = response.headers.get('content-type') ?? '';

                if (!contentType.includes('application/json')) {
                    return;
                }

                const exitResult = await response.json();
                dungeonVersion = exitResult.version ?? dungeonVersion;
                latestChanges = Array.isArray(exitResult.changes) ? exitResult.changes : [];
                applyChanges(exitResult.changes);
                render();
                renderDebugPopup();
                renderCounters();
                renderCardSlots();
                renderHand();
            } finally {
                state.moveInFlight = false;
                updateExitDungeonButton();
            }
        }

        function getDirectionForKey(key) {
            if (key === 'ArrowLeft' || key === 'a' || key === 'A') {
                return 'left';
            }

            if (key === 'ArrowRight' || key === 'd' || key === 'D') {
                return 'right';
            }

            if (key === 'ArrowUp' || key === 'w' || key === 'W') {
                return 'top';
            }

            if (key === 'ArrowDown' || key === 's' || key === 'S') {
                return 'bottom';
            }

            return null;
        }

        function focusOnPlayer() {
            if (isGameBlocked() || !playerPosition) {
                return;
            }

            const targetScale = Math.min(state.maxScale, Math.max(state.minScale, 3));
            state.scale = targetScale;
            render();

            centerViewportOnPoint(playerPosition);
        }

        const playerAnim = {
            active: false,
            held: false,
            forward: true,
            from: null,
            to: null,
            visualFrom: null,
            startTime: null,
            duration: 200,
        };

        const directionOffsets = {
            top:    { x:  0, y: -1 },
            bottom: { x:  0, y:  1 },
            left:   { x: -1, y:  0 },
            right:  { x:  1, y:  0 },
        };

        function getVisualPlayerPosition() {
            if (!playerAnim.active && !playerAnim.held) {
                return playerPosition;
            }

            if (playerAnim.held) {
                return playerAnim.forward ? playerAnim.to : playerAnim.from;
            }

            const elapsed = performance.now() - playerAnim.startTime;
            const t = Math.min(1, elapsed / playerAnim.duration);
            const eased = 1 - Math.pow(1 - t, 2); // ease-out quad

            const from = playerAnim.forward ? playerAnim.from : playerAnim.visualFrom;
            const to   = playerAnim.forward ? playerAnim.to   : playerAnim.from;

            return {
                x: from.x + (to.x - from.x) * eased,
                y: from.y + (to.y - from.y) * eased,
            };
        }

        function tickPlayerAnimation(timestamp) {
            if (!playerAnim.active) {
                return;
            }

            const elapsed = timestamp - playerAnim.startTime;

            if (elapsed >= playerAnim.duration) {
                playerAnim.active = false;
                playerAnim.held = true;
                draw();
                nudgeCameraToPlayer();
                return;
            }

            draw();
            nudgeCameraToPlayer();
            requestAnimationFrame(tickPlayerAnimation);
        }

        function startPlayerMoveAnimationFromTo(from, to) {
            playerAnim.active = false;
            playerAnim.held = false;
            playerAnim.active = true;
            playerAnim.forward = true;
            playerAnim.from = { ...from };
            playerAnim.to = { ...to };
            playerAnim.startTime = performance.now();

            requestAnimationFrame(tickPlayerAnimation);
        }

        function startPlayerMoveAnimation(direction) {
            if (!playerPosition) {
                return;
            }

            const offset = directionOffsets[direction];

            if (!offset) {
                return;
            }

            playerAnim.active = false;
            playerAnim.held = false;
            playerAnim.active = true;
            playerAnim.forward = true;
            playerAnim.from = { ...playerPosition };
            playerAnim.to = { x: playerPosition.x + offset.x, y: playerPosition.y + offset.y };
            playerAnim.startTime = performance.now();

            requestAnimationFrame(tickPlayerAnimation);
        }

        function confirmPlayerAnimation(playerMoved) {
            if (playerMoved) {
                playerAnim.active = false;
                playerAnim.held = false;
                draw();
            } else {
                const currentVisual = getVisualPlayerPosition() ?? playerPosition;
                playerAnim.active = true;
                playerAnim.held = false;
                playerAnim.forward = false;
                playerAnim.visualFrom = { ...currentVisual };
                playerAnim.startTime = performance.now();
                requestAnimationFrame(tickPlayerAnimation);
            }
        }

        function nudgeCameraToPlayer() {
            const pos = getVisualPlayerPosition() ?? playerPosition;
            if (!pos) {
                return;
            }

            const step = getStepSize();
            const tileSize = state.baseTileSize * state.scale;
            const playerPixelX = state.paddingX + (pos.x - bounds.minX) * step + (tileSize / 2);
            const playerPixelY = state.paddingY + (pos.y - bounds.minY) * step + (tileSize / 2);

            const topNotchHeight = document.querySelector('.bottom-notch')?.offsetHeight ?? 0;
            const bottomNotchHeight = handNotch?.offsetHeight ?? 0;
            const margin = step * 3;

            const viewLeft = viewport.scrollLeft;
            const viewTop = viewport.scrollTop + topNotchHeight;
            const viewRight = viewport.scrollLeft + viewport.clientWidth;
            const viewBottom = viewport.scrollTop + viewport.clientHeight - bottomNotchHeight;

            let newScrollLeft = viewport.scrollLeft;
            let newScrollTop = viewport.scrollTop;

            if (playerPixelX - margin < viewLeft) {
                newScrollLeft = playerPixelX - margin;
            } else if (playerPixelX + margin > viewRight) {
                newScrollLeft = playerPixelX + margin - viewport.clientWidth;
            }

            if (playerPixelY - margin < viewTop) {
                newScrollTop = playerPixelY - margin - topNotchHeight;
            } else if (playerPixelY + margin > viewBottom) {
                newScrollTop = playerPixelY + margin + bottomNotchHeight - viewport.clientHeight;
            }

            viewport.scrollLeft = newScrollLeft;
            viewport.scrollTop = newScrollTop;
        }

        function centerViewportOnPoint(point) {
            if (!point) {
                return;
            }

            const step = getStepSize();
            const tileSize = state.baseTileSize * state.scale;
            const centerX = state.paddingX + ((point.x - bounds.minX) * step) + (tileSize / 2);
            const centerY = state.paddingY + ((point.y - bounds.minY) * step) + (tileSize / 2);

            const topNotchHeight = document.querySelector('.bottom-notch')?.offsetHeight ?? 0;
            const bottomNotchHeight = handNotch?.offsetHeight ?? 0;
            const availableHeight = viewport.clientHeight - topNotchHeight - bottomNotchHeight;

            viewport.scrollLeft = centerX - (viewport.clientWidth / 2);
            viewport.scrollTop = centerY - topNotchHeight - (availableHeight / 2);
        }

        function preloadSprites() {
            const wallPromises = Object.entries(wallSpritePaths).map(async ([direction, src]) => {
                const image = await loadImage(src);

                if (image) {
                    wallSprites[direction] = image;
                }
            });

            const floorPromise = loadImage(floorSpritePath).then((image) => {
                floorSprite = image;
            });

            const floorOriginPromise = loadImage(floorOriginSpritePath).then((image) => {
                floorOriginSprite = image;
            });

            const floorSupportPromise = loadImage(floorSupportSpritePath).then((image) => {
                floorSupportSprite = image;
            });

            const floorHealthAltarPromise = loadImage(floorHealthAltarSpritePath).then((image) => {
                floorHealthAltarSprite = image;
            });

            const floorManaAltarPromise = loadImage(floorManaAltarSpritePath).then((image) => {
                floorManaAltarSprite = image;
            });

            const floorStabilityAltarPromise = loadImage(floorStabilityAltarSpritePath).then((image) => {
                floorStabilityAltarSprite = image;
            });

            const floorHealthAltarCooldownPromise = loadImage(floorHealthAltarCooldownSpritePath).then((image) => {
                floorHealthAltarCooldownSprite = image;
            });

            const floorManaAltarCooldownPromise = loadImage(floorManaAltarCooldownSpritePath).then((image) => {
                floorManaAltarCooldownSprite = image;
            });

            const floorStabilityAltarCooldownPromise = loadImage(floorStabilityAltarCooldownSpritePath).then((image) => {
                floorStabilityAltarCooldownSprite = image;
            });

            const floorVictoryPointPromise = loadImage(floorVictoryPointSpritePath).then((image) => {
                floorVictoryPointSprite = image;
            });

            const floorShardPromise = loadImage(floorShardSpritePath).then((image) => {
                floorShardSprite = image;
            });

            const floorCollapsedPromise = loadImage(floorCollapsedSpritePath).then((image) => {
                floorCollapsedSprite = image;
            });

            const floorLakePromise = loadImage(floorLakeSpritePath).then((image) => {
                floorLakeSprite = image;
            });

            const floorLake1Promise = loadImage(floorLake1SpritePath).then((image) => {
                floorLake1Sprite = image;
            });

            const floorLake2Promise = loadImage(floorLake2SpritePath).then((image) => {
                floorLake2Sprite = image;
            });

            const floorLake3Promise = loadImage(floorLake3SpritePath).then((image) => {
                floorLake3Sprite = image;
            });

            const playerPromise = loadImage(playerSpritePath).then((image) => {
                playerSprite = image;
            });

            const dwellerPromise = loadImage(dwellerSpritePath).then(async (image) => {
                if (image) {
                    dwellerSprite = image;
                    return;
                }

                dwellerSprite = await loadImage(dwellerFallbackSpritePath);
            });

            const coinMarkerPromise = loadImage(coinMarkerSpritePath).then((image) => {
                coinMarkerSprite = image;
            });

            const artifactMarkerPromise = loadImage(artifactMarkerSpritePath).then((image) => {
                artifactMarkerSprite = image;
            });

            Promise.all([
                ...wallPromises,
                floorPromise,
                floorOriginPromise,
                floorSupportPromise,
                floorHealthAltarPromise,
                floorManaAltarPromise,
                floorStabilityAltarPromise,
                floorHealthAltarCooldownPromise,
                floorManaAltarCooldownPromise,
                floorStabilityAltarCooldownPromise,
                floorVictoryPointPromise,
                floorShardPromise,
                floorCollapsedPromise,
                floorLakePromise,
                floorLake1Promise,
                floorLake2Promise,
                floorLake3Promise,
                playerPromise,
                dwellerPromise,
                coinMarkerPromise,
                artifactMarkerPromise,
            ]).then(() => {
                render();
            });
        }

        function render() {
            const step = getStepSize();
            viewport.scrollLeft += (lastRenderedMinX - bounds.minX) * step;
            viewport.scrollTop += (lastRenderedMinY - bounds.minY) * step;
            lastRenderedMinX = bounds.minX;
            lastRenderedMinY = bounds.minY;
            resizeCanvas();
            draw();
            updateViewportCursorState();
            updateExitDungeonButton();
            updateDeathOverlay();
            updateExitedOverlay();
            updateResignedOverlay();
        }

        viewport.addEventListener('wheel', (event) => {
            const direction = event.deltaY > 0 ? -1 : 1;
            const previousScale = state.scale;
            const nextScale = Math.min(state.maxScale, Math.max(state.minScale, state.scale + (direction * 0.1)));

            if (nextScale === previousScale) {
                return;
            }

            event.preventDefault();

            const rect = viewport.getBoundingClientRect();
            const cursorX = event.clientX - rect.left;
            const cursorY = event.clientY - rect.top;
            const previousStep = getStepSize();
            const previousPaddingX = state.paddingX;
            const previousPaddingY = state.paddingY;

            const worldX = (viewport.scrollLeft + cursorX - previousPaddingX) / previousStep;
            const worldY = (viewport.scrollTop + cursorY - previousPaddingY) / previousStep;

            state.scale = nextScale;
            render();

            const nextStep = getStepSize();
            const contentX = state.paddingX + (worldX * nextStep);
            const contentY = state.paddingY + (worldY * nextStep);

            viewport.scrollLeft = contentX - cursorX;
            viewport.scrollTop = contentY - cursorY;
        }, { passive: false });

        viewport.addEventListener('scroll', () => {
            drawArtifactDirectionGlow(getStepSize(), state.baseTileSize * state.scale);
            updateExitDungeonButton();
        });

        let exitConfirmPending = false;
        let exitConfirmTimer = null;

        function resetExitButton() {
            exitConfirmPending = false;
            clearTimeout(exitConfirmTimer);
            exitConfirmTimer = null;

            if (exitDungeonButton) {
                exitDungeonButton.textContent = 'Exit';
                exitDungeonButton.classList.remove('is-confirming');
            }
        }

        exitDungeonButton?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (!exitConfirmPending) {
                exitConfirmPending = true;
                exitDungeonButton.textContent = 'Sure?';
                exitDungeonButton.classList.add('is-confirming');
                exitConfirmTimer = setTimeout(resetExitButton, 3000);
            } else {
                resetExitButton();
                exitDungeon();
            }
        });

        document.addEventListener('click', (event) => {
            if (exitConfirmPending && event.target !== exitDungeonButton) {
                resetExitButton();
            }
        });

        deathOverlayExitButton?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            window.location.assign('/dungeon');
        });

        exitedOverlayExitButton?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            window.location.assign('/dungeon');
        });

        resignedOverlayExitButton?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            window.location.assign('/dungeon');
        });

        let resignConfirmPending = false;
        let resignConfirmTimer = null;

        function resetResignButton() {
            resignConfirmPending = false;
            clearTimeout(resignConfirmTimer);
            resignConfirmTimer = null;

            if (resignButton) {
                resignButton.textContent = 'Resign';
                resignButton.classList.remove('is-confirming');
            }
        }

        resignButton?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (!resignConfirmPending) {
                resignConfirmPending = true;
                resignButton.textContent = 'Sure?';
                resignButton.classList.add('is-confirming');
                resignConfirmTimer = setTimeout(resetResignButton, 3000);
            } else {
                resetResignButton();
                resign();
            }
        });

        document.addEventListener('click', (event) => {
            if (resignConfirmPending && event.target !== resignButton) {
                resetResignButton();
            }
        });

        viewport.addEventListener('mousedown', (event) => {
            if (event.button !== 0) {
                return;
            }

            state.isDragging = true;
            state.suppressTileClick = false;
            state.dragStartX = event.clientX;
            state.dragStartY = event.clientY;
            state.scrollStartLeft = viewport.scrollLeft;
            state.scrollStartTop = viewport.scrollTop;
            viewport.classList.add('is-dragging');
        });

        viewport.addEventListener('mousemove', (event) => {
            if (!isTileInteractionEnabled()) {
                if (state.hoveredTileKey !== null) {
                    clearHoveredTile();
                }
                return;
            }

            const tile = getTileAtViewportPosition(event.clientX, event.clientY);
            setHoveredTile(isTileWithinVisibility(tile) ? tile : null);
        });

        viewport.addEventListener('click', (event) => {
            if (event.button !== 0 || !isTileInteractionEnabled()) {
                return;
            }

            if (state.suppressTileClick) {
                state.suppressTileClick = false;
                return;
            }

            const tile = getTileAtViewportPosition(event.clientX, event.clientY);

            if (!isTileWithinVisibility(tile)) {
                return;
            }

            interactWithTile(tile.point);
        });

        window.addEventListener('mousemove', (event) => {
            if (!state.isDragging) {
                return;
            }

            const deltaX = event.clientX - state.dragStartX;
            const deltaY = event.clientY - state.dragStartY;

            if (Math.abs(deltaX) > 4 || Math.abs(deltaY) > 4) {
                state.suppressTileClick = true;
            }

            viewport.scrollLeft = state.scrollStartLeft - deltaX;
            viewport.scrollTop = state.scrollStartTop - deltaY;
        });

        window.addEventListener('mouseup', () => {
            if (!state.isDragging) {
                return;
            }

            state.isDragging = false;
            viewport.classList.remove('is-dragging');
        });

        viewport.addEventListener('mouseleave', () => {
            if (state.hoveredTileKey !== null) {
                clearHoveredTile();
            }

            if (!state.isDragging) {
                return;
            }

            state.isDragging = false;
            viewport.classList.remove('is-dragging');
        });

        window.addEventListener('keydown', (event) => {
            if (isGameBlocked()) {
                return;
            }

            if (event.code === 'Space') {
                event.preventDefault();
                focusOnPlayer();
                return;
            }

            if (event.code === 'KeyQ' && playerPosition && isTileInteractionEnabled()) {
                event.preventDefault();
                interactWithTile(playerPosition);
                return;
            }

            const direction = getDirectionForKey(event.key);

            if (!direction) {
                return;
            }

            event.preventDefault();
            movePlayer(direction);
        });

        // Toggle hand visibility
        toggleHandButton?.addEventListener('click', (event) => {
            event.stopPropagation();
            handNotch?.classList.toggle('is-collapsed');
            requestAnimationFrame(() => requestAnimationFrame(() => centerViewportOnPoint(playerPosition)));
        });

        // Touch: swipe to move, drag to pan, pinch to zoom
        let touchStartX = null;
        let touchStartY = null;
        let touchStartTime = null;
        let pinchStartDistance = null;
        let pinchStartScale = null;
        const SWIPE_MIN_DISTANCE = 40;
        const SWIPE_MAX_TIME = 350;

        viewport.addEventListener('touchstart', (event) => {
            if (event.touches.length === 2) {
                const dx = event.touches[0].clientX - event.touches[1].clientX;
                const dy = event.touches[0].clientY - event.touches[1].clientY;
                pinchStartDistance = Math.hypot(dx, dy);
                pinchStartScale = state.scale;
                return;
            }

            if (event.touches.length !== 1) {
                return;
            }

            touchStartX = event.touches[0].clientX;
            touchStartY = event.touches[0].clientY;
            touchStartTime = Date.now();
        }, { passive: true });

        viewport.addEventListener('touchmove', (event) => {
            event.preventDefault();

            if (event.touches.length === 2 && pinchStartDistance !== null) {
                const dx = event.touches[0].clientX - event.touches[1].clientX;
                const dy = event.touches[0].clientY - event.touches[1].clientY;
                const distance = Math.hypot(dx, dy);
                const ratio = distance / pinchStartDistance;
                state.scale = Math.min(state.maxScale, Math.max(state.minScale, pinchStartScale * ratio));
                render();
                if (playerPosition) {
                    centerViewportOnPoint(playerPosition);
                }
            }
        }, { passive: false });

        viewport.addEventListener('touchend', (event) => {
            const wasPinching = pinchStartDistance !== null;
            pinchStartDistance = null;
            pinchStartScale = null;

            if (wasPinching) {
                if (playerPosition) {
                    centerViewportOnPoint(playerPosition);
                }
                return;
            }

            if (touchStartX === null || event.changedTouches.length !== 1) {
                return;
            }

            const touch = event.changedTouches[0];
            const dx = touch.clientX - touchStartX;
            const dy = touch.clientY - touchStartY;
            const elapsed = Date.now() - touchStartTime;
            const absDx = Math.abs(dx);
            const absDy = Math.abs(dy);

            touchStartX = null;
            touchStartY = null;
            touchStartTime = null;

            if (elapsed <= SWIPE_MAX_TIME && Math.max(absDx, absDy) >= SWIPE_MIN_DISTANCE) {
                const direction = absDx > absDy
                    ? (dx > 0 ? 'right' : 'left')
                    : (dy > 0 ? 'bottom' : 'top');
                movePlayer(direction);
            }
        }, { passive: true });

        hydrateFromPayload(payload);
        lastRenderedMinX = bounds.minX;
        lastRenderedMinY = bounds.minY;
        render();
        centerViewportOnPoint(playerPosition);
        renderDebugPopup();
        renderCounters();
        renderCardSlots();
        renderHand();
        preloadSprites();

        if (document.fonts?.ready) {
            document.fonts.ready.then(() => {
                render();
            });
        }
    </script>
</body>
</html>
