<?php
declare(strict_types=1);

namespace App\Infrastructure\Autoload;

final class Autoloader
{
    /** @var array<string, string> */
    private array $prefixes = [];

    public static function register(string $prefix = 'App\\', string $baseDir = __DIR__ . '/../..'): void
    {
        $loader = new self();
        $loader->addNamespace($prefix, $baseDir);
        spl_autoload_register([$loader, 'loadClass']);
    }

    public function addNamespace(string $prefix, string $baseDir): void
    {
        $normalizedPrefix = rtrim($prefix, '\\') . '\\';
        $normalizedBaseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $this->prefixes[$normalizedPrefix] = $normalizedBaseDir;
    }

    public function loadClass(string $class): void
    {
        foreach ($this->prefixes as $prefix => $baseDir) {
            $prefixLength = strlen($prefix);
            if (strncmp($prefix, $class, $prefixLength) !== 0) {
                continue;
            }

            $relativeClass = substr($class, $prefixLength);
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            if (is_file($file)) {
                require $file;
            }
        }
    }
}
