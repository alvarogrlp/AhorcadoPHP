<?php
declare(strict_types=1);

return [
    'storage' => [
        'words_file' => __DIR__ . '/../storage/words.json',
        'games_file' => __DIR__ . '/../storage/games.json',
        'current_game_id' => 'current',
    ],
    'game' => [
        'max_attempts' => 7,
    ],
];
