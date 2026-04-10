# Rail Claim (working title)

A real-time browser strategy game inspired by *Jet Lag: The Game*, focused on route control, coin economy, and timing.

## Design goals

- Create tense, readable territorial competition on a train map.
- Keep turns asynchronous enough for web play, but fast enough to feel alive.
- Make comebacks possible (no hard lockouts).
- Keep MVP implementation realistic in server-rendered/stateless HTTP architecture.
- Make the game usable on mobile (zoomable/draggable map).

## Core fantasy

"I outmaneuvered you on the rail network, cut your route, and stole your key stations at the right moment."

## Core game loop (MVP)

1. Start at a spawn station with starting coins.
2. Move along connected rail stations.
3. Every station you pass through must be claimable (and claimed by you if owned by someone else or neutral).
4. Spend coins to claim/overclaim stations.
5. Complete challenges at active challenge stations to gain coins.
6. Use earned coins to keep expanding or retake strategic choke points.
7. At time limit, player with best score wins.

## Terminology

- Station: node in graph.
- Track: edge between stations.
- Claim value: the current highest deposited value on a station.
- Owner: player currently controlling station.
- Overclaim: taking a station by depositing more than current claim value.
- Travel action: move from one station to an adjacent station.

## Rules: travel

- You may only move to adjacent connected stations.
- No queued multi-hop routes in MVP.
- Travel time is edge-based and variable (supports distance illusion and express lines).
- Default travel time is 2 seconds per station edge unless overridden by map edge data.
- While traveling, player is in-transit and cannot issue another move action.
- Entering a station requires it to become yours at that moment:
  - If neutral: claim it by depositing coins.
  - If enemy-owned: overclaim it by depositing more than current value.
  - If already yours: no deposit needed.
- A move is only legal if the target station can be legally claimed on arrival.
- Competing moves toward the same station are resolved deterministically by server rules set at departure time, preventing ambiguous dual occupancy.
- Player position visibility is arrival-based in MVP (in-transit destination is hidden).

## Rules: claiming and overclaiming

For station `S`:

- `top_value(S)`: active defending threshold of current owner.
- Neutral stations require a minimum deposit of 1 coin.
- To take an owned station: attacker must set `new_value = top_value + 1` up to `top_value + CAP`.
- `CAP` defaults to 5 in MVP and should remain runtime-configurable for balancing.
- Owner cannot increase their own station while still owner (prevents infinite turtling).
- Reclaim always requires at least `+1` over current `top_value`.
- Passing through your own station has no maintenance cost in MVP.

### Example

- A owns with 5.
- B takes with 6.
- A retakes with 7 (or up to 11 when `CAP = 5`).

## Rules: coin economy

### Baseline

- Starting coins: 40 (MVP default).
- Long-term starting balance should be configurable by player count and map size.
- Primary income is challenge completion.
- A possible future secondary income system: freight carrying with slower travel tradeoff.

### Challenge system (MVP-simple)

- Some stations are marked as active challenges.
- If a player arrives there, challenge auto-completes instantly.
- Each challenge rewards a random 20-50 coins.
- Global active challenge cap: `max_active_challenges = 3 * player_count`.
- On completion, new challenges respawn with regional spawn balancing to avoid runaway leader snowballing.
- If multiple players race for the same challenge, deterministic travel resolution means only the resolved arrival owner completes it.

## Win condition (MVP)

- Timed control mode.
- Default match duration target: 20 minutes (configurable).

## Scoring model (MVP)

- End-of-match station control score.
- Bonus score for key hubs/interchanges using a flat `+N` per controlled hub.
- Completed challenges do not directly add score in MVP (they fuel economy only).

## Map design

### Option A: real-world rail map

Pros:

- Instantly recognizable and marketable.
- Geographic storytelling.

Cons:

- Data cleanup complexity.
- Legal/licensing considerations depending on data/source branding.

### Option B: procedural map (seeded graph)

Pros:

- Infinite replayability.
- Easier balancing with generated constraints.
- Deterministic seeds simplify debugging and competitive fairness.

Cons:

- Harder to make visually intuitive at first.

