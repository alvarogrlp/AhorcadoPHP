<?php

namespace src;

class Renderer
{
    public function ascii(int $attemptsLeft): string
    {
        $attemptsLeft = max(0, min(6, $attemptsLeft));

        $partsVisibility = [
            'head' => $attemptsLeft <= 5,
            'body' => $attemptsLeft <= 4,
            'arm-left' => $attemptsLeft <= 3,
            'arm-right' => $attemptsLeft <= 2,
            'leg-left' => $attemptsLeft <= 1,
            'leg-right' => $attemptsLeft <= 0,
        ];

        $partsMarkup = '';
        foreach ($partsVisibility as $part => $visible) {
            $partsMarkup .= '<span class="hangman__part hangman__part--' . $part;
            if ($visible) {
                $partsMarkup .= ' is-visible';
            }
            $partsMarkup .= '"></span>';
        }

        return
            '<div class="hangman">' .
                '<div class="hangman__structure" aria-hidden="true">' .
                    '<span class="structure structure--base"></span>' .
                    '<span class="structure structure--pole"></span>' .
                    '<span class="structure structure--beam"></span>' .
                    '<span class="structure structure--brace"></span>' .
                    '<span class="structure structure--rope"></span>' .
                '</div>' .
                '<div class="hangman__figure" role="img" aria-label="Progreso del ahorcado">' .
                    '<div class="hangman__person">' .
                        $partsMarkup .
                    '</div>' .
                '</div>' .
            '</div>';
    }
}
