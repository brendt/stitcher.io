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
            <div class="px-3 py-2 text-xs text-gray-600 border-b border-gray-200">
                Navigation: select a player on the right. Current station has a black ring. Green rings are reachable next moves.
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

            <div>
                <label for="deposit-input" class="font-bold block mb-1">Deposit</label>
                <input id="deposit-input" type="number" min="1" value="1" class="w-full bg-gray-50 border border-gray-300 rounded px-2 py-1" />
                <div class="text-xs text-gray-500 mt-1">Used only for neutral/enemy stations.</div>
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
        const depositInput = document.getElementById('deposit-input');
        const challengeBtn = document.getElementById('challenge-btn');
        const finalizeBtn = document.getElementById('finalize-btn');
        const OVERCLAIM_CAP = 5;
        const NODE_SIZE = 24;
        const PLAYER_COLOR_FALLBACK = ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#ec4899', '#14b8a6'];

        let state = null;
        let stationPositions = {};
        let scale = 1;
        let offsetX = 0;
        let offsetY = 0;
        let activePointers = new Map();
        let pinchStartDistance = null;
        let pinchStartScale = 1;
        let panStart = null;

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
                const nodeScale = isCurrent ? (baseScale * 1.14) : baseScale;
                node.style.transform = `scale(${nodeScale})`;
                node.style.transformOrigin = 'center center';
            }
        }

        function applyTransform() {
            stage.style.transform = `translate(${offsetX}px, ${offsetY}px) scale(${scale})`;
            applyNodeScale();
        }

        function clampScale(value) {
            return Math.max(0.4, Math.min(2.5, value));
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

            viewport.setPointerCapture(event.pointerId);
            activePointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

            if (activePointers.size === 1) {
                panStart = { x: event.clientX, y: event.clientY, offsetX, offsetY };
            }

            if (activePointers.size === 2) {
                const [a, b] = [...activePointers.values()];
                pinchStartDistance = distance(a, b);
                pinchStartScale = scale;
            }
        });

        viewport.addEventListener('pointermove', (event) => {
            if (!activePointers.has(event.pointerId)) return;

            activePointers.set(event.pointerId, { x: event.clientX, y: event.clientY });

            if (activePointers.size === 1 && panStart) {
                offsetX = panStart.offsetX + (event.clientX - panStart.x);
                offsetY = panStart.offsetY + (event.clientY - panStart.y);
                applyTransform();
            }

            if (activePointers.size === 2 && pinchStartDistance) {
                const [a, b] = [...activePointers.values()];
                const currentDistance = distance(a, b);
                scale = clampScale(pinchStartScale * (currentDistance / pinchStartDistance));
                applyTransform();
            }
        });

        viewport.addEventListener('pointerup', (event) => {
            activePointers.delete(event.pointerId);
            if (activePointers.size < 2) pinchStartDistance = null;
            if (activePointers.size === 0) panStart = null;
        });

        viewport.addEventListener('wheel', (event) => {
            event.preventDefault();
            const step = event.deltaY < 0 ? 1.08 : 0.92;
            scale = clampScale(scale * step);
            applyTransform();
        }, { passive: false });

        document.getElementById('zoom-in').addEventListener('click', () => {
            scale = clampScale(scale * 1.1);
            applyTransform();
        });

        document.getElementById('zoom-out').addEventListener('click', () => {
            scale = clampScale(scale * 0.9);
            applyTransform();
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

            for (const edge of uniqueEdges()) {
                const from = stationPositions[edge.fromStationId];
                const to = stationPositions[edge.toStationId];
                if (!from || !to) continue;
                const fromOwner = stationOwnerById.get(edge.fromStationId);
                const toOwner = stationOwnerById.get(edge.toStationId);
                const sameOwner = fromOwner !== null && fromOwner === toOwner;
                const ownerStroke = sameOwner ? playerColor(fromOwner) : null;
                const edgeStroke = ownerStroke ?? (edge.isExpress ? '#0f766e' : '#94a3b8');
                const edgeKey = undirectedEdgeKey(edge.fromStationId, edge.toStationId);
                const dx = to.x - from.x;
                const dy = to.y - from.y;
                const length = Math.hypot(dx, dy) || 1;
                const nx = -dy / length;
                const ny = dx / length;
                const hash = Array.from(edgeKey).reduce((acc, char) => acc + char.charCodeAt(0), 0);
                const direction = hash % 2 === 0 ? 1 : -1;
                const bend = Math.min(14, Math.max(3, length * 0.06)) * direction;
                const cx = ((from.x + to.x) / 2) + (nx * bend);
                const cy = ((from.y + to.y) / 2) + (ny * bend);

                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', `M ${from.x} ${from.y} Q ${cx} ${cy} ${to.x} ${to.y}`);
                path.setAttribute('fill', 'none');
                path.setAttribute('stroke', edgeStroke);
                path.setAttribute('stroke-width', edge.isExpress ? '4' : '2');
                path.setAttribute('stroke-opacity', '0.75');
                path.setAttribute('stroke-linecap', 'round');
                path.setAttribute('stroke-linejoin', 'round');
                edgeLayer.appendChild(path);
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

        async function moveTo(targetStationId) {
            const player = currentPlayer();
            if (!player) return;

            if (!canMoveTo(player, targetStationId)) {
                setFeedback('Target is not adjacent to your current station.', true);
                return;
            }

            const target = stationById(targetStationId);
            const ownedByPlayer = target?.ownerId === player.id;
            let deposit = ownedByPlayer ? null : Number.parseInt(depositInput.value || '1', 10);

            if (!ownedByPlayer && target?.ownerId) {
                const minimum = target.topValue + 1;
                const maximum = target.topValue + OVERCLAIM_CAP;
                const requested = Number.isFinite(deposit) ? deposit : minimum;
                const adjusted = Math.max(minimum, Math.min(maximum, requested));

                if (adjusted !== requested) {
                    setFeedback(`Adjusted bid to ${adjusted} (allowed ${minimum}-${maximum} for this overclaim).`);
                }

                deposit = adjusted;
                depositInput.value = String(adjusted);
            }

            const result = await postForm(`/games/${gameId}/commands/move`, {
                playerId: player.id,
                fromStationId: player.stationId,
                toStationId: targetStationId,
                deposit: Number.isFinite(deposit) ? deposit : 1,
            });

            setFeedback(result.accepted ? `Move accepted to ${targetStationId}.` : `Move rejected: ${result.reason}`, !result.accepted);
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
                    button.style.borderColor = isOutOfReachClaimed
                        ? ownerColor
                        : (station.isHub ? '#f59e0b' : '#475569');
                }

                if (isPlayerStation) {
                    button.style.animation = 'none';
                }

                const deposited = station.topValue > 0 ? station.topValue : null;
                button.title = `${station.id} | owner: ${station.ownerId ?? 'neutral'} | deposited: ${deposited ?? '-'}${challenge ? ' | challenge' : ''}`;
                button.textContent = deposited === null ? '-' : String(deposited);
                button.addEventListener('click', () => moveTo(station.id));

                nodeLayer.appendChild(button);
            }

            applyNodeScale();
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
