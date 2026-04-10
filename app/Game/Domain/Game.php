<?php

declare(strict_types=1);

namespace App\Game\Domain;

use InvalidArgumentException;

final class Game
{
    /**
     * @param array<string, Player> $players
     * @param array<string, Station> $stations
     * @param list<Edge> $edges
     */
    public function __construct(
        public readonly string $id,
        public readonly array $players,
        public readonly array $stations,
        public readonly array $edges,
    ) {}

    public function player(string $playerId): Player
    {
        return $this->players[$playerId]
            ?? throw new InvalidArgumentException(sprintf('Unknown player: %s', $playerId));
    }

    public function station(string $stationId): Station
    {
        return $this->stations[$stationId]
            ?? throw new InvalidArgumentException(sprintf('Unknown station: %s', $stationId));
    }

    public function withPlayer(Player $player): self
    {
        $players = $this->players;
        $players[$player->id] = $player;

        return new self(
            id: $this->id,
            players: $players,
            stations: $this->stations,
            edges: $this->edges,
        );
    }

    public function withStation(Station $station): self
    {
        $stations = $this->stations;
        $stations[$station->id] = $station;

        return new self(
            id: $this->id,
            players: $this->players,
            stations: $stations,
            edges: $this->edges,
        );
    }
}
