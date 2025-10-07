<?php
declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entity\Game;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\Repository\WordRepositoryInterface;

final class GameService
{
    private WordRepositoryInterface $wordRepository;
    private GameRepositoryInterface $gameRepository;
    private int $maxAttempts;
    private string $gameId;
    private ?Game $cachedGame = null;

    public function __construct(
        WordRepositoryInterface $wordRepository,
        GameRepositoryInterface $gameRepository,
        int $maxAttempts,
        string $gameId
    ) {
        $this->wordRepository = $wordRepository;
        $this->gameRepository = $gameRepository;
        $this->maxAttempts = max(1, $maxAttempts);
        $this->gameId = $gameId !== '' ? $gameId : 'current';
    }

    public function getCurrentGame(): Game
    {
        if ($this->cachedGame instanceof Game) {
            return $this->cachedGame;
        }

        $game = $this->gameRepository->find($this->gameId);
        if (!$game instanceof Game) {
            $game = $this->createNewGame();
            $this->gameRepository->save($game);
        }

        $this->cachedGame = $game;

        return $game;
    }

    public function guess(string $letter): Game
    {
        $game = $this->getCurrentGame();
        $game->guess($letter);
        $this->gameRepository->save($game);

        return $game;
    }

    public function persist(): void
    {
        $game = $this->getCurrentGame();
        $this->gameRepository->save($game);
    }

    public function reset(): void
    {
        $game = $this->createNewGame();
        $this->gameRepository->save($game);
        $this->cachedGame = $game;
    }

    private function createNewGame(): Game
    {
        $word = $this->wordRepository->randomWord();

        return new Game($this->gameId, $word, $this->maxAttempts);
    }
}
