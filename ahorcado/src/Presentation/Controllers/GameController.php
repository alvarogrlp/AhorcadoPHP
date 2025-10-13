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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['letra'])) {
                $letter = (string) $_POST['letra'];
                $this->gameService->guess($letter);
            } elseif (isset($_POST['attempts_action'])) {
                $action = (string) $_POST['attempts_action'];
                $delta = $action === 'inc' ? 1 : ($action === 'dec' ? -1 : 0);
                if ($delta !== 0) {
                    $this->gameService->adjustAttempts($delta);
                } else {
                    $this->gameService->persist();
                }
            } else {
                $this->gameService->persist();
            }
        } else {
            $this->gameService->persist();
        }

        $game = $this->gameService->getCurrentGame();
        $renderer = new Renderer();

        $maskedWord = $game->maskedWord();
        $maskedWordDisplay = implode(' ', str_split($maskedWord));
        $attemptsLeft = $game->remainingAttempts();
        $maxAttempts = $game->maxAttempts();
        $usedLetters = $game->guesses();
        $hasStarted = $game->hasStarted();
        $canAdjustAttempts = $game->canAdjustAttempts();

        [$message, $bodyState] = $this->resolveGameMessage($game);
        $difficulty = $this->resolveDifficultyState($attemptsLeft, $maxAttempts, $game->status());
        $stateClass = $hasStarted ? 'game-active' : 'game-intro';
        $bodyClasses = trim(implode(' ', array_filter([$bodyState, $difficulty['class'], $stateClass])));

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

    /**
     * @return array{class: string, label: string, tone: string, progress: float}
     */
    private function resolveDifficultyState(int $attemptsLeft, int $maxAttempts, string $status): array
    {
        $maxAttempts = max(1, $maxAttempts);
        $attemptsLeft = max(0, $attemptsLeft);

        if ($status === Game::STATUS_WON) {
            return [
                'class' => 'difficulty-victory',
                'label' => 'Victoria asegurada',
                'tone' => 'success',
                'progress' => 1.0,
            ];
        }

        if ($status === Game::STATUS_LOST) {
            return [
                'class' => 'difficulty-failure',
                'label' => 'Juego finalizado',
                'tone' => 'danger',
                'progress' => 0.0,
            ];
        }

        $ratio = $attemptsLeft / $maxAttempts;
        if ($ratio >= 0.66) {
            return [
                'class' => 'difficulty-safe',
                'label' => 'Ritmo relajado',
                'tone' => 'safe',
                'progress' => $ratio,
            ];
        }

        if ($ratio >= 0.33) {
            return [
                'class' => 'difficulty-tense',
                'label' => 'Momento decisivo',
                'tone' => 'warn',
                'progress' => $ratio,
            ];
        }

        return [
            'class' => 'difficulty-critical',
            'label' => 'Situacion critica',
            'tone' => 'danger',
            'progress' => $ratio,
        ];
    }
}
