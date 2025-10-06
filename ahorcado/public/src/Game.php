<?php

namespace src;

class Game
{
    private string $word;
    private int $maxAttempts;
    private int $attemptsLeft;
    /** @var string[] */
    private array $usedLetters = [];

    public function __construct(string $word, int $maxAttempts = 6, ?array $state = null)
    {
        $this->word = strtoupper($word);
        $this->maxAttempts = $maxAttempts;

        if ($state !== null) {
            $this->restoreState($state);
            return;
        }

        $this->attemptsLeft = $maxAttempts;
        $this->usedLetters = [];
    }

    public function guessLetter(string $letter): void
    {
        if ($this->isWon() || $this->isLost()) {
            return;
        }

        $letter = $this->sanitizeLetter($letter);
        if ($letter === '') {
            return;
        }

        if (in_array($letter, $this->usedLetters, true)) {
            return;
        }

        $this->usedLetters[] = $letter;

        if (strpos($this->word, $letter) === false) {
            $this->attemptsLeft = max(0, $this->attemptsLeft - 1);
        }
    }

    public function getMaskedWord(): string
    {
        $masked = '';
        foreach (str_split($this->word) as $char) {
            $masked .= in_array($char, $this->usedLetters, true) ? $char : '_';
        }

        return $masked;
    }

    public function getAttemptsLeft(): int
    {
        return $this->attemptsLeft;
    }

    /**
     * @return string[]
     */
    public function getUsedLetters(): array
    {
        return $this->usedLetters;
    }

    public function isWon(): bool
    {
        return $this->getMaskedWord() === $this->word;
    }

    public function isLost(): bool
    {
        return $this->attemptsLeft <= 0 && !$this->isWon();
    }

    public function getWord(): string
    {
        return $this->word;
    }

    public function toState(): array
    {
        return [
            'word' => $this->word,
            'maxAttempts' => $this->maxAttempts,
            'attemptsLeft' => $this->attemptsLeft,
            'usedLetters' => $this->usedLetters,
        ];
    }

    private function restoreState(array $state): void
    {
        $this->word = isset($state['word']) ? strtoupper((string) $state['word']) : $this->word;
        $this->maxAttempts = isset($state['maxAttempts']) ? (int) $state['maxAttempts'] : $this->maxAttempts;
        $this->attemptsLeft = isset($state['attemptsLeft']) ? max(0, (int) $state['attemptsLeft']) : $this->maxAttempts;

        $letters = [];
        if (isset($state['usedLetters']) && is_array($state['usedLetters'])) {
            foreach ($state['usedLetters'] as $letter) {
                $clean = $this->sanitizeLetter((string) $letter);
                if ($clean !== '' && !in_array($clean, $letters, true)) {
                    $letters[] = $clean;
                }
            }
        }

        $this->usedLetters = $letters;
    }

    private function sanitizeLetter(string $letter): string
    {
        $letter = strtoupper($letter);
        $letter = preg_replace('/[^A-Z]/', '', $letter);

        return $letter !== null ? substr($letter, 0, 1) : '';
    }
}
