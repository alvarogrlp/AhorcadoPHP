<?php
/** @var App\Domain\Entity\Game $game */
/** @var App\Presentation\Views\Renderer $renderer */
/** @var int $attemptsLeft */
/** @var string $maskedWordDisplay */
/** @var string[] $usedLetters */
/** @var string $message */
/** @var string $bodyState */
/** @var string $bodyClasses */
/** @var int $maxAttempts */
/** @var array{class: string, label: string, tone: string, progress: float} $difficulty */
/** @var bool $hasStarted */
/** @var bool $canAdjustAttempts */

$bodyClassName = trim('app-shell ' . ($bodyClasses ?? ''));
$difficultyTone = 'chip--' . $difficulty['tone'];
$difficultyProgress = max(0.0, min(1.0, $difficulty['progress']));
$progressFormatted = number_format($difficultyProgress, 3, '.', '');
$difficultyDescription = sprintf(
    'Quedan %d de %d intentos',
    (int) $attemptsLeft,
    (int) $maxAttempts
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ahorcado en PHP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/base.css">
    <link rel="stylesheet" href="styles/layout.css">
    <link rel="stylesheet" href="styles/components.css">
    <link rel="stylesheet" href="styles/animations.css">
</head>
<body class="<?php echo htmlspecialchars($bodyClassName, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="background background--layer"></div>
    <div class="background background--flare"></div>
    <main class="layout">
        <header class="hero">
            <div class="hero__headline">
                <h1 class="hero__title">Juego del Ahorcado</h1>
                <p class="hero__subtitle">
                    Adivina la palabra antes de que el tiempo visual llegue a su fin. Cada intento cuenta.
                </p>
            </div>
            <aside class="hero__status" aria-live="polite">
                <span class="chip chip--difficulty <?php echo htmlspecialchars($difficultyTone, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($difficulty['label'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <div class="progress-card" role="group" aria-label="Indicador de intentos restantes">
                    <div class="progress-card__header">
                        <span class="progress-card__label">Intentos restantes</span>
                        <span class="progress-card__value">
                            <?php echo (int) $attemptsLeft; ?>
                            <small>/<?php echo (int) $maxAttempts; ?></small>
                        </span>
                    </div>
                    <div class="meter" role="progressbar"
                         aria-valuemin="0"
                         aria-valuemax="<?php echo (int) $maxAttempts; ?>"
                         aria-valuenow="<?php echo (int) $attemptsLeft; ?>"
                         aria-label="<?php echo htmlspecialchars($difficultyDescription, ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="meter__fill" style="--progress: <?php echo htmlspecialchars($progressFormatted, ENT_QUOTES, 'UTF-8'); ?>;"></span>
                    </div>
                </div>
            </aside>
        </header>

        <section class="content">
            <article class="panel panel--visual">
                <div class="panel__card">
                    <div class="panel__glow"></div>
                    <?php echo $renderer->ascii($attemptsLeft, $maxAttempts); ?>
                    <div class="attempts">
                        <span class="attempts__badge">
                            <strong><?php echo (int) $attemptsLeft; ?></strong>
                            intentos
                        </span>
                        <?php if ($canAdjustAttempts): ?>
                            <div class="attempts__hint">Ajusta los intentos antes de empezar.</div>
                            <form class="attempts__controls" method="post">
                                <button class="btn-icon" type="submit" name="attempts_action" value="dec"
                                        aria-label="Reducir intentos" title="Reducir intentos"<?php echo $maxAttempts <= 1 ? ' disabled' : ''; ?>>
                                    <span aria-hidden="true">&minus;</span>
                                </button>
                                <button class="btn-icon" type="submit" name="attempts_action" value="inc"
                                        aria-label="Aumentar intentos" title="Aumentar intentos">
                                    <span aria-hidden="true">+</span>
                                </button>
                            </form>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>

            <article class="panel panel--interaction">
                <div class="word-board" aria-live="polite">
                    <span class="word-board__label">Palabra secreta</span>
                    <span class="word-board__value"><?php echo htmlspecialchars($maskedWordDisplay, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <div class="letters-cloud" aria-live="polite">
                    <div class="letters-cloud__header">
                        <span class="letters-cloud__label">Letras usadas</span>
                        <span class="letters-cloud__count">
                            <?php echo count($usedLetters); ?>
                        </span>
                    </div>
                    <div class="letters-cloud__wrap">
                        <?php if (empty($usedLetters)): ?>
                            <span class="letters-cloud__placeholder">Aun no has probado ninguna letra.</span>
                        <?php else: ?>
                            <?php foreach ($usedLetters as $letter): ?>
                                <span class="chip chip--letter"><?php echo htmlspecialchars($letter, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($message === ''): ?>
                    <form class="action-card" method="post">
                        <label for="letra" class="action-card__label">Introduce una letra</label>
                        <div class="action-card__controls">
                            <input class="action-card__input" type="text" id="letra" name="letra" maxlength="1" autocomplete="off" required>
                            <button class="action-card__button" type="submit">Adivinar</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="result-banner" role="status">
                        <strong><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                    <a class="reset-button" href="?reset=1">Jugar de nuevo</a>
                <?php endif; ?>
            </article>
        </section>
    </main>
</body>
</html>
