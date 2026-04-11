<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>New Game</title>
    <x-vite-tags entrypoint="app/main.entrypoint.css"/>
</head>
<body style="margin:0;min-height:100vh;background:#111827;color:#f9fafb;font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;display:flex;align-items:center;justify-content:center;padding:24px;box-sizing:border-box;">
<main style="width:min(520px,100%);background:#0b1220;border:1px solid #374151;border-radius:14px;padding:22px;box-shadow:0 18px 30px rgba(0,0,0,0.35);">
    <h1 style="margin:0 0 12px;font-size:18px;font-weight:700;">Create New Game</h1>
    <p style="margin:0 0 18px;color:#9ca3af;font-size:13px;line-height:1.5;">Choose a multiplayer setup or quick 1v1 bot match. You can share the join URL after creating a multiplayer game.</p>

    <form method="post" action="/game/new" style="display:grid;gap:14px;">
        <label style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #374151;border-radius:10px;background:#111827;">
            <input type="radio" name="mode" value="players" checked/>
            <span style="font-size:13px;">Multiplayer (2-6 human players)</span>
        </label>
        <label style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #374151;border-radius:10px;background:#111827;">
            <input type="radio" name="mode" value="bot"/>
            <span style="font-size:13px;">Singleplayer (1v1 bot)</span>
        </label>

        <label id="player-count-row" style="display:grid;gap:6px;">
            <span style="font-size:12px;color:#cbd5e1;">Human player count (multiplayer mode)</span>
            <select name="players" style="padding:8px 10px;border-radius:8px;border:1px solid #4b5563;background:#0f172a;color:#f9fafb;">
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
            </select>
        </label>

        <button type="submit" style="margin-top:4px;padding:10px 12px;border-radius:10px;border:1px solid #374151;background:#ec4899;color:#fff;font-weight:700;cursor:pointer;">Create game</button>
    </form>
</main>
<script>
    (() => {
        const playerCountRow = document.getElementById('player-count-row');
        const modeInputs = document.querySelectorAll('input[name="mode"]');

        if (!playerCountRow || modeInputs.length === 0) {
            return;
        }

        const syncPlayerCountVisibility = () => {
            const selectedMode = [...modeInputs].find((input) => input.checked)?.value ?? 'players';
            playerCountRow.style.display = selectedMode === 'players' ? 'grid' : 'none';
        };

        for (const input of modeInputs) {
            input.addEventListener('change', syncPlayerCountVisibility);
        }

        syncPlayerCountVisibility();
    })();
</script>
</body>
</html>
