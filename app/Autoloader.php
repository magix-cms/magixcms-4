<?php

declare(strict_types=1);

namespace Magix;

// Vérification de version optimisée (80200 = PHP 8.2.0)
if (\PHP_VERSION_ID < 80200) {
    echo 'Your PHP version is not compatible with this version of Magix CMS. PHP 8.2.0 or higher is required.';
    exit(1);
}

// Utilisation de defined() pour éviter les erreurs si les constantes existent déjà
defined('DS') || define('DS', DIRECTORY_SEPARATOR);
// Utilisation moderne de __DIR__
defined('ROOT') || define('ROOT', dirname(__DIR__) . DS);
defined('CONFIG_PATH') || define('CONFIG_PATH', ROOT . 'app' . DS . 'init' . DS . 'common.inc.php');
defined('LIB') || define('LIB', ROOT . 'lib' . DS);

class Autoloader
{
    /**
     * @var array<string, string>
     */
    protected array $libraries = [
        'magepattern'       => LIB . 'magepattern/Bootstrap.php',
        'interventionimage' => LIB . 'interventionimage/autoload.php'
    ];

    /**
     * @var array<string, array<string>>
     */
    protected array $prefixes = [];

    /**
     * @var array<int, string>
     */
    protected array $registered = [];

    public function __construct()
    {
        if (file_exists(CONFIG_PATH)) {
            require_once CONFIG_PATH;
        } else {
            throw new \RuntimeException('Error: Missing Common Init File at ' . CONFIG_PATH);
        }

        foreach ($this->libraries as $name => $path) {
            if (file_exists($path)) {
                require_once $path;
            } else {
                throw new \RuntimeException(sprintf('Failed to load "%s" library at path: %s', $name, $path));
            }
        }
    }

    /**
     * Register loader with SPL autoloader stack.
     */
    public function register(bool $prepend = false): void
    {
        if (!spl_autoload_register([$this, 'loadClass'], true, $prepend)) {
            throw new \RuntimeException('Could not register the Magix autoloader.');
        }
    }

    /**
     * Adds a base directory for a namespace prefix.
     */
    public function addNamespace(string $prefix, string|array $base_dir, bool $prepend = false): void
    {
        // 1. Normalisation propre du préfixe (ex: App\Backend\)
        $prefix = trim($prefix, '\\') . '\\';

        if (is_string($base_dir)) {
            $base_dir = [$base_dir];
        }

        foreach ($base_dir as $dir) { // On simplifie le foreach ici
            // 2. Normalisation du dossier
            $path = rtrim($dir, DS) . DS;

            // 3. LA CORRECTION : Utilise directement le préfixe comme clé
            if (!isset($this->prefixes[$prefix])) {
                $this->prefixes[$prefix] = [];
            }

            if ($prepend) {
                array_unshift($this->prefixes[$prefix], $path);
            } else {
                $this->prefixes[$prefix][] = $path;
            }
        }
    }

    /**
     * Loads the class file for a given class name.
     */
    // Dans votre fichier Autoloader.php (exemple de logique attendue)
    public function loadClass(string $class): void
    {
        foreach ($this->prefixes as $prefix => $baseDirs) {
            // Est-ce que la classe commence par le namespace enregistré ?
            if (strpos($class, $prefix) === 0) {

                // On récupère la partie après le préfixe (ex: Controller\PageController)
                $relativeClass = substr($class, strlen($prefix));

                foreach ($baseDirs as $baseDir) {
                    // On remplace les \ par des / et on assemble le chemin
                    $file = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR .
                        str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

                    if (file_exists($file)) {
                        require_once $file;
                        return;
                    }
                }
            }
        }
    }

    /**
     * Load the mapped file for a namespace prefix and relative class.
     */
    protected function loadMappedFile(string $prefix, string $relative_class): string|false
    {
        if (!isset($this->prefixes[$prefix])) {
            return false;
        }

        foreach ($this->prefixes[$prefix] as $base_dir) {
            // Remplacement des séparateurs pour correspondre au système de fichiers
            $file = $base_dir . str_replace('\\', DS, $relative_class) . '.php';

            if ($this->requireFile($file)) {
                return $file;
            }
        }

        return false;
    }

    /**
     * If a file exists, require it from the file system.
     */
    protected function requireFile(string $file): bool
    {
        if (file_exists($file) && !in_array($file, $this->registered, true)) {
            require $file; // On conserve require ici pour des raisons de performance dans l'autoloading dynamique
            return true;
        }
        return false;
    }
}