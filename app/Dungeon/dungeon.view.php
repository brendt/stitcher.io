<html lang="en">
<head>
    <title>Dungeon</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fontdiner+Swanky&display=swap" rel="stylesheet">


    <style>
        :root {
            --tile: #9ca3af;
            --tile-border: #6b7280;
            --font-title: "Fontdiner Swanky", serif;
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

        .debug-popup {
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
            padding: 12px 18px 10px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-top: none;
            border-bottom-left-radius: 14px;
            border-bottom-right-radius: 14px;
            background: rgba(12, 13, 18, 0.9);
            color: #e5e7eb;
            font-family: ui-sans-serif, system-ui, sans-serif;
            pointer-events: none;
            display: flex;
            gap: 24px;
            justify-content: center;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .bottom-notch-stat {
            min-width: 110px;
        }

        .bottom-notch-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            opacity: 0.7;
            font-family: var(--font-title);
        }

        .bottom-notch-value {
            margin-top: 2px;
            font-size: 20px;
            line-height: 1;
            font-weight: 700;
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
            padding: 10px 12px;
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
            color: #e5e7eb;
            font-family: ui-sans-serif, system-ui, sans-serif;
            pointer-events: auto;
        }

        .hand-layout {
            display: block;
            position: relative;
            width: 100%;
        }

        .hand-side-slots {
            display: flex;
            flex-direction: row;
            gap: 12px;
            width: auto;
            position: absolute;
            left: 0;
            bottom: 0;
            transform: none;
            z-index: 901;
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
            filter: grayscale(1) blur(0.6px);
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
    </style>
</head>

<body>
    <div id="viewport" class="viewport">
        <canvas id="dungeon-canvas"></canvas>
    </div>
    <div id="artifact-compass" class="artifact-compass"></div>
    <div class="bottom-notch">
        <div class="bottom-notch-stat">
            <div class="bottom-notch-label">Health</div>
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
            <div class="bottom-notch-label">Coins</div>
            <div id="coin-counter" class="bottom-notch-value">0</div>
        </div>
    </div>
    <div class="hand-notch">
        <div class="hand-layout">
            <div class="hand-side-slots">
                <div class="hand-side-slot">
                    <div id="active-card-slot"></div>
                </div>
                <div class="hand-side-slot">
                    <div id="passive-card-slot"></div>
                </div>
            </div>
            <div id="hand-cards" class="hand-cards"></div>
        </div>
    </div>
    <pre id="debug-popup" class="debug-popup"></pre>

    <script id="dungeon-data" type="application/json">{!! json_encode($dungeon->toArray()) !!}</script>
    <script>
        const dataElement = document.getElementById('dungeon-data');
        const viewport = document.getElementById('viewport');
        const canvas = document.getElementById('dungeon-canvas');
        const artifactCompass = document.getElementById('artifact-compass');
        const debugPopup = document.getElementById('debug-popup');
        const handCards = document.getElementById('hand-cards');
        const activeCardSlot = document.getElementById('active-card-slot');
        const passiveCardSlot = document.getElementById('passive-card-slot');
        const counters = {
            coins: document.getElementById('coin-counter'),
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
        const stats = {
            coins: 0,
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
        const floorCollapsedSpritePath = '/dungeon/tile-collapsed.png';
        const playerSpritePath = '/dungeon/player-avatar.png';
        const dwellerSpritePath = '/dungeon/dweller-avater.png';
        const dwellerFallbackSpritePath = '/dungeon/dweller-avatar.png';
        const coinMarkerSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" style="color:#facc15"><!-- Icon from Remix Icon by Remix Design - https://github.com/Remix-Design/RemixIcon/blob/master/License --><path fill="currentColor" d="M12.005 4.003c6.075 0 11 2.686 11 6v4c0 3.314-4.925 6-11 6c-5.967 0-10.824-2.591-10.995-5.823l-.005-.177v-4c0-3.314 4.925-6 11-6m0 12c-3.72 0-7.01-1.008-9-2.55v.55c0 1.882 3.883 4 9 4c5.01 0 8.838-2.03 8.995-3.882l.005-.118l.001-.55c-1.99 1.542-5.28 2.55-9.001 2.55m0-10c-5.117 0-9 2.118-9 4s3.883 4 9 4s9-2.118 9-4s-3.883-4-9-4"/></svg>`;
        const artifactMarkerSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512" style="color:#a855f7"><path fill="currentColor" d="M54.6 25.88L41.4 38.12l62.5 67.28c3.8-6.87 6.5-11.92 9.1-16.72zm386.6.26l-46.6 54.33c2.4 4.36 5.4 9.94 9.2 17.01l51-59.62zm-272.9 2.32l-16.6 7.08l42.1 98.26c6.4 2.1 13.6 3.8 21.8 5.2zm143 1.07l-32 111.57c7.2-.6 13.8-1.5 19.8-2.6l29.6-104.03zM263 47.31L255.7 142h.3c6.3 0 12.2-.2 17.8-.5l7.2-92.81zM129.3 96.47c-6.2 11.73-15.1 28.33-31.76 57.13c-16.63 28.8-26.56 44.8-33.56 56.1c11.59-2.6 27.23-3.5 44.92 3.5c22.8 9 48 30.5 73.4 74.4c25.4 44 31.3 76.6 27.7 100.8c-2.7 18.9-11.4 32-19.4 40.7c13.3-.4 32.1-1 65.4-1c33.2 0 52 .6 65.3 1c-8-8.7-16.6-21.8-19.4-40.7c-3.6-24.2 2.4-56.8 27.8-100.8c25.4-43.9 50.6-65.3 73.4-74.4c17.7-7 33.4-6.1 44.9-3.5c-7-11.3-16.9-27.3-33.5-56.1s-25.5-45.4-31.8-57.08c-3.6 11.28-10.6 25.38-25.6 37.18C338 148.9 306.8 160 256 160s-82-11.2-101.1-26.3c-15-11.9-22-25.9-25.6-37.23m313.5 8.13l-25.3 17.9c2.7 4.8 5.7 10.1 9 15.8l26.7-18.9zM35.03 167.5l-6.06 17l24 8.6c2.77-4.5 6-9.8 9.63-15.8zM256 196a49.98 49.98 0 0 1 50 50a49.98 49.98 0 0 1-50 50a49.98 49.98 0 0 1-50-50a49.98 49.98 0 0 1 50-50m118.9 59.4c-4.6 4.9-9.3 10.6-14.1 17.2l118.6 8.4l1.2-18zm-231.2 7.5L30.73 279.1l2.54 17.8L156 279.4c-4.1-6.2-8.2-11.6-12.3-16.5m18.7 26.4L44.23 343.8l7.54 16.4L171.4 305c-1.5-2.7-3-5.5-4.7-8.4c-1.5-2.5-2.9-5-4.3-7.3m181.3 10.1c-3.1 5.6-5.9 10.9-8.4 16l124 76.3l9.4-15.4zm-166.4 17.3L25.88 457.4l12.24 13.2L184.8 334.4q-3-8.4-7.5-17.7m148.5 21.6q-4.2 12.45-5.7 22.8l88.6 124.1l14.6-10.4zM224.4 446.4c-7 .1-13 .2-18.5.4l-6.7 31.3l17.6 3.8zm77.1.2l9.8 35.8l17.4-4.8l-8.4-30.4c-5.4-.2-11.1-.4-18.8-.6"/></svg>`;
        const coinMarkerSpritePath = `data:image/svg+xml;utf8,${encodeURIComponent(coinMarkerSvg)}`;
        const artifactMarkerSpritePath = `data:image/svg+xml;utf8,${encodeURIComponent(artifactMarkerSvg)}`;
        const wallSprites = {};
        let floorSprite = null;
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

                if (isTileOutsideVisibility(tile) && !isArtifactAtPoint(tile?.point) && !tile?.isCollapsed) {
                    drawVisibilityOverlay(x, y, tileSize);
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
            const isOutsideVisibility = isTileOutsideVisibility(tile);
            const sprite = isCollapsed ? (floorCollapsedSprite ?? floorSprite) : floorSprite;

            if (sprite) {
                context.drawImage(sprite, x, y, tileSize, tileSize);
            } else {
                context.fillStyle = '#9ca3af';
                context.fillRect(x, y, tileSize, tileSize);
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
            if (!playerPosition || !Number.isFinite(visibilityRadius)) {
                return false;
            }

            const dx = Number(tile.point.x) - Number(playerPosition.x);
            const dy = Number(tile.point.y) - Number(playerPosition.y);
            const distance = Math.hypot(dx, dy);

            return distance > visibilityRadius;
        }

        function drawVisibilityOverlay(x, y, tileSize) {
            context.save();
            context.fillStyle = 'rgba(0, 0, 0, 0.68)';
            context.fillRect(x, y, tileSize, tileSize);
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

                if (tLeft > 0 && yLeft >= viewportTop && yLeft <= viewportBottom && tLeft < bestT) {
                    bestT = tLeft;
                    hitX = viewportLeft;
                    hitY = yLeft;
                }

                const tRight = (viewportRight - playerX) / vx;
                const yRight = playerY + (tRight * vy);

                if (tRight > 0 && yRight >= viewportTop && yRight <= viewportBottom && tRight < bestT) {
                    bestT = tRight;
                    hitX = viewportRight;
                    hitY = yRight;
                }
            }

            if (vy !== 0) {
                const tTop = (viewportTop - playerY) / vy;
                const xTop = playerX + (tTop * vx);

                if (tTop > 0 && xTop >= viewportLeft && xTop <= viewportRight && tTop < bestT) {
                    bestT = tTop;
                    hitX = xTop;
                    hitY = viewportTop;
                }

                const tBottom = (viewportBottom - playerY) / vy;
                const xBottom = playerX + (tBottom * vx);

                if (tBottom > 0 && xBottom >= viewportLeft && xBottom <= viewportRight && tBottom < bestT) {
                    bestT = tBottom;
                    hitX = xBottom;
                    hitY = viewportBottom;
                }
            }

            if (!Number.isFinite(bestT) || hitX === null || hitY === null) {
                artifactCompass.style.display = 'none';
                return;
            }

            const viewportRect = viewport.getBoundingClientRect();
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
            stats.health = numberFrom(nextPayload?.health);
            stats.maxHealth = numberFrom(nextPayload?.maxHealth);
            stats.mana = numberFrom(nextPayload?.mana);
            stats.maxMana = numberFrom(nextPayload?.maxMana);
            stats.stability = numberFrom(nextPayload?.stability);
            stats.maxStability = numberFrom(nextPayload?.maxStability);
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

            if (!existingDweller) {
                return;
            }

            const index = dwellers.indexOf(existingDweller);

            if (index !== -1) {
                dwellers.splice(index, 1);
            }

            dwellerIndex.delete(dwellerKey);
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
            return Boolean(activeCard?.canInteractWithTile);
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

            if (tileFromPayload) {
                upsertTile({
                    ...tileFromPayload,
                    coins: 0,
                });
                stats.coins += collectedAmount;
                return;
            }

            const tile = findTileByPoint(payload?.point ?? payload?.position ?? payload?.to);

            if (!tile) {
                return;
            }

            tile.coins = 0;
            stats.coins += collectedAmount;
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
            const absoluteValue = payload?.[statKey];

            if (typeof absoluteValue !== 'undefined') {
                stats[statKey] = numberFrom(absoluteValue);
                return;
            }

            const amount = numberFrom(payload?.amount);
            stats[statKey] += isDecrease ? -amount : amount;
        }

        function applyChanges(changes) {
            if (!Array.isArray(changes)) {
                return;
            }

            for (const change of changes) {
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
                    const decreasesHealth = change?.name === 'player.healthLost' || change?.name === 'player.healthDecreased';
                    applySignedStatChange(change.payload, 'health', decreasesHealth);
                }
            }
        }

        function renderDebugPopup() {
            if (!debugPopup) {
                return;
            }

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

            renderCurrentMaxCounter(counters.health, stats.health, stats.maxHealth);
            renderCurrentMaxCounter(counters.mana, stats.mana, stats.maxMana);
            renderCurrentMaxCounter(counters.stability, stats.stability, stats.maxStability);
        }

        async function movePlayer(direction) {
            if (state.moveInFlight) {
                return;
            }

            state.moveInFlight = true;

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
            } finally {
                state.moveInFlight = false;
            }
        }

        async function playCard(cardId) {
            if (state.moveInFlight) {
                return;
            }

            state.moveInFlight = true;

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
            }
        }

        async function interactWithTile(point) {
            if (state.moveInFlight) {
                return;
            }

            state.moveInFlight = true;

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
            if (!playerPosition) {
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

            Promise.all([...wallPromises, floorPromise, floorCollapsedPromise, playerPromise, dwellerPromise, coinMarkerPromise, artifactMarkerPromise]).then(() => {
                render();
            });
        }

        function render() {
            resizeCanvas();
            draw();
            updateViewportCursorState();
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
            setHoveredTile(tile);
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

            if (!tile) {
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

        hydrateFromPayload(payload);
        render();
        centerViewportOnPoint(playerPosition);
        renderDebugPopup();
        renderCounters();
        renderCardSlots();
        renderHand();
        preloadSprites();
    </script>
</body>
</html>
