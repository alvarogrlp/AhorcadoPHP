<?php
declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\GameService;
use App\Domain\Entity\Game;
use App\Infrastructure\Persistence\JsonGameRepository;
use App\Infrastructure\Persistence\JsonWordRepository;
use App\Presentation\Views\Renderer;

final class GameController
{
    /** @var array<string, mixed> */
    private array $config;
    private GameService $gameService;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $storageConfig = $config['storage'] ?? [];

        $wordsFile = (string) ($storageConfig['words_file'] ?? '');
        $gamesFile = (string) ($storageConfig['games_file'] ?? '');
        $currentId = (string) ($storageConfig['current_game_id'] ?? 'current');
        $maxAttempts = (int) ($config['game']['max_attempts'] ?? 6);

        $wordRepository = new JsonWordRepository($wordsFile);
        $gameRepository = new JsonGameRepository($gamesFile);

        $this->gameService = new GameService($wordRepository, $gameRepository, $maxAttempts, $currentId);
    }

    public function handle(): void
    {
        if (isset($_GET['reset'])) {
            $this->gameService->reset();
            header('Location: index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['letra'])) {
            $letter = (string) $_POST['letra'];
            $this->gameService->guess($letter);
        } else {
            $this->gameService->persist();
        }

        $game = $this->gameService->getCurrentGame();
        $renderer = new Renderer();

        $maskedWord = $game->maskedWord();
        $maskedWordDisplay = implode(' ', str_split($maskedWord));
        $attemptsLeft = $game->remainingAttempts();
        $usedLetters = $game->guesses();

        [$message, $bodyState] = $this->resolveGameMessage($game);

        /** @var Game $game */
        require __DIR__ . '/../Views/game.php';
    }

    /**
     * @return array{string, string}
     */
    private function resolveGameMessage(Game $game): array
    {
        if ($game->isWon()) {
            return [
                'Felicidades! Ganaste. La palabra era: ' . $game->word(),
                'state-won',
            ];
        }

        if ($game->isLost()) {
            return [
                'Lo siento! Perdiste. La palabra era: ' . $game->word(),
                'state-lost',
            ];
        }

        return ['', ''];
    }
}
