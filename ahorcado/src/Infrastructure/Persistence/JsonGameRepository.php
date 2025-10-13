<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Game;
use App\Domain\Repository\GameRepositoryInterface;
use RuntimeException;

final class JsonGameRepository implements GameRepositoryInterface
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function save(Game $game): void
    {
        $data = $this->readData();
        if (!isset($data['games']) || !is_array($data['games'])) {
            $data['games'] = [];
        }

        $payload = $game->toArray();
        $data['games'][$game->id()] = $payload;

        $this->writeData($data);
    }

    public function find(string $id): ?Game
    {
        $data = $this->readData();
        $games = $data['games'] ?? [];
        $stored = $games[$id] ?? null;

        if (!is_array($stored)) {
            return null;
        }

        $stored['id'] = $id;

        return Game::fromArray($stored);
    }

    /**
     * @return array<mixed>
     */
    private function readData(): array
    {
        if (!is_file($this->filePath)) {
            return ['games' => []];
        }

        $contents = file_get_contents($this->filePath);
        if ($contents === false) {
            throw new RuntimeException('No se puede leer el archivo de partidas.');
        }

        $decoded = json_decode($contents, true);
        if (!is_array($decoded)) {
            return ['games' => []];
        }

        return $decoded;
    }

    /**
     * @param array<mixed> $data
     */
    private function writeData(array $data): void
    {
        $directory = dirname($this->filePath);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('No se puede crear el directorio de almacenamiento.');
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('No se pudo serializar la partida.');
        }

        $result = file_put_contents($this->filePath, $json . PHP_EOL, LOCK_EX);
        if ($result === false) {
            throw new RuntimeException('No se pudo guardar la partida.');
        }
    }
}
