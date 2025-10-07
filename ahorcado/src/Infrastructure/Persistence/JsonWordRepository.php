<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Repository\WordRepositoryInterface;
use RuntimeException;

final class JsonWordRepository implements WordRepositoryInterface
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function randomWord(): string
    {
        $words = $this->loadWords();
        if ($words === []) {
            throw new RuntimeException('No hay palabras disponibles.');
        }

        return $words[array_rand($words)];
    }

    /**
     * @return string[]
     */
    private function loadWords(): array
    {
        if (!is_file($this->filePath) || !is_readable($this->filePath)) {
            throw new RuntimeException('No se puede leer el archivo de palabras.');
        }

        $contents = file_get_contents($this->filePath);
        if ($contents === false) {
            throw new RuntimeException('Error leyendo el archivo de palabras.');
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded) || !isset($decoded['words']) || !is_array($decoded['words'])) {
            throw new RuntimeException('El formato del archivo de palabras es incorrecto.');
        }

        $words = [];
        foreach ($decoded['words'] as $word) {
            $normalized = $this->normalizeWord((string) $word);
            if ($normalized !== '') {
                $words[] = $normalized;
            }
        }

        return array_values(array_unique($words));
    }

    private function normalizeWord(string $word): string
    {
        $word = trim($word);
        if ($word === '') {
            return '';
        }

        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT', $word);
        if ($transliterated !== false) {
            $word = $transliterated;
        }

        $word = strtoupper($word);
        $word = preg_replace('/[^A-Z]/', '', $word);

        return $word ?? '';
    }
}
