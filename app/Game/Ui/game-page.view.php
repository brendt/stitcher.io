<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <title>Rail Claim Demo</title>
    <x-vite-tags entrypoint="app/main.entrypoint.css"/>
    <style>
        @keyframes reachablePulse {
            0% {
                filter: brightness(1);
            }
            50% {
                filter: brightness(1.12);
            }
            100% {
                filter: brightness(1);
            }
        }

        #edge-layer,
        #edge-layer text {
            user-select: none;
            -webkit-user-select: none;
        }
    </style>
</head>
<body class="bg-gray-100 p-2 md:p-4">
<div id="game-root" class="w-full h-full bg-white rounded-lg border border-gray-200 p-3 md:p-4" :data-game-id="$gameId">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-2xl font-bold">Rail Claim Demo</h1>
        <div class="flex gap-2 text-sm">
            <a href="/game/demo" class="bg-pink-600 text-white px-3 py-2 rounded font-bold hover:opacity-90">New demo</a>
            <button id="refresh-btn" class="bg-gray-200 px-3 py-2 rounded font-bold hover:bg-gray-300">Refresh</button>
        </div>
    </div>

    <div class="grid lg:grid-cols-[1fr_320px] gap-4">
        <section class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-3 py-2 border-b border-gray-200 flex justify-between items-center text-sm">
                <span class="font-bold">Map</span>
                <div class="flex gap-2">
                    <button id="zoom-out" class="bg-gray-100 px-2 py-1 rounded">-</button>
                    <button id="zoom-in" class="bg-gray-100 px-2 py-1 rounded">+</button>
                </div>
            </div>
            <div
                id="map-viewport"
                class="relative w-full bg-gray-50 overflow-hidden touch-none border-t border-gray-200 select-none"
                style="height: 70vh; min-height: 420px;"
            >
                <div id="map-stage" class="absolute left-0 top-0 origin-top-left select-none" style="width:1200px;height:800px;">
                    <svg id="edge-layer" width="1200" height="800" class="absolute inset-0"></svg>
                    <div id="node-layer" class="absolute inset-0"></div>
                </div>
                <div id="move-modal" data-map-interactive="true" class="hidden absolute z-30 w-52 rounded-lg border border-gray-300 bg-white p-3 shadow-lg text-xs">
                    <div id="move-modal-title" class="font-bold text-sm text-gray-900 mb-2"></div>
                    <div class="flex items-center justify-center gap-2">
                        <button id="move-modal-minus" type="button" class="min-w-12 px-3 py-1 rounded bg-gray-100 font-bold">-</button>
                        <div id="move-modal-amount" class="min-w-12 text-center text-sm font-bold text-gray-900">0</div>
                        <button id="move-modal-plus" type="button" class="min-w-12 px-3 py-1 rounded bg-gray-100 font-bold">+</button>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button id="move-modal-cancel" type="button" class="bg-gray-100 px-2 py-1 rounded">Cancel</button>
                        <button id="move-modal-confirm" type="button" class="bg-emerald-600 text-white px-2 py-1 rounded">Confirm travel</button>
                    </div>
                </div>
            </div>
        </section>

        <aside class="bg-white rounded-lg border border-gray-200 p-3 grid gap-3 h-fit text-sm">
            <div>
                <div class="font-bold mb-2">Game</div>
                <div id="status-line" class="text-gray-600">Loading…</div>
            </div>

            <div>
                <label for="player-select" class="font-bold block mb-1">Control player</label>
                <select id="player-select" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1"></select>
            </div>

            <div class="grid gap-2">
                <button id="challenge-btn" class="bg-yellow-300 rounded px-3 py-2 font-bold hover:bg-yellow-400">Complete challenge here</button>
                <button id="finalize-btn" class="bg-gray-200 rounded px-3 py-2 font-bold hover:bg-gray-300">Finalize match (force)</button>
            </div>

            <div>
                <div class="font-bold mb-1">Players</div>
                <div id="players-panel" class="grid gap-1 text-gray-700"></div>
            </div>

            <div>
                <div class="font-bold mb-1">Timeline</div>
                <div id="timeline-panel" class="grid gap-1 text-xs text-gray-600 max-h-52 overflow-auto"></div>
            </div>

            <div id="feedback" class="text-xs min-h-5"></div>
        </aside>
    </div>
</div>

