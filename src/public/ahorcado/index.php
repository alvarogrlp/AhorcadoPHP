<?php

require_once __DIR__ . '/src/Storage.php';
require_once __DIR__ . '/src/WordProvider.php';
require_once __DIR__ . '/src/Game.php';
require_once __DIR__ . '/src/Renderer.php';

use Ahorcado\Game;
use Ahorcado\Renderer;
use Ahorcado\Storage;
use Ahorcado\WordProvider;

$storage = new Storage();
$provider = new WordProvider(__DIR__ . '/words.txt');
$state = $storage->get('game');

if (is_array($state)) {
    $word = isset($state['word']) ? (string) $state['word'] : $provider->randomWord();
    $maxAttempts = isset($state['maxAttempts']) ? (int) $state['maxAttempts'] : 6;
    $game = new Game($word, $maxAttempts, $state);
} else {
    $game = new Game($provider->randomWord());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['letra'])) {
    $game->guessLetter((string) $_POST['letra']);
}

$storage->set('game', $game->toState());

$renderer = new Renderer();

$maskedWord = $game->getMaskedWord();
$attemptsLeft = $game->getAttemptsLeft();
$usedLetters = $game->getUsedLetters();
$maskedWordDisplay = implode(str_split($maskedWord));

$message = '';
$bodyState = '';
if ($game->isWon()) {
    $message = 'Felicidades! Ganaste. La palabra era: ' . $game->getWord();
    $bodyState = 'state-won';
} elseif ($game->isLost()) {
    $message = 'Lo siento! Perdiste. La palabra era: ' . $game->getWord();
    $bodyState = 'state-lost';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ahorcado en PHP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo htmlspecialchars($bodyState, ENT_QUOTES, 'UTF-8'); ?>">
<div class="background"></div>
<main class="app">
    <header class="app__header">
        <h1>Juego del Ahorcado</h1>
        <p class="app__subtitle">Adivina la palabra antes de que se complete la figura.</p>
    </header>

    <section class="game">
        <div class="game__visual">
            <div class="hangman-card">
                <?php echo $renderer->ascii($attemptsLeft); ?>
                <span class="attempts-badge">Intentos restantes: <strong><?php echo $attemptsLeft; ?></strong></span>
            </div>
        </div>
        <div class="game__panel">
            <div class="word-display" aria-live="polite">
                <span class="label">Palabra</span>
                <span class="value"><?php echo htmlspecialchars($maskedWordDisplay, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="used-letters" aria-live="polite">
                <span class="label">Letras usadas</span>
                <div class="letters">
                    <?php if (empty($usedLetters)): ?>
                        <span class="letters__placeholder">Aun no has probado ninguna letra.</span>
                    <?php else: ?>
                        <?php foreach ($usedLetters as $letter): ?>
                            <span class="chip"><?php echo htmlspecialchars($letter, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($message === ''): ?>
                <form class="guess-form" method="post">
                    <label for="letra" class="label">Introduce una letra</label>
                    <div class="guess-form__controls">
                        <input type="text" id="letra" name="letra" maxlength="1" autocomplete="off" required>
                        <button type="submit">Adivinar</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="result-banner" role="status">
                    <strong><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <a class="reset-button" href="reset.php">Jugar de nuevo</a>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
