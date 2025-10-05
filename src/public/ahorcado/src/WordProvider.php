<?php

namespace Ahorcado;

use RuntimeException;

class WordProvider
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function randomWord(): string
    {
        if (!is_file($this->filePath) || !is_readable($this->filePath)) {
            throw new RuntimeException('No se puede leer el fichero de palabras.');
        }

        $lines = file($this->filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new RuntimeException('Error leyendo el fichero de palabras.');
        }

        $words = [];
        foreach ($lines as $line) {
            $normalized = $this->normalizeWord($line);
            if ($normalized !== '') {
                $words[] = $normalized;
            }
        }

        if (empty($words)) {
            throw new RuntimeException('No hay palabras disponibles.');
        }

        return $words[array_rand($words)];
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
