<?php
declare(strict_types=1);

namespace App\Domain\Repository;

interface WordRepositoryInterface
{
    public function randomWord(): string;
}