### Procedural generation constraints

- Graph is connected.
- Dead-end ratio is limited.
- Include medium-value choke points.
- Enforce spawn separation distance.
- Station count scales by player count.
- Include some express lines (lower travel time edges).
- Approximately 5% of stations should be designated as hubs/interchanges.

Example targets:

- 2 players: 30-45 stations.
- 3-4 players: 45-70 stations.

## Match flow

1. Lobby created (players join, settings chosen, seed locked).
2. Countdown starts.
3. Match runs in real-time clock.
4. Server resolves departures, arrivals, claims, challenge completions, and respawns deterministically.
5. At match end, freeze actions and render results timeline.

## Simultaneous action and conflict resolution

Needed for fairness in stateless HTTP model.

### Deterministic ordering rule

At same effective timestamp, resolve in this order:

1. Departures (validate move legality and reserve conflict ordering)
2. Arrivals
3. Claims/overclaims
4. Challenge completions
5. Challenge respawns
6. Score snapshot

Tie-break for identical timestamps on same station:

- Lowest server-generated monotonic event ID.

## Technical architecture (Tempest/PHP)

### Guiding principle

Use server-authoritative simulation with deterministic event processing; client is a view + command sender.

### Stateless HTTP-friendly approach

- Client sends commands: `move`, `ready`, etc.
- Server writes commands/events to DB with timestamps and deterministic ordering metadata.
- Read endpoints rebuild current game state by replaying events (or snapshot + tail events).
- Periodic snapshots reduce replay cost.

### Data model sketch

- `games` (settings, seed, status, clock)
- `players` (game_id, resources, spawn, status)
- `stations` (game_id, station_id, topology metadata, is_hub)
- `edges` (station_a, station_b, travel_time, is_express)
- `station_claims` (station_id, owner_id, top_value, updated_at)
- `challenges` (station_id, active, reward, spawned_at)
- `events` (type, game_id, player_id, payload_json, effective_at, order_key, created_at)
- `snapshots` (game_id, tick, state_blob)

### API sketch

- `POST /games` create lobby/match
- `POST /games/{id}/join`
- `POST /games/{id}/commands/move`
- `GET /games/{id}/state`
- `GET /games/{id}/timeline`

## Client/UI brainstorm

Visual direction: "tabletop transit war room"

- Stylized metro map with clear line colors and station nodes.
- Ownership rings around stations (player color + claim value label).
- Challenge markers distinct from ownership markers.
- Bottom action bar: coins, ETA, score.
- Right panel: event log ("B overclaimed Utrecht with 6").
- Mobile-ready interaction with pinch zoom and drag pan.

### Interaction principles

- One-click adjacent move.
- Hover/tap station to preview cost to take.
- Warn before moving into costly choke point.
- Mobile-first station hit areas.

## Balancing risks and mitigations

- Runaway leader from challenge luck.
- Mitigation: regional spawn balancing.

- Hard lock at expensive choke points.
- Mitigation: overclaim cap (`CAP`) and tune with playtests.

- Passive play / stalling.
- Mitigation: timed mode.

## MVP scope recommendation

Ship first playable with:

- 2 players.
- Seeded procedural map only.
- Timed control win condition.
- Auto-complete challenges.
- Single-hop movement commands.
- Variable edge travel times + express lines.
- Basic replay/event log.
- Mobile zoom/pan map support.

Defer:

- Real rail datasets.
- Custom challenge minigames.
- Fog of war.
- >2 players.
- Cosmetic polish-heavy animations.

## Open questions to resolve next

- Should freight/passive income be added before or after first playable tests?
- Should express lines be map-authored only, or also procedurally generated with constraints?

## First implementation milestone

"Playable vertical slice":

- Generate seeded map with variable edge travel times and hub tags.
- Join match with 2 players.
- Move, claim, overclaim with coin deductions.
- Resolve move conflicts deterministically (departure-first ordering).
- Spawn and complete simple challenges with regional balancing.
- End match at timer and compute winner from stations + hub bonuses.

If this slice is fun, expand content. If not, iterate economy and claim tuning before adding complexity.
