# Game Implementation Plan

This plan splits implementation into human-digestible chunks.

## Coding rules

- For anything game-logic related, rely on commands and the command bus. Docs for Tempest's command bus are here: `./vendor/tempest/framework/docs/2-features/10-command-bus.md`
- All Tempest docs can be found in `./vendor/tempest/framework/docs`
- Follow the same coding style as other parts already available in this app
- Only make changes in the `App\Game` directory

## Chunking rules

- Max 5-8 files changed per chunk.
- Max ~300-500 LOC net change per chunk.
- Each chunk must be runnable/testable on its own.
- Do not mix concerns in one chunk.
- End each chunk with a short "how to test" note.

## Chunk 1: Game domain skeleton

- Add `app/Game` core domain objects/entities:
  - `Game`
  - `Player`
  - `Station`
  - `Edge`
  - `ClaimRule`
- No DB and no HTTP endpoints yet.
- Add unit tests for claim/overclaim rules:
  - reclaim requires `+1`
  - `CAP = 5`

## Chunk 2: Map generation

- Add seeded graph generator.
- Add hub tagging (target ~5% stations).
- Add express edge generation.
- Add unit tests for map invariants:
  - connected graph
  - hub percentage bounds

## Chunk 3: Persistence layer

- Add migrations/tables for:
  - games
  - stations
  - edges
  - claims
  - events
- Add simple repositories for game state persistence.
- Add minimal integration test: create and load a game.

## Chunk 4: Move command + resolver

- Add `POST /games/{id}/commands/move`.
- Implement departure-time move legality checks.
- Implement deterministic conflict ordering.
- Add tests for tie/conflict behavior.

## Chunk 5: Challenge economy

- Add challenge spawn/completion logic.
- Enforce active challenge cap: `3 * player_count`.
- Implement reward range: `20-50` coins.
- Add regional spawn balancing.
- Add tests for cap and reward bounds.

## Chunk 6: Scoring + match end

- Implement timed control match end.
- Implement scoring:
  - station control score
  - flat `+N` hub bonus per controlled hub
- Add tests for winner/tie scenarios.

## Chunk 7: Read model endpoint

- Add `GET /games/{id}/state`.
- Optionally add timeline endpoint/read model if needed.
- Return UI-ready state payload.
- Add integration test from commands to projected state.

## Chunk 8: Minimal UI slice

- Render basic map.
- Add click/tap-to-move.
- Show coins, ETA, and score.
- Add mobile zoom/pan support.
- Keep visuals minimal; no polish-focused animation work yet.

## Progress tracking

- [x] Chunk 1: Game domain skeleton
- [x] Chunk 2: Map generation
- [x] Chunk 3: Persistence layer
- [x] Chunk 4: Move command + resolver
- [x] Chunk 5: Challenge economy
- [x] Chunk 6: Scoring + match end
- [x] Chunk 7: Read model endpoint
- [x] Chunk 8: Minimal UI slice
