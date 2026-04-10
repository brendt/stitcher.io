# Part 2: Map Rendering Overhaul

Before adding more gameplay features, map rendering needs to be deterministic, readable, and compatible with the game rules.

## Goals

- Render stations on a coordinate grid.
- Keep train lines mostly linear.
- Keep topology simple: two to three major lines with occasional intersections.
- Preserve deterministic generation from seed.
- Use 50 stations as baseline target for 2-player maps.

## Non-goals (for this part)

- Perfect metro-style beautification.
- Advanced pathfinding visualization.
- Animation polish.

## Core constraints

- Coordinates are integer grid positions.
- Default grid size: `100 x 100`.
- Station spacing should avoid overlap.
- Lines should feel like rail corridors (long stretches, few turns).
- Intersections should be rare and intentional.

## Map data contract

Generation should return a structure like:

- `stations[]`
- `id`
- `x`
- `y`
- `line_id`
- `is_hub`
- `connections[]`
- `from_station_id`
- `to_station_id`
- `travel_time_seconds`
- `is_express`

Notes:

- `line_id` is required for styling and debugability.
- Coordinates are authoritative for rendering (no fallback radial layout).
- Edges remain undirected in gameplay terms.

## Chunk 1: Generation (proposed + clarified)

### 1) Grid and start position

- Determine map size from player count:
- 2 players: `100 x 100` with target station count `50`
- 3-4 players: `120 x 120`
- Pick a start coordinate in top zone, not too close to edges:
- `x in [15..85]`, `y in [8..20]` for `100 x 100`

### 2) Main loop line

- Create one primary loop by directional segments:
- ~10 stations moving right
- ~10 down
- ~10 left
- ~10 up
- Per-step forward distance: random `1..5`.
- Per-step perpendicular offset: random `0..3`.

Clarification:

- Offsets should be biased to small values to preserve linear feel.
- Clamp resulting coordinates inside bounds.

### 3) Collision and spacing rules

- Minimum Manhattan distance between stations: `>= 2`.
- If new coordinate violates spacing:
- retry up to N times (`N=8`)
- otherwise shorten step and retry
- final fallback: skip station

### 4) Connectivity guarantee

- Every created station must connect to previous station in its line.
- Ensure at least one closed loop exists at end of generation.

### 5) Determinism

- All random choices must come from seeded RNG.
- Same seed + same player count => same map.

## Chunk 2: Frontend station rendering

- Render stations using generated `(x, y)` only.
- Convert grid coordinates to pixels with fixed scale and padding.
- Station visual states:
- neutral
- owned by player A/B/...
- hub/intersection
- current player position
- reachable next move
- blocked/unreachable

Clarification:

- Do not allow hidden auto-layout in this chunk.
- If coordinates are out-of-range, log and skip rendering that station.

## Chunk 3: Frontend connection rendering

- Render one segment for each connection.
- Connection style variants:
- normal
- express
- highlighted (reachable route preview)
- Optional: travel-time labels on edges.

Clarification:

- Connection draw order should be below stations.
- Deduplicate visually identical undirected edges.

## Chunk 4: Parallel lines and intersections

### Intersection rule

- Each station on a main line has low chance to branch (`4%` for now).
- Hard cap intersection count per map based on line count target.

### Branch direction

- Branch should initially move toward map center.
- Initial branch offset distance: `4..8`.

### Branch progression

- After branch starts, continue with same line step logic as main line.
- At each branch station, merge-back chance increases by distance from split:
- `merge_chance = min(60, 5 * stations_since_split)%`

### Branch limits

- Maximum active branches at once: `2`.
- Stop branch if out-of-bounds pressure or repeated collisions.

## Acceptance criteria

- Map renders with coordinate-based layout (no radial fallback).
- Topology usually contains 2-3 rail corridors.
- Lines are mostly linear with occasional intersections.
- No station overlaps at default zoom.
- Map is deterministic per seed.
- Express-edge generation is out of scope for this phase.

## Open decisions (explicit)

- Exact station count target for 3-4 player maps (proposal: `65`)?
- Should branch chance remain global (`4%`) or vary by region density?
