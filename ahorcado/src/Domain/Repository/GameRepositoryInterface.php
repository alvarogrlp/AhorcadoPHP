<?php
declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Game;

interface GameRepositoryInterface
{
    public function save(Game $game): void;

    public function find(string $id): ?Game;
}