<script>
    (() => {
        const gameId = document.getElementById('game-root')?.dataset?.gameId;
        if (!gameId) {
            throw new Error('Missing game id');
        }
        const stateUrl = `/games/${gameId}/state?timeline=true`;

        const viewport = document.getElementById('map-viewport');
        const stage = document.getElementById('map-stage');
        const edgeLayer = document.getElementById('edge-layer');
        const nodeLayer = document.getElementById('node-layer');
        const statusLine = document.getElementById('status-line');
        const playersPanel = document.getElementById('players-panel');
        const timelinePanel = document.getElementById('timeline-panel');
        const feedback = document.getElementById('feedback');
        const playerSelect = document.getElementById('player-select');
        const moveModal = document.getElementById('move-modal');
        const moveModalTitle = document.getElementById('move-modal-title');
        const moveModalMinus = document.getElementById('move-modal-minus');
        const moveModalAmount = document.getElementById('move-modal-amount');
        const moveModalPlus = document.getElementById('move-modal-plus');
        const moveModalCancel = document.getElementById('move-modal-cancel');
        const moveModalConfirm = document.getElementById('move-modal-confirm');
        const challengeBtn = document.getElementById('challenge-btn');
        const finalizeBtn = document.getElementById('finalize-btn');
        const OVERCLAIM_CAP = 5;
        const NODE_SIZE = 24;
        const PLAYER_COLOR_FALLBACK = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#ec4899', '#14b8a6'];

        let state = null;
        let stationPositions = {};
        let scale = 1;
        let stageX = 0;
        let stageY = 0;
        let activePointers = new Map();
        let pinchStartDistance = null;
        let pinchStartScale = 1;
        let pinchStartWorld = null;
        let panStart = null;
        let moveModalStationId = null;
        let moveModalBounds = null;
        let moveModalCoins = 0;

        stage.style.transformOrigin = '0 0';

        function setFeedback(message, isError = false) {
            feedback.textContent = message;
            feedback.className = isError ? 'text-xs min-h-5 text-red-700' : 'text-xs min-h-5 text-emerald-700';
        }

        function applyNodeScale() {
            const inverse = 1 / scale;
            for (const node of nodeLayer.querySelectorAll('[data-map-node="true"]')) {
                const isReachable = node.dataset.reachable === 'true';
                const isCurrent = node.dataset.current === 'true';
                const exponent = isReachable ? 0.9 : 1;
                const baseScale = Math.pow(inverse, exponent);
                const nodeScale = isCurrent ? (baseScale * 1.5) : baseScale;
                node.style.transform = `scale(${nodeScale})`;
                node.style.transformOrigin = 'center center';
            }
        }

        function applyTransform() {
            // Canonical camera model: screen = world * scale + translation.
            stage.style.transform = `matrix(${scale}, 0, 0, ${scale}, ${stageX}, ${stageY})`;
            applyNodeScale();
            positionMoveModal();
        }

        function clampScale(value) {
            return Math.max(0.4, Math.min(2.5, value));
        }

        function viewportPoint(clientX, clientY) {
            const rect = viewport.getBoundingClientRect();

            return {
                x: clientX - rect.left,
                y: clientY - rect.top,
            };
        }

        function viewportCenterClientPoint() {
            const rect = viewport.getBoundingClientRect();

            return {
                x: rect.left + (rect.width / 2),
                y: rect.top + (rect.height / 2),
            };
        }

        function screenToWorld(pointX, pointY) {
            return {
                x: (pointX - stageX) / scale,
                y: (pointY - stageY) / scale,
            };
        }

        function stationScreenPoint(stationId) {
            const world = stationPositions[stationId];
            if (!world) {
                return null;
            }

            return {
                x: (world.x * scale) + stageX,
                y: (world.y * scale) + stageY,
            };
        }

        function hideMoveModal() {
            moveModalStationId = null;
            moveModalBounds = null;
            moveModal.classList.add('hidden');
        }

        function clampMoveCoins(value) {
            if (!moveModalBounds) {
                return 0;
            }

            return Math.max(moveModalBounds.min, Math.min(moveModalBounds.max, value));
        }

        function updateMoveModalAmountUi() {
            if (!moveModalBounds) {
                return;
            }

            moveModalAmount.textContent = String(moveModalCoins);
            const decreaseDisabled = moveModalBounds.disabled || moveModalCoins <= moveModalBounds.min;
            const increaseDisabled = moveModalBounds.disabled || moveModalCoins >= moveModalBounds.max;
            moveModalMinus.disabled = decreaseDisabled;
            moveModalPlus.disabled = increaseDisabled;
            moveModalMinus.className = decreaseDisabled
                ? 'min-w-12 px-3 py-1 rounded bg-gray-200 text-gray-400 font-bold cursor-not-allowed'
                : 'min-w-12 px-3 py-1 rounded bg-gray-100 font-bold';
            moveModalPlus.className = increaseDisabled
                ? 'min-w-12 px-3 py-1 rounded bg-gray-200 text-gray-400 font-bold cursor-not-allowed'
                : 'min-w-12 px-3 py-1 rounded bg-gray-100 font-bold';
        }

        function positionMoveModal() {
            if (!moveModalStationId || moveModal.classList.contains('hidden')) {
                return;
            }

            const stationPoint = stationScreenPoint(moveModalStationId);
            if (!stationPoint) {
                hideMoveModal();
                return;
            }

            const modalWidth = moveModal.offsetWidth || 208;
            const modalHeight = moveModal.offsetHeight || 128;
            let left = stationPoint.x - (modalWidth / 2);
            let top = stationPoint.y - modalHeight - 14;

            if (top < 8) {
                top = stationPoint.y + 14;
            }

            left = Math.max(8, Math.min((viewport.clientWidth - modalWidth - 8), left));
            top = Math.max(8, Math.min((viewport.clientHeight - modalHeight - 8), top));
            moveModal.style.left = `${left}px`;
            moveModal.style.top = `${top}px`;
        }

        function moveDepositBounds(player, target) {
            if (!player || !target) {
                return null;
            }

            if (target.ownerId === player.id) {
                return { min: 0, max: 0, value: 0, disabled: true };
            }

            if (target.ownerId === null) {
                const min = 1;
                const max = Math.min(OVERCLAIM_CAP, player.coins);

                if (max < min) {
                    return { min, max, value: min, disabled: true };
                }

                return { min, max, value: min, disabled: false };
            }

            const min = target.topValue + 1;
            const max = Math.min(target.topValue + OVERCLAIM_CAP, player.coins);

            if (max < min) {
                return { min, max, value: min, disabled: true };
            }

            return { min, max, value: min, disabled: false };
        }

        function showMoveModal(stationId) {
            const player = currentPlayer();
            if (!player) {
                return;
            }

            if (!canMoveTo(player, stationId)) {
                setFeedback('Target is not adjacent to your current station.', true);
                return;
            }

            const station = stationById(stationId);
            const bounds = moveDepositBounds(player, station);
            if (!bounds) {
                return;
            }

            moveModalStationId = stationId;
            moveModalBounds = bounds;
            moveModalCoins = clampMoveCoins(bounds.value);
            moveModalTitle.textContent = stationLabel(station);
            moveModalConfirm.disabled = bounds.disabled;
            moveModalConfirm.className = bounds.disabled
                ? 'bg-gray-300 text-white px-2 py-1 rounded cursor-not-allowed'
                : 'bg-emerald-600 text-white px-2 py-1 rounded';
            updateMoveModalAmountUi();
            moveModal.classList.remove('hidden');
            positionMoveModal();
        }

        function zoomAt(clientX, clientY, factor) {
            const nextScale = clampScale(scale * factor);
            if (nextScale === scale) {
                return;
            }

            const point = viewportPoint(clientX, clientY);
            const world = screenToWorld(point.x, point.y);
            scale = nextScale;
            stageX = point.x - (world.x * scale);
            stageY = point.y - (world.y * scale);
            applyTransform();
        }

        function zoomAtViewportCenter(factor) {
            const center = viewportCenterClientPoint();
            zoomAt(center.x, center.y, factor);
        }

        function distance(a, b) {
            const dx = a.x - b.x;
            const dy = a.y - b.y;
            return Math.sqrt(dx * dx + dy * dy);
        }

        function isMapInteractiveTarget(target) {
            return target instanceof Element && Boolean(target.closest('[data-map-interactive="true"]'));
        }

        viewport.addEventListener('pointerdown', (event) => {
            if (isMapInteractiveTarget(event.target)) {
                return;
            }

            if (moveModalStationId) {
                hideMoveModal();
            }

            viewport.setPointerCapture(event.pointerId);
            activePointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

            if (activePointers.size === 1) {
                panStart = { x: event.clientX, y: event.clientY, stageX, stageY };
            }

            if (activePointers.size === 2) {
                const [a, b] = [...activePointers.values()];
                pinchStartDistance = distance(a, b);
                pinchStartScale = scale;
                const mid = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
                const midPoint = viewportPoint(mid.x, mid.y);
                pinchStartWorld = screenToWorld(midPoint.x, midPoint.y);
            }
        });

        viewport.addEventListener('pointermove', (event) => {
            if (!activePointers.has(event.pointerId)) return;

            activePointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

            if (activePointers.size === 1 && panStart) {
                stageX = panStart.stageX + (event.clientX - panStart.x);
                stageY = panStart.stageY + (event.clientY - panStart.y);
                applyTransform();
            }

            if (activePointers.size === 2 && pinchStartDistance && pinchStartWorld) {
                const [a, b] = [...activePointers.values()];
                const currentDistance = distance(a, b);
                const mid = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
                const midPoint = viewportPoint(mid.x, mid.y);
                scale = clampScale(pinchStartScale * (currentDistance / pinchStartDistance));
                stageX = midPoint.x - (pinchStartWorld.x * scale);
                stageY = midPoint.y - (pinchStartWorld.y * scale);
                applyTransform();
            }
        });

        viewport.addEventListener('pointerup', (event) => {
            activePointers.delete(event.pointerId);
            if (activePointers.size < 2) {
                pinchStartDistance = null;
                pinchStartWorld = null;
            }
            if (activePointers.size === 0) panStart = null;
        });

        viewport.addEventListener('wheel', (event) => {
            event.preventDefault();
            const step = Math.exp(-event.deltaY * 0.0045);
            zoomAt(event.clientX, event.clientY, step);
        }, { passive: false });

        document.getElementById('zoom-in').addEventListener('click', () => {
            zoomAtViewportCenter(1.1);
        });

        document.getElementById('zoom-out').addEventListener('click', () => {
            zoomAtViewportCenter(0.9);
        });

        moveModalCancel.addEventListener('click', hideMoveModal);
        moveModalMinus.addEventListener('click', () => {
            if (!moveModalBounds || moveModalBounds.disabled) {
                return;
            }

            moveModalCoins = clampMoveCoins(moveModalCoins - 1);
            updateMoveModalAmountUi();
        });
        moveModalPlus.addEventListener('click', () => {
            if (!moveModalBounds || moveModalBounds.disabled) {
                return;
            }

            moveModalCoins = clampMoveCoins(moveModalCoins + 1);
            updateMoveModalAmountUi();
        });
        moveModalConfirm.addEventListener('click', async () => {
            if (!moveModalStationId || !moveModalBounds) {
                return;
            }

            const deposit = clampMoveCoins(moveModalCoins);
            await moveTo(moveModalStationId, deposit);
        });

        function ensurePlayerOptions() {
            const selected = playerSelect.value;
            playerSelect.innerHTML = '';

            for (const player of state.players) {
                const option = document.createElement('option');
                option.value = player.id;
                option.textContent = `${player.id} (${player.coins} coins)`;
                playerSelect.appendChild(option);
            }

            if (selected && state.players.some((p) => p.id === selected)) {
                playerSelect.value = selected;
            }
        }

        function computeStationPositions() {
            stationPositions = {};
            const scaleFactor = 10;
            const padding = 40;

            let maxX = 0;
            let maxY = 0;
            let skipped = 0;

            for (const station of state.stations) {
                if (typeof station.x !== 'number' || typeof station.y !== 'number') {
                    skipped++;
                    continue;
                }

                const x = padding + (station.x * scaleFactor);
                const y = padding + (station.y * scaleFactor);

                stationPositions[station.id] = { x, y };
                maxX = Math.max(maxX, x);
                maxY = Math.max(maxY, y);
            }

            stage.style.width = `${Math.max(1200, maxX + padding)}px`;
            stage.style.height = `${Math.max(800, maxY + padding)}px`;
            edgeLayer.setAttribute('width', String(Math.max(1200, maxX + padding)));
            edgeLayer.setAttribute('height', String(Math.max(800, maxY + padding)));

            if (skipped > 0) {
                setFeedback(`Skipped ${skipped} stations without coordinates`, true);
            }
        }

        function stationById(id) {
            return state.stations.find((station) => station.id === id) ?? null;
        }

        function stationLabel(station) {
            return station?.name ?? station?.id ?? '-';
        }

        function currentPlayer() {
            const id = playerSelect.value || state.players[0]?.id;
            return state.players.find((player) => player.id === id) ?? null;
        }

        function playerColor(playerId) {
            if (!playerId) {
                return '#94a3b8';
            }

            if (playerId === 'p1') return '#ef4444';
            if (playerId === 'p2') return '#3b82f6';
            if (playerId === 'p3') return '#10b981';
            if (playerId === 'p4') return '#f59e0b';

            let hash = 0;
            for (let i = 0; i < playerId.length; i++) {
                hash = (hash * 31 + playerId.charCodeAt(i)) >>> 0;
            }

            return PLAYER_COLOR_FALLBACK[hash % PLAYER_COLOR_FALLBACK.length];
        }

        function claimShadow(ownerId, topValue) {
            if (!ownerId || topValue <= 0) {
                return '';
            }

            const spread = Math.min(8, 1 + Math.floor(topValue / 3));
            const color = playerColor(ownerId);
            return `0 0 0 ${spread}px ${color}`;
        }

        function undirectedEdgeKey(a, b) {
            return a < b ? `${a}|${b}` : `${b}|${a}`;
        }

        function uniqueEdges() {
            const deduped = new Map();

            for (const edge of state.edges) {
                const key = undirectedEdgeKey(edge.fromStationId, edge.toStationId);
                const existing = deduped.get(key);

                if (!existing) {
                    deduped.set(key, edge);
                    continue;
                }

                // Keep strongest visual variant if duplicate directed rows exist.
                if (!existing.isExpress && edge.isExpress) {
                    deduped.set(key, edge);
                }
            }

            return [...deduped.values()];
        }

        function reachableEdgeKeys(player) {
            if (!player?.stationId) {
                return new Set();
            }

            const keys = new Set();

            for (const edge of uniqueEdges()) {
                if (edge.fromStationId === player.stationId || edge.toStationId === player.stationId) {
                    keys.add(undirectedEdgeKey(edge.fromStationId, edge.toStationId));
                }
            }

            return keys;
        }

        function renderEdges() {
            edgeLayer.innerHTML = '';
            const stationOwnerById = new Map(state.stations.map((station) => [station.id, station.ownerId]));
            const stationLineById = new Map(state.stations.map((station) => [station.id, station.lineId]));

            for (const edge of uniqueEdges()) {
                const from = stationPositions[edge.fromStationId];
                const to = stationPositions[edge.toStationId];
                if (!from || !to) continue;
                const fromOwner = stationOwnerById.get(edge.fromStationId);
                const toOwner = stationOwnerById.get(edge.toStationId);
                const sameOwner = fromOwner !== null && fromOwner === toOwner;
                const ownerStroke = sameOwner ? playerColor(fromOwner) : null;
                const fromLine = stationLineById.get(edge.fromStationId);
                const toLine = stationLineById.get(edge.toStationId);
                const touchesBranchLine = (fromLine && fromLine !== 'L1') || (toLine && toLine !== 'L1');
                const baseStroke = edge.isExpress ? '#0f766e' : (touchesBranchLine ? '#7f8fb0' : '#94a3b8');
                const edgeStroke = ownerStroke ?? baseStroke;

                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', String(from.x));
                line.setAttribute('y1', String(from.y));
                line.setAttribute('x2', String(to.x));
                line.setAttribute('y2', String(to.y));
                line.setAttribute('stroke', edgeStroke);
                line.setAttribute('stroke-width', edge.isExpress ? '4' : '2');
                line.setAttribute('stroke-opacity', '0.75');
                line.setAttribute('stroke-linecap', 'round');
                edgeLayer.appendChild(line);
            }
        }

        function challengeAtStation(stationId) {
            return state.challenges.find((challenge) => challenge.active && challenge.station_id === stationId) ?? null;
        }

        function canMoveTo(player, targetStationId) {
            if (!player || !player.stationId) return false;
            return state.edges.some((edge) => (
                edge.fromStationId === player.stationId && edge.toStationId === targetStationId
            ) || (
                edge.toStationId === player.stationId && edge.fromStationId === targetStationId
            ));
        }

        async function postForm(url, payload) {
            const body = new URLSearchParams();
            Object.entries(payload).forEach(([key, value]) => {
                if (value !== null && value !== undefined) body.set(key, String(value));
            });

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                    'Accept': 'application/json',
                },
                body: body.toString(),
            });

            return response.json();
        }

        async function moveTo(targetStationId, requestedDeposit = null) {
            const player = currentPlayer();
            if (!player) return;

            if (!canMoveTo(player, targetStationId)) {
                setFeedback('Target is not adjacent to your current station.', true);
                return;
            }

            const target = stationById(targetStationId);
            const ownedByPlayer = target?.ownerId === player.id;
            let deposit = ownedByPlayer ? 0 : Number.parseInt(String(requestedDeposit ?? 1), 10);
            const bounds = moveDepositBounds(player, target);
            if (!bounds) return;

            if (bounds.disabled && !ownedByPlayer) {
                setFeedback('Not enough coins for this move.', true);
                return;
            }

            const adjusted = Math.max(bounds.min, Math.min(bounds.max, Number.isFinite(deposit) ? deposit : bounds.value));
            if (!ownedByPlayer && adjusted !== deposit) {
                setFeedback(`Adjusted investment to ${adjusted} (allowed ${bounds.min}-${bounds.max}).`);
            }
            deposit = adjusted;

            const result = await postForm(`/games/${gameId}/commands/move`, {
                playerId: player.id,
                fromStationId: player.stationId,
                toStationId: targetStationId,
                deposit,
            });

            setFeedback(
                result.accepted ? `Move accepted to ${stationLabel(target)}.` : `Move rejected: ${result.reason}`,
                !result.accepted,
            );
            hideMoveModal();
            await loadState();
        }

        function renderNodes() {
            nodeLayer.innerHTML = '';
            const player = currentPlayer();

            for (const station of state.stations) {
                const position = stationPositions[station.id];
                if (!position) continue;

                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.mapInteractive = 'true';
                button.dataset.mapNode = 'true';
                const challenge = challengeAtStation(station.id);
                const isPlayerStation = player?.stationId === station.id;
                const isReachable = player ? canMoveTo(player, station.id) : false;
                button.dataset.reachable = isReachable ? 'true' : 'false';
                button.dataset.current = isPlayerStation ? 'true' : 'false';
                const selectedPlayerColor = playerColor(player?.id ?? null);
                const ownerColor = playerColor(station.ownerId ?? null);
                const isClaimed = station.ownerId !== null;
                const isOutOfReachClaimed = isClaimed && !isPlayerStation && !isReachable;
                const claimAura = claimShadow(station.ownerId, station.topValue);

                button.className = 'absolute rounded-full border text-[7px] font-bold transition flex items-center justify-center p-0 leading-none';
                button.style.left = `${position.x - (NODE_SIZE / 2)}px`;
                button.style.top = `${position.y - (NODE_SIZE / 2)}px`;
                button.style.width = `${NODE_SIZE}px`;
                button.style.height = `${NODE_SIZE}px`;
                button.style.borderRadius = '50%';
                button.style.userSelect = 'none';
                button.style.webkitUserSelect = 'none';
                button.style.opacity = isOutOfReachClaimed ? '1' : ((!isPlayerStation && !isReachable) ? '0.35' : '1');
                button.style.cursor = isReachable || isPlayerStation ? 'pointer' : 'not-allowed';

                if (isPlayerStation) {
                    button.style.background = selectedPlayerColor;
                    button.style.borderColor = selectedPlayerColor;
                    button.style.color = '#ffffff';
                    button.style.outline = 'none';
                    button.style.boxShadow = claimAura || 'none';
                } else if (isReachable) {
                    button.style.background = '#ffffff';
                    button.style.borderColor = selectedPlayerColor;
                    button.style.color = isClaimed ? ownerColor : selectedPlayerColor;
                    button.style.outline = 'none';
                    const doubleRing = `0 0 0 2px #ffffff, 0 0 0 4px ${selectedPlayerColor}`;
                    const reachGlow = `0 0 12px ${selectedPlayerColor}88`;
                    button.style.boxShadow = claimAura ? `${claimAura}, ${doubleRing}, ${reachGlow}` : `${doubleRing}, ${reachGlow}`;
                    button.style.animation = 'reachablePulse 1.3s ease-in-out infinite';
                } else {
                    button.style.background = isClaimed ? '#ffffff' : '#e5e7eb';
                    button.style.color = isClaimed ? ownerColor : '#334155';
                    button.style.outline = 'none';
                    button.style.boxShadow = claimAura || 'none';
                    button.style.animation = 'none';
                    button.style.borderColor = isOutOfReachClaimed ? ownerColor : '#475569';
                }

                if (isPlayerStation) {
                    button.style.animation = 'none';
                }

                const deposited = station.topValue > 0 ? station.topValue : null;
                button.title = `${stationLabel(station)} (${station.id}) | owner: ${station.ownerId ?? 'neutral'} | deposited: ${deposited ?? '-'}${challenge ? ' | challenge' : ''}`;
                button.textContent = deposited === null ? '-' : String(deposited);
                button.addEventListener('click', () => {
                    if (!isReachable) {
                        setFeedback('Target is not adjacent to your current station.', true);
                        return;
                    }

                    if (station.ownerId === player?.id) {
                        moveTo(station.id, 0);
                        return;
                    }

                    showMoveModal(station.id);
                });

                nodeLayer.appendChild(button);
            }

            applyNodeScale();

            if (moveModalStationId && !stationPositions[moveModalStationId]) {
                hideMoveModal();
            } else {
                positionMoveModal();
            }
        }

        function renderSidebar() {
            statusLine.textContent = `Game ${state.game.id} · ${state.game.status}`;

            playersPanel.innerHTML = '';
            for (const player of state.players) {
                const score = state.score?.scores?.[player.id]?.score ?? 0;
                const line = document.createElement('div');
                line.textContent = `${player.id}: ${player.coins} coins · ${player.stationId ?? '-'} · score ${score}`;
                playersPanel.appendChild(line);
            }

            const active = currentPlayer();
            if (active?.stationId) {
                const reachableCount = state.stations.filter((station) => canMoveTo(active, station.id)).length;
                const nav = document.createElement('div');
                nav.className = 'mt-2 text-xs text-gray-600';
                nav.textContent = `Selected: ${active.id} at ${active.stationId} · reachable: ${reachableCount}`;
                playersPanel.appendChild(nav);
            }

            timelinePanel.innerHTML = '';
            for (const event of (state.timeline ?? []).slice(-15).reverse()) {
                const row = document.createElement('div');
                row.textContent = `${event.type} (${event.player_id ?? 'system'})`;
                timelinePanel.appendChild(row);
            }
        }

        async function loadState() {
            const response = await fetch(stateUrl, { headers: { 'Accept': 'application/json' } });
            state = await response.json();
            ensurePlayerOptions();
            computeStationPositions();
            renderEdges();
            renderNodes();
            renderSidebar();
        }

        playerSelect.addEventListener('change', () => {
            hideMoveModal();
            renderEdges();
            renderNodes();
            renderSidebar();
        });

        challengeBtn.addEventListener('click', async () => {
            const player = currentPlayer();
            if (!player || !player.stationId) return;

            const result = await postForm(`/games/${gameId}/commands/complete-challenge`, {
                playerId: player.id,
                stationId: player.stationId,
            });

            setFeedback(result.accepted ? `Challenge completed: +${result.reward} coins` : `Challenge rejected: ${result.reason}`, !result.accepted);
            await loadState();
        });

        finalizeBtn.addEventListener('click', async () => {
            const result = await postForm(`/games/${gameId}/commands/finalize-match`, {
                force: true,
                hubBonus: 2,
            });

            if (!result.accepted) {
                setFeedback(`Finalize rejected: ${result.reason}`, true);
            } else if (result.isTie) {
                setFeedback(`Match finalized: tie (${result.tiedPlayerIds.join(', ')})`);
            } else {
                setFeedback(`Match finalized: winner ${result.winnerPlayerId}`);
            }

            await loadState();
        });

        document.getElementById('refresh-btn').addEventListener('click', loadState);

        loadState().then(() => {
            applyTransform();
            setInterval(loadState, 3000);
        }).catch((error) => {
            setFeedback(`Failed to load state: ${error.message}`, true);
        });
    })();
</script>
</body>
</html>
