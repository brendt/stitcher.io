<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <title>Rail Claim Demo</title>
    <x-vite-tags entrypoint="app/main.entrypoint.css"/>
    <style>
        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
        }

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

        @keyframes transitTargetPulse {
            0% {
                box-shadow:
                    0 0 0 2px rgba(var(--pulse-r), var(--pulse-g), var(--pulse-b), 0.9),
                    2px 2px 0 #00000055;
            }
            50% {
                box-shadow:
                    0 0 0 9px rgba(var(--pulse-r), var(--pulse-g), var(--pulse-b), 0.2),
                    2px 2px 0 #00000055;
            }
            100% {
                box-shadow:
                    0 0 0 2px rgba(var(--pulse-r), var(--pulse-g), var(--pulse-b), 0.9),
                    2px 2px 0 #00000055;
            }
        }

        @keyframes departingStationFade {
            0% {
                filter: brightness(1.06);
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

        #terrain-layer,
        #edge-layer,
        #node-layer {
            image-rendering: pixelated;
        }

        #edge-layer {
            shape-rendering: crispEdges;
        }

        #move-modal button:not(:disabled) {
            cursor: pointer;
            transition: filter 120ms ease-in-out, background-color 120ms ease-in-out;
        }

        #move-modal button:not(:disabled):hover {
            filter: brightness(0.94);
        }

        #move-modal-coin-selector button:not(:disabled):hover {
            background-color: #d1d5db;
        }
    </style>
