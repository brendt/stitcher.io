<html lang="en">
<head>
    <title>Dungeon</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <style>
        :root {
            --tile: #9ca3af;
            --tile-border: #6b7280;
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
            background: rgba(12, 13, 18, 0.9);
            color: #e5e7eb;
            font: 12px/1.4 ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            white-space: pre-wrap;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <div id="viewport" class="viewport">
        <canvas id="dungeon-canvas"></canvas>
    </div>
    <pre id="debug-popup" class="debug-popup"></pre>

    <script id="dungeon-data" type="application/json">{!! json_encode($dungeon->toArray()) !!}</script>
    <script>
        const dataElement = document.getElementById('dungeon-data');
        const viewport = document.getElementById('viewport');
        const canvas = document.getElementById('dungeon-canvas');
        const debugPopup = document.getElementById('debug-popup');
        const context = canvas.getContext('2d');

        const payload = JSON.parse(dataElement.textContent);
        const tiles = normalizeTiles(payload.tiles ?? []);
        const tileIndex = new Map();
        let playerPosition = payload.playerPosition ?? null;
        let dungeonVersion = payload.version ?? null;
        let latestChanges = [];
        const wallSpritePaths = {
            top: '/dungeon/wall-top.png',
            right: '/dungeon/wall-right.png',
            bottom: '/dungeon/wall-bottom.png',
            left: '/dungeon/wall-left.png',
        };
        const floorSpritePath = '/dungeon/tile-floor.png';
        const playerSpritePath = '/dungeon/player-avatar.png';
        const wallSprites = {};
        let floorSprite = null;
        let playerSprite = null;

        for (const tile of tiles) {
            tileIndex.set(getTileKey(tile.point.x, tile.point.y), tile);
        }

        const boundsAccumulator = tiles.reduce((acc, tile) => {
            const x = tile.point.x;
            const y = tile.point.y;

            acc.minX = Math.min(acc.minX, x);
            acc.minY = Math.min(acc.minY, y);
            acc.maxX = Math.max(acc.maxX, x);
            acc.maxY = Math.max(acc.maxY, y);

            return acc;
        }, { minX: Infinity, minY: Infinity, maxX: -Infinity, maxY: -Infinity });

        const bounds = {
            minX: Number.isFinite(boundsAccumulator.minX) ? boundsAccumulator.minX : 0,
            minY: Number.isFinite(boundsAccumulator.minY) ? boundsAccumulator.minY : 0,
            maxX: Number.isFinite(boundsAccumulator.maxX) ? boundsAccumulator.maxX : 0,
            maxY: Number.isFinite(boundsAccumulator.maxY) ? boundsAccumulator.maxY : 0,
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

            context.clearRect(0, 0, canvas.width, canvas.height);

            for (const tile of tiles) {
                const x = state.paddingX + (tile.point.x - bounds.minX) * step;
                const y = state.paddingY + (tile.point.y - bounds.minY) * step;
                const openDirections = new Set(tile.directions ?? []);

                drawFloor(x, y, tileSize);

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
            }

            drawPlayer(tileSize, step);
        }

        function drawFloor(x, y, tileSize) {
            if (floorSprite) {
                context.drawImage(floorSprite, x, y, tileSize, tileSize);
                return;
            }

            context.fillStyle = '#9ca3af';
            context.fillRect(x, y, tileSize, tileSize);
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
            if (Array.isArray(rawTiles)) {
                return rawTiles
                    .filter((tile) => tile && tile.point)
                    .map((tile) => ({
                        ...tile,
                        point: {
                            x: Number(tile.point.x),
                            y: Number(tile.point.y),
                        },
                    }));
            }

            const normalized = [];

            for (const [xKey, column] of Object.entries(rawTiles ?? {})) {
                if (!column || typeof column !== 'object') {
                    continue;
                }

                for (const [yKey, tile] of Object.entries(column)) {
                    if (!tile || typeof tile !== 'object') {
                        continue;
                    }

                    normalized.push({
                        ...tile,
                        point: {
                            x: Number(xKey),
                            y: Number(yKey),
                        },
                    });
                }
            }

            return normalized;
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

        function applyChanges(changes) {
            if (!Array.isArray(changes)) {
                return;
            }

            for (const change of changes) {
                if (change?.name === 'player.moved') {
                    playerPosition = toPoint(change.payload?.to) ?? playerPosition;
                }

                if (change?.name === 'tile.generated') {
                    upsertTile(change.payload);
                }
            }
        }

        function renderDebugPopup() {
            if (!debugPopup) {
                return;
            }

            debugPopup.textContent = JSON.stringify({
                version: dungeonVersion,
                changes: latestChanges,
            }, null, 2);
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

            const playerPromise = loadImage(playerSpritePath).then((image) => {
                playerSprite = image;
            });

            Promise.all([...wallPromises, floorPromise, playerPromise]).then(() => {
                render();
            });
        }

        function render() {
            resizeCanvas();
            draw();
            centerOnPlayer();
        }

        function centerOnPlayer() {
            const focusPoint = playerPosition ?? { x: 0, y: 0 };
            const step = getStepSize();
            const tileSize = state.baseTileSize * state.scale;
            const tileCenterX = state.paddingX + (focusPoint.x - bounds.minX) * step + (tileSize / 2);
            const tileCenterY = state.paddingY + (focusPoint.y - bounds.minY) * step + (tileSize / 2);

            viewport.scrollLeft = Math.max(0, tileCenterX - (viewport.clientWidth / 2));
            viewport.scrollTop = Math.max(0, tileCenterY - (viewport.clientHeight / 2));
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
            state.dragStartX = event.clientX;
            state.dragStartY = event.clientY;
            state.scrollStartLeft = viewport.scrollLeft;
            state.scrollStartTop = viewport.scrollTop;
            viewport.classList.add('is-dragging');
        });

        window.addEventListener('mousemove', (event) => {
            if (!state.isDragging) {
                return;
            }

            const deltaX = event.clientX - state.dragStartX;
            const deltaY = event.clientY - state.dragStartY;

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
            if (!state.isDragging) {
                return;
            }

            state.isDragging = false;
            viewport.classList.remove('is-dragging');
        });

        window.addEventListener('keydown', (event) => {
            const direction = getDirectionForKey(event.key);

            if (!direction) {
                return;
            }

            event.preventDefault();
            movePlayer(direction);
        });

        render();
        renderDebugPopup();
        preloadSprites();
    </script>
</body>
</html>
