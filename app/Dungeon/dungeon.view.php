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
            filter: grayscale(1);
            cursor: not-allowed;
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
        const floorCoinsSpritePath = '/dungeon/tile-floor-coins.png';
        const floorCollapsedSpritePath = '/dungeon/tile-collapsed.png';
        const playerSpritePath = '/dungeon/player-avatar.png';
        const dwellerSpritePath = '/dungeon/dweller-avater.png';
        const dwellerFallbackSpritePath = '/dungeon/dweller-avatar.png';
        const wallSprites = {};
        let floorSprite = null;
        let floorCoinsSprite = null;
        let floorCollapsedSprite = null;
        let playerSprite = null;
        let dwellerSprite = null;

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

                if (isTileOutsideVisibility(tile)) {
                    drawVisibilityOverlay(x, y, tileSize);
                }
            }

            drawDwellers(tileSize, step);
            drawPlayer(tileSize, step);
        }

        function drawFloor(tile, x, y, tileSize, isHovered = false) {
            const hasCoins = Number(tile?.coins ?? 0) > 0;
            const isCollapsed = Boolean(tile?.isCollapsed);
            const sprite = isCollapsed
                ? (floorCollapsedSprite ?? floorSprite)
                : (hasCoins ? (floorCoinsSprite ?? floorSprite) : floorSprite);

            if (sprite) {
                context.drawImage(sprite, x, y, tileSize, tileSize);
            } else {
                context.fillStyle = '#9ca3af';
                context.fillRect(x, y, tileSize, tileSize);
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
            context.strokeStyle = '#27282e';
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
            const normalizedDwellers = normalizePoints(nextPayload?.dwellers ?? []);
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

        function collectPoints(node, out, fallbackX, fallbackY) {
            if (!node) {
                return;
            }

            if (Array.isArray(node)) {
                for (const item of node) {
                    collectPoints(item, out, fallbackX, fallbackY);
                }
                return;
            }

            if (typeof node !== 'object') {
                return;
            }

            if (typeof node.x !== 'undefined' && typeof node.y !== 'undefined') {
                out.push({
                    x: Number(node.x),
                    y: Number(node.y),
                });
                return;
            }

            if (fallbackX !== null && fallbackY !== null) {
                out.push({
                    x: Number(fallbackX),
                    y: Number(fallbackY),
                });
                return;
            }

            for (const [key, value] of Object.entries(node)) {
                if (fallbackX === null) {
                    collectPoints(value, out, Number(key), fallbackY);
                    continue;
                }

                if (fallbackY === null) {
                    collectPoints(value, out, fallbackX, Number(key));
                    continue;
                }

                collectPoints(value, out, fallbackX, fallbackY);
            }
        }

        function normalizePoints(value) {
            const normalized = [];
            collectPoints(value, normalized, null, null);

            return normalized.filter((point) => Number.isFinite(point.x) && Number.isFinite(point.y));
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
                existingDweller.isVisible = isVisible;
                return;
            }

            const dweller = {
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
            const point = payload?.dweller ?? payload?.point ?? payload?.position ?? payload;
            if (!payload?.isVisible) {
                removeDweller(point);
                return;
            }

            upsertDweller({
                ...(point && typeof point === 'object' ? point : {}),
                isVisible: payload?.isVisible,
            });
        }

        function applyDwellerMoved(payload) {
            const from = payload?.from ?? payload?.oldPosition ?? payload?.previousPosition ?? null;
            const to = payload?.to ?? payload?.position ?? payload?.point ?? payload?.dweller ?? null;

            removeDweller(from);

            if (!payload?.isVisible) {
                removeDweller(to);
                return;
            }

            upsertDweller({
                ...(to && typeof to === 'object' ? to : {}),
                isVisible: payload?.isVisible,
            });
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

                if (change?.name === 'dweller.despawned') {
                    applyDwellerDespawned(change.payload);
                    continue;
                }

                if (change?.name === 'visibility.changed') {
                    const nextRadius = Number(change?.payload?.visibilityRadius);

                    visibilityRadius = Number.isFinite(nextRadius) ? nextRadius : visibilityRadius;
                    continue;
                }

                if (change?.name === 'player.manaGained') {
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

                if (change?.name === 'player.manaLost') {
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

            const step = getStepSize();
            const tileSize = state.baseTileSize * state.scale;
            const centerX = state.paddingX + ((playerPosition.x - bounds.minX) * step) + (tileSize / 2);
            const centerY = state.paddingY + ((playerPosition.y - bounds.minY) * step) + (tileSize / 2);

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

            const floorCoinsPromise = loadImage(floorCoinsSpritePath).then((image) => {
                floorCoinsSprite = image;
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

            Promise.all([...wallPromises, floorPromise, floorCoinsPromise, floorCollapsedPromise, playerPromise, dwellerPromise]).then(() => {
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
        renderDebugPopup();
        renderCounters();
        renderCardSlots();
        renderHand();
        preloadSprites();
    </script>
</body>
</html>