</head>
<body class="bg-gray-800 m-0 h-screen overflow-hidden">
<div id="game-root" class="w-screen h-screen bg-gray-800 p-0" style="width: 100vw; height: 100vh;" :data-game-id="$gameId">
    <div class="relative w-full h-full" style="width: 100%; height: 100%;">
        <a href="/game/demo?mode=single&players=2" class="fixed right-4 top-4 z-50 bg-pink-600 text-white px-3 py-2 rounded font-bold hover:opacity-90 text-sm">New demo</a>
        <button id="help-toggle" type="button" onclick="var m=document.getElementById('help-modal'); if(m){m.style.display='flex';}" class="fixed right-6 top-20 rounded-full font-bold text-base" style="z-index:130;background:#111827;color:#f9fafb;border:1px solid #374151;box-shadow:0 6px 14px rgba(0,0,0,0.35);font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, &quot;Liberation Mono&quot;, &quot;Courier New&quot;, monospace;cursor:pointer;padding:10px 14px;line-height:1;margin:10px;" aria-label="Open help" title="Help">?</button>
        <div id="game-timer-notch" style="position: fixed; left: 50%; top: 0; transform: translateX(-50%); z-index: 100; background: #111827; color: #f9fafb; border: 1px solid #374151; border-top: none; border-radius: 0 0 12px 12px; box-shadow: 0 12px 24px rgba(0,0,0,0.4), 0 2px 0 rgba(255,255,255,0.08) inset; padding: 8px 20px; font-size: 14px; font-weight: 700; line-height: 1; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, &quot;Liberation Mono&quot;, &quot;Courier New&quot;, monospace;">
            00:00
        </div>

        <section class="w-full h-full overflow-hidden" style="width: 100%; height: 100%;">
            <div
                id="map-viewport"
                class="relative w-full h-full bg-gray-800 overflow-hidden touch-none select-none"
                style="width: 100%; height: 100%; background: #1f2937; touch-action: none;"
            >
                <div id="map-stage" class="absolute left-0 top-0 origin-top-left select-none" style="width:1200px;height:800px;border-radius:18px;overflow:hidden;border:2px solid #374151;box-shadow:0 18px 30px rgba(0,0,0,0.35), 0 2px 0 rgba(255,255,255,0.08) inset;">
                    <canvas id="terrain-layer" width="1200" height="800" class="absolute inset-0"></canvas>
                    <svg id="edge-layer" width="1200" height="800" class="absolute inset-0"></svg>
                    <div id="intersection-layer" class="absolute inset-0 pointer-events-none"></div>
                    <div id="node-layer" class="absolute inset-0"></div>
                </div>
                <div id="move-modal" data-map-interactive="true" class="hidden absolute z-30 w-52 rounded-lg border border-gray-300 p-3 shadow-lg text-xs bg-white">
                    <div id="move-modal-title" class="font-bold text-sm text-gray-900 mb-2"></div>
                    <div id="move-modal-travel-time" class="hidden mb-2 text-center text-[11px] font-semibold text-gray-600"></div>
                    <div id="move-modal-coin-selector" class="flex items-center justify-center gap-2">
                        <button id="move-modal-minus" type="button" class="min-w-12 px-3 py-1 rounded bg-gray-100 font-bold cursor-pointer hover:bg-gray-200">-</button>
                        <div id="move-modal-amount" class="min-w-12 text-center text-sm font-bold text-gray-900">0</div>
                        <button id="move-modal-plus" type="button" class="min-w-12 px-3 py-1 rounded bg-gray-100 font-bold cursor-pointer hover:bg-gray-200">+</button>
                        <button id="move-modal-max" type="button" class="min-w-12 px-3 py-1 rounded bg-gray-100 font-bold cursor-pointer hover:bg-gray-200">++</button>
                    </div>
                    <div class="mt-4 flex w-full gap-2">
                        <button id="move-modal-cancel" type="button" class="flex-1 px-2 py-1 rounded bg-gray-100 text-center">Cancel</button>
                        <button id="move-modal-confirm" type="button" class="flex-1 px-2 py-1 rounded bg-emerald-600 text-white text-center">Confirm travel</button>
                        <button id="move-modal-challenge" type="button" class="hidden flex-1 px-2 py-1 rounded bg-yellow-300 font-bold hover:bg-yellow-400 text-center">Claim challenge</button>
                    </div>
                </div>
            </div>
        </section>

        <aside id="player-stats-overlay" class="grid gap-2" style="position: fixed; left: 50%; bottom: 0; transform: translateX(-50%); width: min(520px, calc(100vw - 24px)); z-index: 95; background: #111827; color: #f9fafb; border: 1px solid #374151; border-bottom: none; border-radius: 12px 12px 0 0; box-shadow: 0 -12px 24px rgba(0,0,0,0.4), 0 -2px 0 rgba(255,255,255,0.08) inset; padding: 10px 14px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, &quot;Liberation Mono&quot;, &quot;Courier New&quot;, monospace; font-size: 14px; font-weight: 700; line-height: 1;">
            <div id="feedback" class="min-h-5 text-center text-xs" style="color: #9ca3af; font-weight: 600;"></div>
            <div id="player-stats-content" class="text-center" style="color: #d1d5db;">Loading…</div>
        </aside>

        <aside style="position: fixed; right: 16px; top: 68px; width: 220px; z-index: 96; background: rgba(17,24,39,0.95); border: 1px solid #374151; border-radius: 10px; box-shadow: 0 10px 18px rgba(0,0,0,0.35); padding: 10px;">
            <label for="player-select" class="font-bold block mb-1 text-xs" style="color: #e5e7eb; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, &quot;Liberation Mono&quot;, &quot;Courier New&quot;, monospace;">Control player</label>
            <select id="player-select" class="w-full rounded px-2 py-1 text-sm" style="background: #0b1220; color: #f9fafb; border: 1px solid #4b5563;"></select>
        </aside>
        <div id="help-modal" class="fixed inset-0 flex items-center justify-center" style="display:none;position:fixed;inset:0;z-index:200;padding:28px;background: rgba(3,7,18,0.62);">
            <div data-help-panel="true" class="rounded-xl border" style="width:min(430px, calc(100vw - 56px));padding:20px 22px;background:#111827;color:#f9fafb;border-color:#374151;box-shadow:0 18px 28px rgba(0,0,0,0.45), 0 2px 0 rgba(255,255,255,0.08) inset;font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, &quot;Liberation Mono&quot;, &quot;Courier New&quot;, monospace;box-sizing:border-box;">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-bold tracking-wide">How To Play</h2>
                    <button id="help-close" type="button" onclick="var m=document.getElementById('help-modal'); if(m){m.style.display='none';}" class="rounded px-2 py-1 text-xs font-bold" style="background:#1f2937;border:1px solid #4b5563;color:#e5e7eb;cursor:pointer;">Close</button>
                </div>
                <p class="text-xs leading-relaxed" style="color:#d1d5db;">
                    Travel across connected stations and invest coins to claim control of the rail network. Build momentum by chaining routes, completing challenges, and ending with the strongest claimed map presence.
                </p>
                <div class="mt-4 grid gap-2 text-xs">
                    <div class="flex items-center gap-4">
                        <span style="display:inline-flex;width:24px;height:24px;align-items:center;justify-content:center;border:1px solid #475569;border-radius:2px;background:#e5e7eb;color:#334155;box-shadow:2px 2px 0 #00000033;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,&quot;Liberation Mono&quot;,&quot;Courier New&quot;,monospace;font-size:9px;font-weight:900;line-height:1;">-</span>
                        <span>Unclaimed station</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span style="display:inline-flex;width:24px;height:24px;align-items:center;justify-content:center;border:1px solid #ef4444;border-radius:2px;background:#ef4444;color:#ffffff;box-shadow:0 0 0 2px #ef4444, 2px 2px 0 #00000055;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,&quot;Liberation Mono&quot;,&quot;Courier New&quot;,monospace;font-size:9px;font-weight:900;line-height:1;">5</span>
                        <span>Self-claimed station with 5 coins</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span style="display:inline-flex;width:24px;height:24px;align-items:center;justify-content:center;border:1px solid #3b82f6;border-radius:2px;background:#ffffff;color:#3b82f6;box-shadow:0 0 0 2px #3b82f6, 2px 2px 0 #00000044;font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,&quot;Liberation Mono&quot;,&quot;Courier New&quot;,monospace;font-size:9px;font-weight:900;line-height:1;">3</span>
                        <span>Claimed station by another player</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span style="position:relative;display:inline-block;width:64px;height:10px;">
                            <span style="position:absolute;left:0;top:2px;right:0;height:6px;background:#facc15;"></span>
                            <span style="position:absolute;left:0;top:3px;right:0;height:3px;background:#94a3b8;opacity:0.9;"></span>
                        </span>
                        <span>High-speed line</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span style="position:relative;display:inline-block;width:64px;height:8px;">
                            <span style="position:absolute;left:0;top:2px;right:0;height:4px;background:rgba(255,255,255,0.72);"></span>
                            <span style="position:absolute;left:0;top:3px;right:0;height:2px;background:#94a3b8;opacity:0.9;"></span>
                        </span>
                        <span>Normal line</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:9999px;border:1px solid #713f12;background:#facc15;box-shadow:1px 1px 0 #00000055;"></span>
                        <span>Challenge</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:9999px;border:1px solid #0f172a;background:#ef4444;box-shadow:1px 1px 0 #00000055;"></span>
                        <span>Player-specific challenge</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const gameId = document.getElementById('game-root')?.dataset?.gameId;
        if (!gameId) {
            throw new Error('Missing game id');
        }
        const viewport = document.getElementById('map-viewport');
        const stage = document.getElementById('map-stage');
        const terrainLayer = document.getElementById('terrain-layer');
        const edgeLayer = document.getElementById('edge-layer');
        const intersectionLayer = document.getElementById('intersection-layer');
        const nodeLayer = document.getElementById('node-layer');
        const gameTimerNotch = document.getElementById('game-timer-notch');
        const feedback = document.getElementById('feedback');
        const playerStatsOverlay = document.getElementById('player-stats-overlay');
        const playerStatsContent = document.getElementById('player-stats-content');
        const playerSelect = document.getElementById('player-select');
        const helpToggle = document.getElementById('help-toggle');
        const helpModal = document.getElementById('help-modal');
        const helpClose = document.getElementById('help-close');
        const moveModal = document.getElementById('move-modal');
        const moveModalTitle = document.getElementById('move-modal-title');
        const moveModalCoinSelector = document.getElementById('move-modal-coin-selector');
        const moveModalMinus = document.getElementById('move-modal-minus');
        const moveModalAmount = document.getElementById('move-modal-amount');
        const moveModalPlus = document.getElementById('move-modal-plus');
        const moveModalMax = document.getElementById('move-modal-max');
        const moveModalCancel = document.getElementById('move-modal-cancel');
        const moveModalConfirm = document.getElementById('move-modal-confirm');
        const moveModalChallenge = document.getElementById('move-modal-challenge');
        const moveModalTravelTime = document.getElementById('move-modal-travel-time');
        const zoomInBtn = document.getElementById('zoom-in');
        const zoomOutBtn = document.getElementById('zoom-out');
        const refreshBtn = document.getElementById('refresh-btn');
        const OVERCLAIM_CAP = 5;
        const NODE_SIZE = 24;
        const DEFAULT_CENTER_SCALE = 1.24;
        const MAP_SCALE_FACTOR = 10;
        const MAP_PADDING = 40;
        const MAP_TRAILING_PADDING = 420;
        const PINCH_ZOOM_EXPONENT = 1.5;
        const DOUBLE_TAP_MAX_DELAY_MS = 280;
        const DOUBLE_TAP_MAX_DISTANCE_PX = 24;
        const PLAYER_COLOR_FALLBACK = ['#ef4444', '#3b82f6', '#10b981', '#6366f1', '#ec4899', '#06b6d4'];

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
        let panStartIsTouch = false;
        let lastTouchDragAt = 0;
        let lastTapAt = 0;
        let lastTapPoint = null;
        let moveModalStationId = null;
        let moveModalBounds = null;
        let moveModalCoins = 0;
        let stationBounds = null;
        let statsIntervalId = null;
        let lastStateLoadedAtMs = Date.now();

        stage.style.transformOrigin = '0 0';

        function showHelpModal() {
            if (helpModal) {
                helpModal.style.display = 'flex';
            }
        }

        function hideHelpModal() {
            if (helpModal) {
                helpModal.style.display = 'none';
            }
        }

        function setFeedback(message, isError = false) {
            feedback.textContent = message;
            feedback.className = isError ? 'text-xs min-h-5 text-center text-red-700' : 'text-xs min-h-5 text-center text-emerald-700';
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
            moveModalChallenge.classList.add('hidden');
            if (moveModalTravelTime) {
                moveModalTravelTime.textContent = '';
                moveModalTravelTime.classList.add('hidden');
            }
            moveModalCoinSelector.classList.remove('hidden');
            moveModalCoinSelector.style.display = '';
            moveModalConfirm.classList.remove('hidden');
            moveModalConfirm.style.display = '';
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
            const maxDisabled = moveModalBounds.disabled || moveModalCoins >= moveModalBounds.max;
            moveModalMinus.disabled = decreaseDisabled;
            moveModalPlus.disabled = increaseDisabled;
            moveModalMax.disabled = maxDisabled;
            moveModalMinus.className = decreaseDisabled
                ? 'min-w-12 px-3 py-1 rounded bg-gray-200 text-gray-400 font-bold cursor-not-allowed'
                : 'min-w-12 px-3 py-1 rounded bg-gray-100 font-bold cursor-pointer hover:bg-gray-200';
            moveModalPlus.className = increaseDisabled
                ? 'min-w-12 px-3 py-1 rounded bg-gray-200 text-gray-400 font-bold cursor-not-allowed'
                : 'min-w-12 px-3 py-1 rounded bg-gray-100 font-bold cursor-pointer hover:bg-gray-200';
            moveModalMax.className = maxDisabled
                ? 'min-w-12 px-3 py-1 rounded bg-gray-200 text-gray-400 font-bold cursor-not-allowed'
                : 'min-w-12 px-3 py-1 rounded bg-gray-100 font-bold cursor-pointer hover:bg-gray-200';
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

        function defaultDepositForMove(player, target) {
            const bounds = moveDepositBounds(player, target);
            if (!bounds || bounds.disabled) {
                return 0;
            }

            return bounds.value;
        }

        function showMoveModal(stationId) {
            const player = currentPlayer();
            if (!player) {
                return;
            }

            const isCurrentStation = player.stationId === stationId;
            if (!isCurrentStation && !canMoveTo(player, stationId)) {
                setFeedback('Target is not adjacent to your current station.', true);
                return;
            }

            const station = stationById(stationId);
            const bounds = moveDepositBounds(player, station);
            if (!bounds) {
                return;
            }
            const challenge = isCurrentStation ? challengeAtStation(stationId) : null;
            if (challenge) {
                moveModalChallenge.textContent = `Claim challenge (+${challenge.reward})`;
                moveModalChallenge.classList.remove('hidden');
            } else {
                moveModalChallenge.classList.add('hidden');
            }
            const challengeOnlyMode = isCurrentStation && challenge !== null;
            const claimedTravelMode = !isCurrentStation && station.ownerId === player.id;
            const hideCoinSelector = challengeOnlyMode || claimedTravelMode;
            moveModalCoinSelector.classList.toggle('hidden', hideCoinSelector);
            moveModalConfirm.classList.toggle('hidden', challengeOnlyMode);
            moveModalCoinSelector.style.display = hideCoinSelector ? 'none' : '';
            moveModalConfirm.style.display = challengeOnlyMode ? 'none' : '';
            const travelTimeSeconds = (!isCurrentStation && player.stationId)
                ? edgeTravelTimeSeconds(player.stationId, stationId)
                : null;
            if (moveModalTravelTime) {
                if (!challengeOnlyMode && travelTimeSeconds !== null) {
                    moveModalTravelTime.textContent = `Travel time: ${travelTimeSeconds}s`;
                    moveModalTravelTime.classList.remove('hidden');
                } else {
                    moveModalTravelTime.textContent = '';
                    moveModalTravelTime.classList.add('hidden');
                }
            }

            moveModalStationId = stationId;
            moveModalBounds = bounds;
            moveModalCoins = clampMoveCoins(bounds.value);
            moveModalTitle.textContent = stationLabel(station);
            const confirmDisabled = challengeOnlyMode ? false : (!claimedTravelMode && bounds.disabled);
            moveModalConfirm.disabled = confirmDisabled;
            moveModalConfirm.className = confirmDisabled
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

        function isMapNodeTarget(target) {
            return target instanceof Element && Boolean(target.closest('[data-map-node="true"]'));
        }

        function isTypingTarget(target) {
            return target instanceof HTMLElement
                && (
                    target.tagName === 'INPUT'
                    || target.tagName === 'TEXTAREA'
                    || target.tagName === 'SELECT'
                    || target.isContentEditable
                );
        }

        function clearPointerState(pointerId = null) {
            if (pointerId === null) {
                activePointers.clear();
            } else {
                activePointers.delete(pointerId);
            }

            if (activePointers.size === 1) {
                const [remainingPointer] = [...activePointers.values()];
                if (remainingPointer) {
                    panStart = { x: remainingPointer.x, y: remainingPointer.y, stageX, stageY };
                    panStartIsTouch = true;
                }
            }

            if (activePointers.size < 2) {
                pinchStartDistance = null;
                pinchStartWorld = null;
            }

            if (activePointers.size === 0) {
                panStart = null;
                panStartIsTouch = false;
            }
        }

        function handleDoubleTapRecenter(clientX, clientY, target, isTouch) {
            if (!isTouch || isMapInteractiveTarget(target)) {
                return;
            }

            if (!panStart) {
                return;
            }

            const movedX = clientX - panStart.x;
            const movedY = clientY - panStart.y;
            if ((movedX * movedX) + (movedY * movedY) > 36) {
                return;
            }

            const now = Date.now();
            if (!lastTapPoint) {
                lastTapAt = now;
                lastTapPoint = { x: clientX, y: clientY };
                return;
            }

            const deltaTime = now - lastTapAt;
            const deltaX = clientX - lastTapPoint.x;
            const deltaY = clientY - lastTapPoint.y;
            const maxDistanceSq = DOUBLE_TAP_MAX_DISTANCE_PX * DOUBLE_TAP_MAX_DISTANCE_PX;

            lastTapAt = now;
            lastTapPoint = { x: clientX, y: clientY };

            if (deltaTime <= DOUBLE_TAP_MAX_DELAY_MS && ((deltaX * deltaX) + (deltaY * deltaY) <= maxDistanceSq)) {
                recenterMapCameraPreserveZoom();
            }
        }

        function beginGesture(pointerId, clientX, clientY, target, isTouch = false, allowInteractiveStart = false) {
            if (!allowInteractiveStart && isMapInteractiveTarget(target)) {
                return;
            }

            activePointers.set(pointerId, { x: clientX, y: clientY });

            if (activePointers.size === 1) {
                panStart = { x: clientX, y: clientY, stageX, stageY };
                panStartIsTouch = isTouch;
            }

            if (activePointers.size === 2) {
                const [a, b] = [...activePointers.values()];
                panStart = null;
                panStartIsTouch = false;
                pinchStartDistance = distance(a, b);
                pinchStartScale = scale;
                const mid = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
                const midPoint = viewportPoint(mid.x, mid.y);
                pinchStartWorld = screenToWorld(midPoint.x, midPoint.y);
            }
        }

        function moveGesture(pointerId, clientX, clientY) {
            if (!activePointers.has(pointerId)) return;

            activePointers.set(pointerId, { x: clientX, y: clientY });

            if (activePointers.size === 1 && panStart) {
                const movedX = clientX - panStart.x;
                const movedY = clientY - panStart.y;
                if (panStartIsTouch && ((movedX * movedX) + (movedY * movedY) > 36)) {
                    lastTouchDragAt = Date.now();
                }

                stageX = panStart.stageX + (clientX - panStart.x);
                stageY = panStart.stageY + (clientY - panStart.y);
                applyTransform();
            }

            if (activePointers.size === 2 && pinchStartDistance && pinchStartWorld) {
                const [a, b] = [...activePointers.values()];
                const currentDistance = distance(a, b);
                const mid = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
                const midPoint = viewportPoint(mid.x, mid.y);
                const pinchRatio = currentDistance / pinchStartDistance;
                const acceleratedRatio = Math.pow(pinchRatio, PINCH_ZOOM_EXPONENT);
                scale = clampScale(pinchStartScale * acceleratedRatio);
                stageX = midPoint.x - (pinchStartWorld.x * scale);
                stageY = midPoint.y - (pinchStartWorld.y * scale);
                applyTransform();
            }
        }

        viewport.addEventListener('pointerdown', (event) => {
            const isInteractiveTarget = isMapInteractiveTarget(event.target);
            const allowInteractiveStart = event.pointerType === 'touch' && isMapNodeTarget(event.target);

            if (!isInteractiveTarget) {
                if (moveModalStationId) {
                    hideMoveModal();
                }

                try {
                    viewport.setPointerCapture(event.pointerId);
                } catch (_error) {
                    // Some mobile browsers fail pointer capture for touch pointers.
                }
            }

            beginGesture(
                event.pointerId,
                event.clientX,
                event.clientY,
                event.target,
                event.pointerType === 'touch',
                allowInteractiveStart
            );
        });

        viewport.addEventListener('pointermove', (event) => {
            if (event.pointerType === 'touch' && activePointers.has(event.pointerId)) {
                event.preventDefault();
            }

            moveGesture(event.pointerId, event.clientX, event.clientY);
        });

        viewport.addEventListener('pointerup', (event) => {
            handleDoubleTapRecenter(event.clientX, event.clientY, event.target, event.pointerType === 'touch');
            clearPointerState(event.pointerId);
        });
        viewport.addEventListener('pointercancel', (event) => {
            clearPointerState(event.pointerId);
        });
        viewport.addEventListener('lostpointercapture', (event) => {
            clearPointerState(event.pointerId);
        });

        if (!window.PointerEvent) {
            viewport.addEventListener('touchstart', (event) => {
                const isInteractiveTarget = isMapInteractiveTarget(event.target);
                const allowInteractiveStart = isMapNodeTarget(event.target);

                if (!isInteractiveTarget && moveModalStationId) {
                    hideMoveModal();
                }

                for (const touch of event.changedTouches) {
                    beginGesture(touch.identifier, touch.clientX, touch.clientY, event.target, true, allowInteractiveStart);
                }
            }, { passive: true });

            viewport.addEventListener('touchmove', (event) => {
                for (const touch of event.changedTouches) {
                    if (activePointers.has(touch.identifier)) {
                        event.preventDefault();
                    }

                    moveGesture(touch.identifier, touch.clientX, touch.clientY);
                }
            }, { passive: false });

            viewport.addEventListener('touchend', (event) => {
                for (const touch of event.changedTouches) {
                    handleDoubleTapRecenter(touch.clientX, touch.clientY, event.target, true);
                    clearPointerState(touch.identifier);
                }
            }, { passive: true });

            viewport.addEventListener('touchcancel', (event) => {
                for (const touch of event.changedTouches) {
                    clearPointerState(touch.identifier);
                }
            }, { passive: true });
        }

        viewport.addEventListener('wheel', (event) => {
            event.preventDefault();
            const step = Math.exp(-event.deltaY * 0.0045);
            zoomAt(event.clientX, event.clientY, step);
        }, { passive: false });

        window.addEventListener('keydown', (event) => {
            if (event.code === 'Escape' && helpModal && helpModal.style.display !== 'none') {
                event.preventDefault();
                hideHelpModal();
                return;
            }

            if (event.code !== 'Space') {
                return;
            }

            if (isTypingTarget(event.target)) {
                return;
            }

            event.preventDefault();
            recenterMapCameraPreserveZoom();
        });

        zoomInBtn?.addEventListener('click', () => {
            zoomAtViewportCenter(1.1);
        });

        zoomOutBtn?.addEventListener('click', () => {
            zoomAtViewportCenter(0.9);
        });
        helpToggle?.addEventListener('click', showHelpModal);
        helpClose?.addEventListener('click', hideHelpModal);
        helpModal?.addEventListener('click', (event) => {
            if (event.target === helpModal) {
                hideHelpModal();
            }
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
        moveModalMax.addEventListener('click', async (event) => {
            if (!moveModalBounds || moveModalBounds.disabled) {
                return;
            }

            moveModalCoins = clampMoveCoins(moveModalBounds.max);
            updateMoveModalAmountUi();

            if (event.altKey && moveModalStationId) {
                await moveTo(moveModalStationId, moveModalCoins);
            }
        });
        moveModalConfirm.addEventListener('click', async () => {
            if (!moveModalStationId || !moveModalBounds) {
                return;
            }

            const deposit = clampMoveCoins(moveModalCoins);
            await moveTo(moveModalStationId, deposit);
        });
        moveModalChallenge.addEventListener('click', async () => {
            const player = currentPlayer();
            await completeChallengeForPlayer(player);
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

            let maxX = 0;
            let maxY = 0;
            let minX = Number.POSITIVE_INFINITY;
            let minY = Number.POSITIVE_INFINITY;
            let skipped = 0;

            for (const station of state.stations) {
                if (typeof station.x !== 'number' || typeof station.y !== 'number') {
                    skipped++;
                    continue;
                }

                const x = MAP_PADDING + (station.x * MAP_SCALE_FACTOR);
                const y = MAP_PADDING + (station.y * MAP_SCALE_FACTOR);

                stationPositions[station.id] = { x, y };
                maxX = Math.max(maxX, x);
                maxY = Math.max(maxY, y);
                minX = Math.min(minX, x);
                minY = Math.min(minY, y);
            }

            const stageWidth = Math.max(1200, maxX + MAP_PADDING + MAP_TRAILING_PADDING);
            const stageHeight = Math.max(800, maxY + MAP_PADDING + MAP_TRAILING_PADDING);
            stage.style.width = `${stageWidth}px`;
            stage.style.height = `${stageHeight}px`;
            terrainLayer.width = stageWidth;
            terrainLayer.height = stageHeight;
            terrainLayer.style.width = `${stageWidth}px`;
            terrainLayer.style.height = `${stageHeight}px`;
            edgeLayer.setAttribute('width', String(stageWidth));
            edgeLayer.setAttribute('height', String(stageHeight));
            stationBounds = Number.isFinite(minX) && Number.isFinite(minY)
                ? { minX, minY, maxX, maxY }
                : null;

            if (skipped > 0) {
                setFeedback(`Skipped ${skipped} stations without coordinates`, true);
            }
        }

        function centerMapCamera() {
            const viewportWidth = viewport.clientWidth;
            const viewportHeight = viewport.clientHeight;
            if (viewportWidth <= 0 || viewportHeight <= 0) {
                return;
            }

            scale = clampScale(DEFAULT_CENTER_SCALE);
            const selected = currentPlayer();
            const selectedWorld = selected?.stationId ? stationPositions[selected.stationId] : null;
            const worldCenterX = selectedWorld?.x ?? (stationBounds ? ((stationBounds.minX + stationBounds.maxX) / 2) : (stage.clientWidth / 2));
            const worldCenterY = selectedWorld?.y ?? (stationBounds ? ((stationBounds.minY + stationBounds.maxY) / 2) : (stage.clientHeight / 2));
            stageX = (viewportWidth / 2) - (worldCenterX * scale);
            stageY = (viewportHeight / 2) - (worldCenterY * scale);
            applyTransform();
            hideMoveModal();
        }

        function recenterMapCameraPreserveZoom() {
            const viewportWidth = viewport.clientWidth;
            const viewportHeight = viewport.clientHeight;
            if (viewportWidth <= 0 || viewportHeight <= 0) {
                return;
            }

            const selected = currentPlayer();
            const selectedWorld = selected?.stationId ? stationPositions[selected.stationId] : null;
            const worldCenterX = selectedWorld?.x ?? (stationBounds ? ((stationBounds.minX + stationBounds.maxX) / 2) : (stage.clientWidth / 2));
            const worldCenterY = selectedWorld?.y ?? (stationBounds ? ((stationBounds.minY + stationBounds.maxY) / 2) : (stage.clientHeight / 2));
            stageX = (viewportWidth / 2) - (worldCenterX * scale);
            stageY = (viewportHeight / 2) - (worldCenterY * scale);
            applyTransform();
            hideMoveModal();
        }

        function stationById(id) {
            return state.stations.find((station) => station.id === id) ?? null;
        }

        function edgeTravelTimeSeconds(fromStationId, toStationId) {
            if (!fromStationId || !toStationId) {
                return null;
            }

            const edge = state.edges.find((candidate) => (
                (candidate.fromStationId === fromStationId && candidate.toStationId === toStationId)
                || (candidate.toStationId === fromStationId && candidate.fromStationId === toStationId)
            ));

            const seconds = Number(edge?.travelTimeSeconds ?? NaN);
            return Number.isFinite(seconds) && seconds > 0 ? seconds : null;
        }

        function stationLabel(station) {
            return station?.name ?? station?.id ?? '-';
        }

        function currentPlayer() {
            const id = playerSelect.value || state.players[0]?.id;
            return state.players.find((player) => player.id === id) ?? null;
        }

        function playerRemainingTravelSeconds(player) {
            const pendingMove = player?.pendingMove;
            if (!pendingMove) {
                return null;
            }

            if (Number.isFinite(Number(pendingMove.remainingSeconds))) {
                const elapsedSeconds = Math.max(0, Math.floor((Date.now() - lastStateLoadedAtMs) / 1000));
                return Math.max(0, Number(pendingMove.remainingSeconds) - elapsedSeconds);
            }

            if (pendingMove.arrivalAt) {
                const arrivalTimestamp = Date.parse(String(pendingMove.arrivalAt).replace(' ', 'T'));
                const remaining = Math.ceil((arrivalTimestamp - Date.now()) / 1000);
                return Math.max(0, Number.isFinite(remaining) ? remaining : 0);
            }

            return null;
        }

        function stateUrlForCurrentPlayer() {
            const playerId = playerSelect.value || state?.players?.[0]?.id || '';
            const params = new URLSearchParams({ timeline: 'true' });
            if (playerId) {
                params.set('playerId', playerId);
            }

            return `/games/${gameId}/state?${params.toString()}`;
        }

        function playerColor(playerId) {
            if (!playerId) {
                return '#94a3b8';
            }

            if (playerId === 'p1') return '#ef4444';
            if (playerId === 'p2') return '#3b82f6';
            if (playerId === 'p3') return '#10b981';
            if (playerId === 'p4') return '#6366f1';
            if (playerId === 'p5') return '#ec4899';
            if (playerId === 'p6') return '#06b6d4';

            let hash = 0;
            for (let i = 0; i < playerId.length; i++) {
                hash = (hash * 31 + playerId.charCodeAt(i)) >>> 0;
            }

            return PLAYER_COLOR_FALLBACK[hash % PLAYER_COLOR_FALLBACK.length];
        }

        function hexToRgb(hex) {
            const value = String(hex ?? '').replace('#', '');
            const normalized = value.length === 3
                ? value.split('').map((part) => part + part).join('')
                : value;

            if (!/^[0-9a-fA-F]{6}$/.test(normalized)) {
                return { r: 148, g: 163, b: 184 };
            }

            return {
                r: Number.parseInt(normalized.slice(0, 2), 16),
                g: Number.parseInt(normalized.slice(2, 4), 16),
                b: Number.parseInt(normalized.slice(4, 6), 16),
            };
        }

        function rgbaFromHex(hex, alpha) {
            const rgb = hexToRgb(hex);
            return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${alpha})`;
        }

        function washedHexColor(hex, ratio = 0.35) {
            const rgb = hexToRgb(hex);
            const clamp = (value) => Math.max(0, Math.min(255, Math.round(value)));
            const mix = (channel) => clamp(channel + ((255 - channel) * ratio));
            const toHex = (channel) => mix(channel).toString(16).padStart(2, '0');
            return `#${toHex(rgb.r)}${toHex(rgb.g)}${toHex(rgb.b)}`;
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

        function terrainSeed() {
            let hash = 2166136261;
            for (let i = 0; i < gameId.length; i++) {
                hash ^= gameId.charCodeAt(i);
                hash = Math.imul(hash, 16777619);
            }

            return hash >>> 0;
        }

        function gridKey(x, y) {
            return `${x},${y}`;
        }

        function parseGridKey(key) {
            const [x, y] = key.split(',').map(Number);
            return { x, y };
        }

        function bresenhamLineCells(x0, y0, x1, y1) {
            const cells = [];
            let x = x0;
            let y = y0;
            const dx = Math.abs(x1 - x0);
            const sx = x0 < x1 ? 1 : -1;
            const dy = -Math.abs(y1 - y0);
            const sy = y0 < y1 ? 1 : -1;
            let error = dx + dy;

            while (true) {
                cells.push({ x, y });
                if (x === x1 && y === y1) break;
                const e2 = 2 * error;
                if (e2 >= dy) {
                    error += dy;
                    x += sx;
                }
                if (e2 <= dx) {
                    error += dx;
                    y += sy;
                }
            }

            return cells;
        }

        function terrainNoise(x, y, seed) {
            let value = seed ^ Math.imul(x, 374761393) ^ Math.imul(y, 668265263);
            value = (value ^ (value >>> 13)) >>> 0;
            value = Math.imul(value, 1274126177) >>> 0;
            return ((value ^ (value >>> 16)) >>> 0) / 4294967295;
        }

        function buildPerlin(seed) {
            const permutation = [...Array(256).keys()];
            let state = seed >>> 0;
            const random = () => {
                state ^= state << 13;
                state ^= state >>> 17;
                state ^= state << 5;
                return (state >>> 0) / 4294967295;
            };

            for (let i = permutation.length - 1; i > 0; i--) {
                const j = Math.floor(random() * (i + 1));
                [permutation[i], permutation[j]] = [permutation[j], permutation[i]];
            }

            const p = new Array(512);
            for (let i = 0; i < 512; i++) {
                p[i] = permutation[i & 255];
            }

            return p;
        }

        function perlin2d(x, y, p) {
            const fade = (t) => t * t * t * (t * (t * 6 - 15) + 10);
            const lerp = (a, b, t) => a + (t * (b - a));
            const grad = (hash, gx, gy) => {
                const h = hash & 3;
                if (h === 0) return gx + gy;
                if (h === 1) return -gx + gy;
                if (h === 2) return gx - gy;
                return -gx - gy;
            };

            const xi = Math.floor(x) & 255;
            const yi = Math.floor(y) & 255;
            const xf = x - Math.floor(x);
            const yf = y - Math.floor(y);
            const u = fade(xf);
            const v = fade(yf);

            const aa = p[p[xi] + yi];
            const ab = p[p[xi] + yi + 1];
            const ba = p[p[xi + 1] + yi];
            const bb = p[p[xi + 1] + yi + 1];

            const x1 = lerp(grad(aa, xf, yf), grad(ba, xf - 1, yf), u);
            const x2 = lerp(grad(ab, xf, yf - 1), grad(bb, xf - 1, yf - 1), u);
            return lerp(x1, x2, v);
        }

        function octavePerlin(x, y, p, octaves = 4, persistence = 0.55, lacunarity = 2) {
            let amplitude = 1;
            let frequency = 1;
            let total = 0;
            let amplitudeSum = 0;

            for (let i = 0; i < octaves; i++) {
                total += perlin2d(x * frequency, y * frequency, p) * amplitude;
                amplitudeSum += amplitude;
                amplitude *= persistence;
                frequency *= lacunarity;
            }

            // Normalize from roughly [-1, 1] to [0, 1]
            return (total / amplitudeSum + 1) / 2;
        }

        function biomeColor(biome, x, y, seed) {
            const variation = ((x + y) & 1) ^ (terrainNoise(x, y, seed) > 0.66 ? 1 : 0);
            const palettes = {
                land: ['#8fbf61', '#7fae53'],
                water: ['#4d89b9', '#3f78a5'],
                forest: ['#4f8c3d', '#447a35'],
                mountain: ['#8f8a83', '#7b766f'],
            };

            return palettes[biome][variation];
        }

        function renderTerrain() {
            const context = terrainLayer.getContext('2d');
            if (!context) {
                return;
            }

            context.clearRect(0, 0, terrainLayer.width, terrainLayer.height);

            const stationsWithCoords = state.stations.filter((station) => (
                typeof station.x === 'number' && typeof station.y === 'number'
            ));
            const land = new Set();

            for (const station of stationsWithCoords) {
                land.add(gridKey(station.x, station.y));
            }

            for (const edge of uniqueEdges()) {
                const from = stationById(edge.fromStationId);
                const to = stationById(edge.toStationId);
                if (!from || !to || typeof from.x !== 'number' || typeof from.y !== 'number' || typeof to.x !== 'number' || typeof to.y !== 'number') {
                    continue;
                }

                for (const cell of bresenhamLineCells(from.x, from.y, to.x, to.y)) {
                    land.add(gridKey(cell.x, cell.y));
                }
            }

            const expandedLand = new Set(land);
            for (const key of land) {
                const { x, y } = parseGridKey(key);
                for (let dx = -1; dx <= 1; dx++) {
                    for (let dy = -1; dy <= 1; dy++) {
                        const nx = x + dx;
                        const ny = y + dy;
                        expandedLand.add(gridKey(nx, ny));
                    }
                }
            }

            const seed = terrainSeed();
            const perlin = buildPerlin(seed);
            const tileSize = MAP_SCALE_FACTOR;
            const columns = Math.ceil(terrainLayer.width / tileSize);
            const rows = Math.ceil(terrainLayer.height / tileSize);

            for (let row = 0; row <= rows; row++) {
                for (let column = 0; column <= columns; column++) {
                    const elevation = octavePerlin((column + 1000) * 0.06, (row + 1000) * 0.06, perlin, 4, 0.58, 2.1);
                    const moisture = octavePerlin((column - 700) * 0.08, (row - 700) * 0.08, perlin, 3, 0.5, 2);
                    let biome = 'land';

                    if (elevation < 0.36) {
                        biome = 'water';
                    } else if (elevation > 0.78) {
                        biome = 'mountain';
                    } else if (moisture > 0.58) {
                        biome = 'forest';
                    }

                    context.fillStyle = biomeColor(biome, column, row, seed);
                    context.fillRect(column * tileSize, row * tileSize, tileSize, tileSize);
                }
            }

            for (const key of expandedLand) {
                const { x, y } = parseGridKey(key);
                context.fillStyle = biomeColor('land', x, y, seed);
                context.fillRect(
                    MAP_PADDING + (x * tileSize),
                    MAP_PADDING + (y * tileSize),
                    tileSize,
                    tileSize,
                );
            }
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
                const baseStroke = touchesBranchLine ? '#7f8fb0' : '#94a3b8';
                const edgeStroke = ownerStroke ?? baseStroke;
                const borderStroke = edge.isExpress ? '#facc15' : 'rgba(255,255,255,0.72)';

                const base = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                base.setAttribute('x1', String(from.x));
                base.setAttribute('y1', String(from.y));
                base.setAttribute('x2', String(to.x));
                base.setAttribute('y2', String(to.y));
                base.setAttribute('stroke', borderStroke);
                base.setAttribute('stroke-width', edge.isExpress ? '6' : '4');
                base.setAttribute('stroke-linecap', 'square');
                edgeLayer.appendChild(base);

                const accent = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                accent.setAttribute('x1', String(from.x));
                accent.setAttribute('y1', String(from.y));
                accent.setAttribute('x2', String(to.x));
                accent.setAttribute('y2', String(to.y));
                accent.setAttribute('stroke', edgeStroke);
                accent.setAttribute('stroke-width', edge.isExpress ? '3' : '2');
                accent.setAttribute('stroke-opacity', '0.9');
                accent.setAttribute('stroke-linecap', 'square');
                edgeLayer.appendChild(accent);
            }
        }

        function edgeSegments() {
            const segments = [];

            for (const edge of uniqueEdges()) {
                const from = stationPositions[edge.fromStationId];
                const to = stationPositions[edge.toStationId];
                if (!from || !to) {
                    continue;
                }

                segments.push({
                    fromStationId: edge.fromStationId,
                    toStationId: edge.toStationId,
                    x1: from.x,
                    y1: from.y,
                    x2: to.x,
                    y2: to.y,
                });
            }

            return segments;
        }

        function segmentIntersectionPoint(a, b) {
            const denominator = ((a.x1 - a.x2) * (b.y1 - b.y2)) - ((a.y1 - a.y2) * (b.x1 - b.x2));
            if (Math.abs(denominator) < 1e-9) {
                return null;
            }

            const determinantA = (a.x1 * a.y2) - (a.y1 * a.x2);
            const determinantB = (b.x1 * b.y2) - (b.y1 * b.x2);
            const x = ((determinantA * (b.x1 - b.x2)) - ((a.x1 - a.x2) * determinantB)) / denominator;
            const y = ((determinantA * (b.y1 - b.y2)) - ((a.y1 - a.y2) * determinantB)) / denominator;

            const within = (value, min, max) => value >= (Math.min(min, max) - 0.001) && value <= (Math.max(min, max) + 0.001);
            if (
                !within(x, a.x1, a.x2)
                || !within(y, a.y1, a.y2)
                || !within(x, b.x1, b.x2)
                || !within(y, b.y1, b.y2)
            ) {
                return null;
            }

            const endpointMatch = (pointX, pointY, segment) => (
                (Math.abs(pointX - segment.x1) < 0.001 && Math.abs(pointY - segment.y1) < 0.001)
                || (Math.abs(pointX - segment.x2) < 0.001 && Math.abs(pointY - segment.y2) < 0.001)
            );
            if (endpointMatch(x, y, a) || endpointMatch(x, y, b)) {
                return null;
            }

            return { x, y };
        }

        function renderIntersections() {
            intersectionLayer.innerHTML = '';
            const segments = edgeSegments();
            const points = new Map();

            for (let i = 0; i < segments.length; i++) {
                for (let j = i + 1; j < segments.length; j++) {
                    const first = segments[i];
                    const second = segments[j];
                    if (
                        first.fromStationId === second.fromStationId
                        || first.fromStationId === second.toStationId
                        || first.toStationId === second.fromStationId
                        || first.toStationId === second.toStationId
                    ) {
                        continue;
                    }

                    const intersection = segmentIntersectionPoint(first, second);
                    if (!intersection) {
                        continue;
                    }

                    const key = `${Math.round(intersection.x)},${Math.round(intersection.y)}`;
                    points.set(key, intersection);
                }
            }

            for (const point of points.values()) {
                const marker = document.createElement('div');
                marker.className = 'absolute';
                marker.style.left = `${point.x - 4}px`;
                marker.style.top = `${point.y - 4}px`;
                marker.style.width = '8px';
                marker.style.height = '8px';
                marker.style.borderRadius = '2px';
                marker.style.backgroundColor = '#facc15';
                marker.style.border = '1px solid #854d0e';
                marker.style.boxShadow = '1px 1px 0 #00000066';
                marker.title = 'Rail intersection';
                intersectionLayer.appendChild(marker);
            }
        }

        function challengeAtStation(stationId) {
            const activeAtStation = state.challenges.filter((challenge) => challenge.active && challenge.station_id === stationId);
            if (activeAtStation.length === 0) {
                return null;
            }

            const player = currentPlayer();
            const personal = activeAtStation.find((challenge) => challenge.challenge_type === 'player' && challenge.player_id === player?.id);
            return personal ?? activeAtStation[0];
        }

        function activeChallengeForPlayer(player) {
            if (!player?.stationId) {
                return null;
            }

            return challengeAtStation(player.stationId);
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

        async function completeChallengeForPlayer(player) {
            if (!player || !player.stationId) return;

            const challenge = activeChallengeForPlayer(player);
            if (!challenge) {
                setFeedback('No active challenge at this station.', true);
                return;
            }

            const result = await postForm(`/games/${gameId}/commands/complete-challenge`, {
                playerId: player.id,
                stationId: player.stationId,
            });

            setFeedback(result.accepted ? `Challenge completed: +${result.reward} coins` : `Challenge rejected: ${result.reason}`, !result.accepted);
            hideMoveModal();
            await loadState();
        }

        async function moveTo(targetStationId, requestedDeposit = null, autoClaimChallenge = false) {
            const player = currentPlayer();
            if (!player) return;

            if (player.pendingMove) {
                const remaining = playerRemainingTravelSeconds(player);
                setFeedback(`Already traveling (${remaining ?? '?'}s left).`, true);
                return;
            }

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

            let moveMessage = `Move rejected: ${result.reason}`;
            let moveError = !result.accepted;
            if (result.accepted && result.reason === 'in_transit') {
                moveMessage = `Traveling to ${stationLabel(target)} (${result.travelTimeSeconds ?? '?'}s)`;
                moveError = false;
            } else if (result.accepted) {
                moveMessage = `Traveled to ${stationLabel(target)}.`;
                moveError = false;
            }

            setFeedback(moveMessage, moveError);
            hideMoveModal();
            await loadState();

            if (result.accepted) {
                const updatedPlayer = state.players.find((candidate) => candidate.id === player.id);
                if (updatedPlayer?.stationId === targetStationId && challengeAtStation(targetStationId)) {
                    if (autoClaimChallenge) {
                        await completeChallengeForPlayer(updatedPlayer);
                    } else {
                        showMoveModal(targetStationId);
                    }
                }
            }
        }

        function renderNodes() {
            nodeLayer.innerHTML = '';
            const player = currentPlayer();
            const inTransit = Boolean(player?.pendingMove);
            const transitTargetStationId = player?.pendingMove?.toStationId ?? null;
            const playerIdsByStation = new Map();

            for (const candidate of state.players) {
                if (!candidate.stationId) {
                    continue;
                }

                const occupying = playerIdsByStation.get(candidate.stationId) ?? [];
                occupying.push(candidate.id);
                playerIdsByStation.set(candidate.stationId, occupying);
            }

            for (const station of state.stations) {
                const position = stationPositions[station.id];
                if (!position) continue;

                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.mapInteractive = 'true';
                button.dataset.mapNode = 'true';
                const challenge = challengeAtStation(station.id);
                const isPlayerStation = !inTransit && player?.stationId === station.id;
                const isReachable = !inTransit && player ? canMoveTo(player, station.id) : false;
                button.dataset.reachable = isReachable ? 'true' : 'false';
                button.dataset.current = isPlayerStation ? 'true' : 'false';
                const selectedPlayerColor = playerColor(player?.id ?? null);
                const ownerColor = playerColor(station.ownerId ?? null);
                const isClaimed = station.ownerId !== null;
                const isOutOfReachClaimed = isClaimed && !isPlayerStation && !isReachable;
                const claimAura = claimShadow(station.ownerId, station.topValue);
                const occupyingPlayerIds = playerIdsByStation.get(station.id) ?? [];
                const otherOccupyingPlayerId = occupyingPlayerIds.find((playerId) => playerId !== player?.id) ?? null;
                const otherOccupyingPlayerColor = otherOccupyingPlayerId ? playerColor(otherOccupyingPlayerId) : null;

                button.className = 'absolute border text-[7px] font-black flex items-center justify-center p-0 leading-none';
                button.style.left = `${position.x - (NODE_SIZE / 2)}px`;
                button.style.top = `${position.y - (NODE_SIZE / 2)}px`;
                button.style.width = `${NODE_SIZE}px`;
                button.style.height = `${NODE_SIZE}px`;
                button.style.borderRadius = '2px';
                button.style.fontFamily = 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace';
                button.style.userSelect = 'none';
                button.style.webkitUserSelect = 'none';
                button.style.touchAction = 'none';
                button.style.opacity = challenge
                    ? '1'
                    : (isOutOfReachClaimed ? '1' : ((!isPlayerStation && !isReachable) ? '0.7' : '1'));
                button.style.cursor = isReachable || isPlayerStation ? 'pointer' : 'not-allowed';

                if (isPlayerStation) {
                    button.style.background = selectedPlayerColor;
                    button.style.borderColor = selectedPlayerColor;
                    button.style.color = '#ffffff';
                    button.style.outline = 'none';
                    button.style.boxShadow = claimAura ? `${claimAura}, 2px 2px 0 #00000055` : '2px 2px 0 #00000055';
                } else if (otherOccupyingPlayerColor) {
                    button.style.background = washedHexColor(otherOccupyingPlayerColor, 0.42);
                    button.style.borderColor = otherOccupyingPlayerColor;
                    button.style.color = '#ffffff';
                    button.style.outline = 'none';
                    button.style.boxShadow = claimAura ? `${claimAura}, 2px 2px 0 #00000044` : '2px 2px 0 #00000044';
                    button.style.animation = 'none';
                } else if (isReachable) {
                    button.style.background = '#ffffff';
                    button.style.borderColor = selectedPlayerColor;
                    button.style.color = isClaimed ? ownerColor : selectedPlayerColor;
                    button.style.outline = 'none';
                    const doubleOutline = `0 0 0 1px #ffffff, 0 0 0 3px ${selectedPlayerColor}`;
                    button.style.boxShadow = claimAura ? `${doubleOutline}, ${claimAura}, 2px 2px 0 #00000044` : `${doubleOutline}, 2px 2px 0 #00000044`;
                    button.style.animation = 'none';
                } else {
                    button.style.background = isClaimed ? '#ffffff' : '#e5e7eb';
                    button.style.color = isClaimed ? ownerColor : '#334155';
                    button.style.outline = 'none';
                    button.style.boxShadow = claimAura ? `${claimAura}, 2px 2px 0 #00000033` : '2px 2px 0 #00000033';
                    button.style.animation = 'none';
                    button.style.borderColor = isOutOfReachClaimed ? ownerColor : '#475569';
                }

                if (isPlayerStation) {
                    button.style.animation = 'none';
                }

                if (inTransit && player?.stationId === station.id) {
                    button.style.animation = 'departingStationFade 180ms ease-out';
                }

                if (inTransit && transitTargetStationId === station.id) {
                    const pulseColor = playerColor(player?.id ?? null);
                    const rgb = hexToRgb(pulseColor);
                    button.style.setProperty('--pulse-r', String(rgb.r));
                    button.style.setProperty('--pulse-g', String(rgb.g));
                    button.style.setProperty('--pulse-b', String(rgb.b));
                    button.style.borderColor = pulseColor;
                    button.style.animation = 'transitTargetPulse 1.25s ease-in-out infinite';
                }

                const deposited = station.topValue > 0 ? station.topValue : null;
                button.title = `${stationLabel(station)} (${station.id}) | owner: ${station.ownerId ?? 'neutral'} | deposited: ${deposited ?? '-'}${challenge ? ` | challenge +${challenge.reward}` : ''}`;
                button.textContent = deposited === null ? '-' : String(deposited);
                button.addEventListener('click', (event) => {
                    if (Date.now() - lastTouchDragAt < 300) {
                        return;
                    }

                    if (inTransit) {
                        const remaining = playerRemainingTravelSeconds(player);
                        setFeedback(`Travel in progress (${remaining ?? '?'}s left).`, true);
                        return;
                    }

                    const bypassPopup = event.altKey === true;

                    if (bypassPopup && isPlayerStation && challenge) {
                        completeChallengeForPlayer(player);
                        return;
                    }

                    if (bypassPopup && isReachable) {
                        const targetStation = stationById(station.id);
                        const deposit = defaultDepositForMove(player, targetStation);
                        moveTo(station.id, deposit, true);
                        return;
                    }

                    if (isPlayerStation && challenge) {
                        showMoveModal(station.id);
                        return;
                    }

                    if (!isReachable) {
                        setFeedback('Target is not adjacent to your current station.', true);
                        return;
                    }

                    if (station.ownerId === player?.id) {
                        showMoveModal(station.id);
                        return;
                    }

                    showMoveModal(station.id);
                });

                if (challenge) {
                    const badge = document.createElement('div');
                    badge.dataset.mapInteractive = 'true';
                    badge.className = 'absolute rounded-full border shadow-[1px_1px_0_#00000055]';
                    badge.style.opacity = '1';
                    badge.style.right = '-4px';
                    badge.style.top = '-4px';
                    badge.style.width = '10px';
                    badge.style.height = '10px';
                    if (challenge.challenge_type === 'player' && challenge.player_id) {
                        const color = playerColor(challenge.player_id);
                        badge.style.backgroundColor = color;
                        badge.style.borderColor = '#0f172a';
                    } else {
                        badge.style.backgroundColor = '#facc15';
                        badge.style.borderColor = '#713f12';
                    }
                    badge.title = `Challenge reward: +${challenge.reward} coins`;
                    button.appendChild(badge);
                }

                nodeLayer.appendChild(button);
            }

            applyNodeScale();

            if (inTransit || (moveModalStationId && !stationPositions[moveModalStationId])) {
                hideMoveModal();
            } else {
                positionMoveModal();
            }
        }

        function gameTimeLeftSeconds() {
            if (!state?.game) {
                return 0;
            }

            const durationSeconds = Number(state.game.durationSeconds ?? 600);
            const createdAtTimestamp = Date.parse(String(state.game.createdAt ?? ''));
            if (!Number.isFinite(createdAtTimestamp)) {
                return Math.max(0, Math.floor(durationSeconds));
            }

            const elapsedSeconds = Math.floor((Date.now() - createdAtTimestamp) / 1000);
            return Math.max(0, Math.floor(durationSeconds) - elapsedSeconds);
        }

        function formatDuration(totalSeconds) {
            const clamped = Math.max(0, Math.floor(totalSeconds));
            const minutes = Math.floor(clamped / 60);
            const seconds = clamped % 60;
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        function renderPlayerStatsOverlay() {
            if (!playerStatsOverlay || !playerStatsContent || !state) {
                return;
            }

            const claimedByPlayer = new Map();
            for (const station of state.stations) {
                if (!station.ownerId) {
                    continue;
                }

                claimedByPlayer.set(station.ownerId, (claimedByPlayer.get(station.ownerId) ?? 0) + 1);
            }

            const timeLeft = formatDuration(gameTimeLeftSeconds());
            if (gameTimerNotch) {
                gameTimerNotch.textContent = timeLeft;
            }
            const activePlayer = currentPlayer();

            playerStatsContent.innerHTML = '';

            if (!activePlayer) {
                const empty = document.createElement('div');
                empty.className = 'text-xs';
                empty.style.color = '#9ca3af';
                empty.textContent = 'No active player selected.';
                playerStatsContent.appendChild(empty);
                return;
            }

            const row = document.createElement('div');
            row.style.display = 'inline-flex';
            row.style.alignItems = 'center';
            row.style.justifyContent = 'center';
            row.style.gap = '8px';
            row.style.fontSize = '14px';
            row.style.fontWeight = '700';
            row.style.lineHeight = '1';
            row.style.color = '#f3f4f6';

            const indicator = document.createElement('span');
            indicator.style.display = 'inline-block';
            indicator.style.width = '10px';
            indicator.style.height = '10px';
            indicator.style.borderRadius = '9999px';
            indicator.style.backgroundColor = playerColor(activePlayer.id);
            indicator.style.border = '1px solid #0f172a';

            const text = document.createElement('span');
            text.textContent = `${activePlayer.id} · coins: ${activePlayer.coins} · claimed: ${claimedByPlayer.get(activePlayer.id) ?? 0}`;

            row.appendChild(indicator);
            row.appendChild(text);
            playerStatsContent.appendChild(row);

            if (activePlayer.pendingMove) {
                const destination = stationById(activePlayer.pendingMove.toStationId);
                const destinationLabel = stationLabel(destination) || activePlayer.pendingMove.toStationId;
                const remaining = playerRemainingTravelSeconds(activePlayer) ?? activePlayer.pendingMove.remainingSeconds ?? '?';
                feedback.className = 'text-xs min-h-5 text-center text-gray-300';
                feedback.textContent = `Traveling to ${destinationLabel} (${remaining}s)`;
            }
        }

        function maybeShowArrivalFeedback(previousState, nextState) {
            if (!previousState || !nextState) {
                return null;
            }

            const selectedPlayerId = playerSelect.value || nextState.players[0]?.id;
            if (!selectedPlayerId) {
                return null;
            }

            const previousPlayer = previousState.players?.find((player) => player.id === selectedPlayerId) ?? null;
            const nextPlayer = nextState.players?.find((player) => player.id === selectedPlayerId) ?? null;
            if (!previousPlayer || !nextPlayer) {
                return null;
            }

            if (!previousPlayer.pendingMove || nextPlayer.pendingMove) {
                return null;
            }

            const arrivedStation = nextState.stations?.find((station) => station.id === nextPlayer.stationId) ?? null;
            const destinationLabel = stationLabel(arrivedStation) || nextPlayer.stationId || previousPlayer.pendingMove.toStationId;
            setFeedback(`Arrived at ${destinationLabel}.`);
            return nextPlayer.stationId ?? null;
        }

        async function loadState(recenter = false) {
            const previousState = state;
            const response = await fetch(stateUrlForCurrentPlayer(), { headers: { 'Accept': 'application/json' } });
            state = await response.json();
            lastStateLoadedAtMs = Date.now();
            ensurePlayerOptions();
            const arrivalStationId = maybeShowArrivalFeedback(previousState, state);
            computeStationPositions();
            renderTerrain();
            renderEdges();
            renderIntersections();
            renderNodes();
            if (arrivalStationId && challengeAtStation(arrivalStationId)) {
                showMoveModal(arrivalStationId);
            }
            renderPlayerStatsOverlay();
            if (recenter) {
                centerMapCamera();
            }
        }

        playerSelect.addEventListener('change', async () => {
            hideMoveModal();
            await loadState();
        });

        refreshBtn?.addEventListener('click', () => loadState(true));

        loadState(true).then(() => {
            if (!statsIntervalId) {
                statsIntervalId = setInterval(() => {
                    renderPlayerStatsOverlay();
                }, 1000);
            }
            setInterval(loadState, 3000);
        }).catch((error) => {
            setFeedback(`Failed to load state: ${error.message}`, true);
        });
    })();
</script>
</body>
</html>
