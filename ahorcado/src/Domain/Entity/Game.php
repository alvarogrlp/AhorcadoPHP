<?php
declare(strict_types=1);

namespace App\Domain\Entity;

final class Game
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_WON = 'won';
    public const STATUS_LOST = 'lost';

    private string $id;
    private string $word;
    private int $maxAttempts;
    /** @var string[] */
    private array $guesses = [];
    private string $status = self::STATUS_IN_PROGRESS;

    /**
     * @param string[] $guesses
     */
    public function __construct(
        string $id,
        string $word,
        int $maxAttempts = 7,
        array $guesses = [],
        string $status = self::STATUS_IN_PROGRESS
    ) {
        $this->id = $id;
        $this->word = $this->sanitizeWord($word);
        $this->maxAttempts = max(1, $maxAttempts);
        $this->status = $this->normalizeStatus($status);

        $this->guesses = [];
        foreach ($guesses as $guess) {
            $this->addGuess($guess);
        }

        $this->refreshStatus();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function word(): string
    {
        return $this->word;
    }

    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    /**
     * @return string[]
     */
    public function guesses(): array
    {
        return $this->guesses;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function isWon(): bool
    {
        return $this->status === self::STATUS_WON;
    }

    public function isLost(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    public function maskedWord(): string
    {
        $masked = '';
        foreach (str_split($this->word) as $letter) {
            $masked .= in_array($letter, $this->guesses, true) ? $letter : '_';
        }

        return $masked;
    }

    public function remainingAttempts(): int
    {
        $wrongGuesses = 0;
        foreach ($this->guesses as $guess) {
            if (strpos($this->word, $guess) === false) {
                $wrongGuesses++;
            }
        }

        return max(0, $this->maxAttempts - $wrongGuesses);
    }

    public function guess(string $letter): void
    {
        if ($this->status !== self::STATUS_IN_PROGRESS) {
            return;
        }

        $sanitized = $this->sanitizeLetter($letter);
        if ($sanitized === '' || in_array($sanitized, $this->guesses, true)) {
            return;
        }

        $this->guesses[] = $sanitized;
        $this->refreshStatus();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'word' => $this->word,
            'maxAttempts' => $this->maxAttempts,
            'guesses' => $this->guesses,
            'status' => $this->status,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $id = (string) ($data['id'] ?? '');
        $word = (string) ($data['word'] ?? '');
        $maxAttempts = isset($data['maxAttempts']) ? (int) $data['maxAttempts'] : 7;
        $guesses = isset($data['guesses']) && is_array($data['guesses']) ? $data['guesses'] : [];
        $status = (string) ($data['status'] ?? self::STATUS_IN_PROGRESS);

        return new self($id, $word, $maxAttempts, $guesses, $status);
    }

    private function addGuess(string $letter): void
    {
        $sanitized = $this->sanitizeLetter($letter);
        if ($sanitized === '' || in_array($sanitized, $this->guesses, true)) {
            return;
        }

        $this->guesses[] = $sanitized;
    }

    private function refreshStatus(): void
    {
        if ($this->isWordGuessed()) {
            $this->status = self::STATUS_WON;
            return;
        }

        if ($this->remainingAttempts() <= 0) {
            $this->status = self::STATUS_LOST;
            return;
        }

        $this->status = self::STATUS_IN_PROGRESS;
    }

    private function isWordGuessed(): bool
    {
        foreach (str_split($this->word) as $letter) {
            if (!in_array($letter, $this->guesses, true)) {
                return false;
            }
        }

        return true;
    }

    private function sanitizeLetter(string $letter): string
    {
        $letter = strtoupper($letter);
        $letter = preg_replace('/[^A-Z]/', '', $letter);

        return $letter !== null ? substr($letter, 0, 1) : '';
    }

    private function sanitizeWord(string $word): string
    {
        $word = strtoupper($word);
        $word = preg_replace('/[^A-Z]/', '', $word);

        return $word ?? '';
    }

    private function normalizeStatus(string $status): string
    {
        $allowed = [
            self::STATUS_IN_PROGRESS,
            self::STATUS_WON,
            self::STATUS_LOST,
        ];

        return in_array($status, $allowed, true) ? $status : self::STATUS_IN_PROGRESS;
    }
}
