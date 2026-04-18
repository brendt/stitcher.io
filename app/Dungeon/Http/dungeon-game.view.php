<html lang="en">
<head>
    <title>Dungeon</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fontdiner+Swanky&display=swap" rel="stylesheet">
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
            background: #27282e;
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
            position: fixed;
            z-index: 2100;
            top: 12px;
            right: 12px;
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
            left: 0;
            bottom: 0;
            transform: none;
            z-index: 900;
            width: 100vw;
            border-bottom: none;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 4px 12px 10px;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            background: rgba(9, 10, 15, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: #e5e7eb;
            font-family: ui-sans-serif, system-ui, sans-serif;
            pointer-events: auto;
            box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.35);
        }

        .hand-notch.is-collapsed {
            padding-bottom: 4px;
        }

        .hand-notch.is-collapsed .hand-layout {
            display: none;
        }

        .toggle-hand-button {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 4px;
            padding: 5px 18px;
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
            width: 14px;
            height: 14px;
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

        .card-slots-corner {
            position: fixed;
            top: 70px;
            left: 8px;
            z-index: 1000;
            pointer-events: auto;
        }

        .hand-side-slots {
            display: flex;
            flex-direction: row;
            gap: 6px;
        }

        @media (pointer: coarse) {
            .card-slots-corner .hand-slot-empty {
                width: 52px;
                min-width: 52px;
                height: 72px;
            }

            .card-slots-corner .hand-slot-empty svg {
                width: 16px;
                height: 16px;
            }

            .card-slots-corner .hand-card-small {
                width: 52px;
                min-width: 52px;
            }

            .card-slots-corner .hand-card-small .hand-card-image {
                height: 72px;
            }

            .card-slots-corner .hand-card-small .hand-card-name {
                display: none;
            }

            .card-slots-corner .hand-card-small .hand-card-content {
                display: none;
            }

            .card-slots-corner .hand-card-small .hand-card-mana {
                font-size: 9px;
                min-width: 16px;
                padding: 2px 3px;
                top: 3px;
                right: 3px;
            }

            .card-slots-corner .hand-card-small .hand-card-type {
                top: 3px;
                left: 3px;
                min-width: 16px;
                min-height: 16px;
                padding: 2px;
            }

            .card-slots-corner .hand-card-small .hand-card-type svg {
                width: 9px;
                height: 9px;
            }
        }

        .hand-side-slot {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: center;
        }

        .hand-cards {
            display: flex;
            gap: 20px;
            align-items: stretch;
            justify-content: center;
            padding-bottom: 2px;
        }

        .hand-empty {
            opacity: 0.65;
            font-size: 13px;
            text-align: center;
            width: 100%;
            padding: 10px 0 8px;
        }

        .hand-card {
            width: 200px;
            min-width: 200px;
            border-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            overflow: hidden;
            position: relative;
            cursor: pointer;
            --card-accent: rgba(255, 255, 255, 0.42);
            box-shadow: 0 0 0 1px color-mix(in srgb, var(--card-accent) 55%, transparent), 0 0 22px color-mix(in srgb, var(--card-accent) 32%, transparent);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .hand-card:not(.hand-card-unplayable):hover {
            transform: translateY(-4px) scale(1.015);
            box-shadow: 0 0 0 1px color-mix(in srgb, var(--card-accent) 80%, transparent), 0 8px 32px color-mix(in srgb, var(--card-accent) 45%, transparent);
        }

        .hand-card-small {
            width: 145px;
            min-width: 145px;
        }

        .hand-card-small .hand-card-image {
            height: 165px;
        }

        .hand-card-small .hand-card-name {
            font-size: 12px;
        }

        .hand-slot-empty {
            width: 145px;
            min-width: 145px;
            height: 165px;
            border-radius: 5px;
            border: 1px dashed rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.04);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(229, 231, 235, 0.75);
        }

        .hand-slot-empty svg {
            width: 26px;
            height: 26px;
        }

        .hand-card-rarity-common {
            --card-accent: rgba(255, 255, 255, 0.42);
        }

        .hand-card-rarity-rare {
            --card-accent: rgba(45, 175, 255, 0.75);
        }

        .hand-card-rarity-epic {
            --card-accent: rgba(189, 120, 255, 0.82);
        }

        .hand-card-rarity-meta {
            --card-accent: rgba(255, 190, 64, 0.86);
        }

        .hand-card-unplayable {
            cursor: not-allowed;
        }

        .hand-card-unplayable .hand-card-image {
            filter: grayscale(1);
        }

        .hand-card-mana {
            position: absolute;
            top: 8px;
            right: 8px;
            min-width: 36px;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid color-mix(in srgb, var(--card-accent) 70%, white 30%);
            background: color-mix(in srgb, var(--card-accent) 78%, black 22%);
            color: #f8fafc;
            font-family: var(--font-title), ui-serif, serif;
            font-size: 13px;
            line-height: 1;
            text-align: center;
            font-weight: 700;
            letter-spacing: 0.02em;
            z-index: 2;
            pointer-events: none;
        }

        .hand-card-type {
            position: absolute;
            top: 8px;
            left: 8px;
            min-width: 36px;
            min-height: 25px;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid color-mix(in srgb, var(--card-accent) 70%, white 30%);
            background: color-mix(in srgb, var(--card-accent) 78%, black 22%);
            color: #f8fafc;
            z-index: 2;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hand-card-type svg {
            width: 14px;
            height: 14px;
        }

        .hand-card-image {
            width: 100%;
            height: 230px;
            object-fit: cover;
            display: block;
        }

        .hand-card-content {
            padding: 8px 10px 10px;
            background: color-mix(in srgb, var(--card-accent) 68%, black 32%);
            border: 1px solid color-mix(in srgb, var(--card-accent) 72%, white 28%);
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            margin: 5px;
            border-radius: 5px;
            pointer-events: none;
        }

        .hand-card-name {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
            padding-top: 2px;
            text-align: center;
            font-family: var(--font-title);
        }

        .hand-card-description {
            display: none;
            margin-top: 4px;
            font-size: 11px;
            line-height: 1.3;
            text-align: center;
        }

        .hand-card:hover .hand-card-description {
            display: block;
        }

        @media (pointer: coarse) {
            .bottom-notch {
                min-width: unset;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border-left: none;
                border-right: none;
                border-radius: 0;
                flex-wrap: nowrap;
                justify-content: flex-start;
                transform: none;
                left: 0;
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
                max-height: 52vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            .hand-cards {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                justify-content: flex-start;
                padding: 0 16px 2px;
            }

            .hand-card {
                width: 130px;
                min-width: 130px;
            }

            .hand-card-image {
                height: 148px;
            }

            .hand-card-name {
                font-size: 11px;
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
    <button id="resign-button" class="resign-button" type="button">Resign</button>
    <div class="bottom-notch">
        <div class="bottom-notch-stat">
            <div id="health-label" class="bottom-notch-label">Health</div>
            <div id="health-counter" class="bottom-notch-value">0</div>
        </div>
        <div class="bottom-notch-stat">
            <div class="bottom-notch-label">Mana</div>
            <div id="mana-counter" class="bottom-notch-value">0</div>
        </div>
        <div class="bottom-notch-stat">
            <div class="bottom-notch-label">Stability</div>
            <div id="stability-counter" class="bottom-notch-value">0</div>
        </div>
        <div class="bottom-notch-stat">
            <div id="coin-label" class="bottom-notch-label">Coins</div>
            <div id="coin-counter" class="bottom-notch-value">0</div>
        </div>
        <div class="bottom-notch-stat">
            <div id="shard-label" class="bottom-notch-label">Shards</div>
            <div id="shard-counter" class="bottom-notch-value">0</div>
        </div>
        <div class="bottom-notch-stat">
            <div id="victory-point-label" class="bottom-notch-label">Victory Points</div>
            <div id="victory-point-counter" class="bottom-notch-value">0</div>
        </div>
    </div>
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
            <div id="hand-cards" class="hand-cards"></div>
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
        const activeCardSlot = document.getElementById('active-card-slot');
        const passiveCardSlot = document.getElementById('passive-card-slot');
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
        let activeCard = null;
        let passiveCard = null;
        let playerPosition = null;
        let artifactLocation = null;
        let visibilityRadius = null;
        let dungeonVersion = null;
        let isPlayerDead = false;
        let hasPlayerExited = false;
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
        const playerSpritePath = '/dungeon/player-avatar.png';
        const dwellerSpritePath = '/dungeon/dweller-avater.png';
        const dwellerFallbackSpritePath = '/dungeon/dweller-avatar.png';
        const coinMarkerSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" style="color:#facc15"><!-- Icon from Remix Icon by Remix Design - https://github.com/Remix-Design/RemixIcon/blob/master/License --><path fill="currentColor" d="M12.005 4.003c6.075 0 11 2.686 11 6v4c0 3.314-4.925 6-11 6c-5.967 0-10.824-2.591-10.995-5.823l-.005-.177v-4c0-3.314 4.925-6 11-6m0 12c-3.72 0-7.01-1.008-9-2.55v.55c0 1.882 3.883 4 9 4c5.01 0 8.838-2.03 8.995-3.882l.005-.118l.001-.55c-1.99 1.542-5.28 2.55-9.001 2.55m0-10c-5.117 0-9 2.118-9 4s3.883 4 9 4s9-2.118 9-4s-3.883-4-9-4"/></svg>`;
        const artifactMarkerSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512" style="color:#a855f7"><path fill="currentColor" d="M54.6 25.88L41.4 38.12l62.5 67.28c3.8-6.87 6.5-11.92 9.1-16.72zm386.6.26l-46.6 54.33c2.4 4.36 5.4 9.94 9.2 17.01l51-59.62zm-272.9 2.32l-16.6 7.08l42.1 98.26c6.4 2.1 13.6 3.8 21.8 5.2zm143 1.07l-32 111.57c7.2-.6 13.8-1.5 19.8-2.6l29.6-104.03zM263 47.31L255.7 142h.3c6.3 0 12.2-.2 17.8-.5l7.2-92.81zM129.3 96.47c-6.2 11.73-15.1 28.33-31.76 57.13c-16.63 28.8-26.56 44.8-33.56 56.1c11.59-2.6 27.23-3.5 44.92 3.5c22.8 9 48 30.5 73.4 74.4c25.4 44 31.3 76.6 27.7 100.8c-2.7 18.9-11.4 32-19.4 40.7c13.3-.4 32.1-1 65.4-1c33.2 0 52 .6 65.3 1c-8-8.7-16.6-21.8-19.4-40.7c-3.6-24.2 2.4-56.8 27.8-100.8c25.4-43.9 50.6-65.3 73.4-74.4c17.7-7 33.4-6.1 44.9-3.5c-7-11.3-16.9-27.3-33.5-56.1s-25.5-45.4-31.8-57.08c-3.6 11.28-10.6 25.38-25.6 37.18C338 148.9 306.8 160 256 160s-82-11.2-101.1-26.3c-15-11.9-22-25.9-25.6-37.23m313.5 8.13l-25.3 17.9c2.7 4.8 5.7 10.1 9 15.8l26.7-18.9zM35.03 167.5l-6.06 17l24 8.6c2.77-4.5 6-9.8 9.63-15.8zM256 196a49.98 49.98 0 0 1 50 50a49.98 49.98 0 0 1-50 50a49.98 49.98 0 0 1-50-50a49.98 49.98 0 0 1 50-50m118.9 59.4c-4.6 4.9-9.3 10.6-14.1 17.2l118.6 8.4l1.2-18zm-231.2 7.5L30.73 279.1l2.54 17.8L156 279.4c-4.1-6.2-8.2-11.6-12.3-16.5m18.7 26.4L44.23 343.8l7.54 16.4L171.4 305c-1.5-2.7-3-5.5-4.7-8.4c-1.5-2.5-2.9-5-4.3-7.3m181.3 10.1c-3.1 5.6-5.9 10.9-8.4 16l124 76.3l9.4-15.4zm-166.4 17.3L25.88 457.4l12.24 13.2L184.8 334.4q-3-8.4-7.5-17.7m148.5 21.6q-4.2 12.45-5.7 22.8l88.6 124.1l14.6-10.4zM224.4 446.4c-7 .1-13 .2-18.5.4l-6.7 31.3l17.6 3.8zm77.1.2l9.8 35.8l17.4-4.8l-8.4-30.4c-5.4-.2-11.1-.4-18.8-.6"/></svg>`;
        const trapMarkerPathData = 'm106 113.773l-32.963 74.375a99 99 0 0 0-3.12.704c-5.293 1.296-9.95 2.918-14.044 4.79l-8.266-53.435l-25.037 87.277a108 108 0 0 0-3.338 11.635l-.26.905l.07-.04c-3.632 16.665-3.56 35.726 3.597 55.818c3.306 14.022 15.515 30.355 40.24 48.135c29.193 20.992 75.05 42.954 138.495 63.86a39 39 0 0 1-.393-5.486c0-12.21 5.637-23.185 14.432-30.447l-4.07-42.73l-31.54 37.69a621 621 0 0 1-27.896-11.3l-2.95-78.177l-33.57 60.615c-9.068-4.85-17.496-9.773-25.294-14.75l-4.627-90.04l-28.932 65.057c-7.485-6.607-13.957-13.243-19.45-19.86c-4.244-20.016-.412-38.063 6.145-52.42l4.483-2.602c15.852-5.496 35.514-7.645 58.504-6.182c32.732 2.084 72.51 11.748 118.152 30.803c.098-13.092 7.704-24.51 18.692-30.142l-5.597-52.59l-30.14 42.78c-9.68-3.6-19.025-6.73-28.012-9.41l-4.26-68.73l-32.567 59.774c-11.784-2.163-22.712-3.436-32.716-3.91l-3.77-71.97zm323.08 29.936l-15.973 70.28c-9.928-1.244-20.884-1.876-32.837-1.777l-19.58-66.443l-18.075 68.964c-9.342 1.12-19.127 2.635-29.316 4.55l-19.015-44.84l-16.422 45.742c8.9 6.183 14.768 16.47 14.768 28.04c0 2.407-.257 4.758-.74 7.03c47.224-10.57 87.28-13.166 119.37-9.7c22.9 2.47 41.908 7.938 56.592 16.05l3.978 3.332c4.016 15.265 4.72 33.704-2.873 52.707c-6.54 5.582-14.047 11.016-22.547 16.25l-17.43-69.034l-19.89 87.94c-8.51 3.565-17.626 6.972-27.356 10.198l-19.724-61.576l-19.274 72.674a623 623 0 0 1-29.326 6.37l-22.605-45.43l-14.87 49.995a39.1 39.1 0 0 1 4.02 17.283a39 39 0 0 1-3.476 16.107c70.416-9.85 122.176-24.18 155.893-40.565c27.394-13.31 42.205-27.326 47.852-40.582c10.472-18.58 13.79-37.348 13.048-54.388l.063.053l-.102-.942a108 108 0 0 0-1.308-12.035l-9.81-90.26l-17.243 51.245c-3.714-2.54-8.03-4.93-13.023-7.11c-.96-.417-1.95-.822-2.954-1.222L429.08 143.71zm-170.584 89.07c-8.642 0-15.443 6.802-15.443 15.445c0 3.53 1.15 6.74 3.084 9.318a161 161 0 0 1 23.101 1.844c2.91-2.793 4.705-6.733 4.705-11.162c0-8.64-6.806-15.446-15.447-15.446zm-12.652 43.468q-1.53-.005-3.033.025c-12.016.244-22.59 2.134-30.23 4.98c-5.094 1.9-8.82 4.23-10.85 6.22s-2.375 3.155-2.375 4.37c0 2.426 3.81 8.437 14.258 13.844c10.448 5.408 25.905 9.714 42.992 10.954c17.088 1.24 32.486-.854 42.674-4.65c5.093-1.898 8.82-4.23 10.85-6.22c2.03-1.987 2.374-3.154 2.374-4.368c0-2.43-3.81-8.44-14.258-13.847c-10.447-5.408-25.904-9.712-42.992-10.95a135 135 0 0 0-9.41-.357zm-5.688 57.215l-2.96 29.51c1.08-.09 2.17-.15 3.273-.15c5.382 0 10.524 1.1 15.214 3.077l3.05-30.406a160 160 0 0 1-18.578-2.032zm.313 48.05c-11.6 0-20.798 9.2-20.798 20.8c0 11.595 9.2 20.796 20.797 20.796c11.594 0 20.798-9.203 20.798-20.798s-9.202-20.798-20.8-20.798z';
        const trapMarkerPath = typeof Path2D === 'function' ? new Path2D(trapMarkerPathData) : null;
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

                if (isOutsideVisibility && !isArtifactAtPoint(tile?.point) && !tile?.isCollapsed) {
                    drawVisibilityOverlay(x, y, tileSize);
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

            if (sprite) {
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

        function drawVisibilityOverlay(x, y, tileSize) {
            context.save();
            context.fillStyle = 'rgba(0, 0, 0, 0.68)';
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

            const tileX = state.paddingX + (playerPosition.x - bounds.minX) * step;
            const tileY = state.paddingY + (playerPosition.y - bounds.minY) * step;
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
            if (!exitDungeonButton || isGameBlocked() || !playerPosition || !isPlayerOnOriginTile()) {
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
            mana.textContent = String(card.mana);
            article.appendChild(mana);

            if (options.showTypeBadge && (card.type === 'active' || card.type === 'passive')) {
                const type = document.createElement('div');
                type.className = 'hand-card-type';
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
            svg.setAttribute('fill', 'none');
            svg.setAttribute('viewBox', '0 0 24 24');
            svg.setAttribute('stroke-width', '1.5');
            svg.setAttribute('stroke', 'currentColor');
            svg.setAttribute('aria-hidden', 'true');

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');

            if (slotType === 'active') {
                path.setAttribute('d', 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z');
            } else {
                path.setAttribute('d', 'M4.5 12.75l7.5-7.5 7.5 7.5m-15 6l7.5-7.5 7.5 7.5');
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
                empty.textContent = 'No cards in hand';
                handCards.appendChild(empty);
                return;
            }

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

                if (change?.name === 'player.coinsIncreased') {
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
                    if (typeof change.payload?.maxHealth !== 'undefined') {
                        stats.maxHealth = numberFrom(change.payload.maxHealth);
                        continue;
                    }

                    stats.maxHealth += numberFrom(change.payload?.amount);
                    continue;
                }

                if (change?.name === 'player.maxManaIncreased') {
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
            }

            if (counters.shards) {
                counters.shards.textContent = String(stats.shards);
            }

            if (counters.victoryPoints) {
                counters.victoryPoints.textContent = String(stats.victoryPoints);
            }

            renderCurrentMaxCounter(counters.health, stats.health, stats.maxHealth);
            renderCurrentMaxCounter(counters.mana, stats.mana, stats.maxMana);
            renderCurrentMaxCounter(counters.stability, stats.stability, stats.maxStability);
        }

        async function movePlayer(direction) {
            if (state.moveInFlight || isGameBlocked()) {
                return;
            }

            state.moveInFlight = true;
            updateExitDungeonButton();

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
                    return;
                }

                const moveResult = await response.json();
                dungeonVersion = moveResult.version ?? dungeonVersion;
                latestChanges = Array.isArray(moveResult.changes) ? moveResult.changes : [];
                applyChanges(moveResult.changes);
                render();
                renderDebugPopup();
                renderCounters();
                renderCardSlots();
                renderHand();

                if (isMobile() && playerPosition) {
                    centerViewportOnPoint(playerPosition);
                }
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

        function centerViewportOnPoint(point) {
            if (!point) {
                return;
            }

            const step = getStepSize();
            const tileSize = state.baseTileSize * state.scale;
            const centerX = state.paddingX + ((point.x - bounds.minX) * step) + (tileSize / 2);
            const centerY = state.paddingY + ((point.y - bounds.minY) * step) + (tileSize / 2);

            viewport.scrollLeft = centerX - (viewport.clientWidth / 2);
            viewport.scrollTop = centerY - (viewport.clientHeight / 2);
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
                playerPromise,
                dwellerPromise,
                coinMarkerPromise,
                artifactMarkerPromise,
            ]).then(() => {
                render();
            });
        }

        function render() {
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

        exitDungeonButton?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            exitDungeon();
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
