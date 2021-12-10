<?php

namespace danog\MadelineProto;

if (\defined('MADELINE_PHP')) {
    throw new \Exception('Please do not include madeline.php twice!');
}

if (!\defined('MADELINE_ALLOW_COMPOSER') && \class_exists(\Composer\Autoload\ClassLoader::class)) {
    throw new \Exception('Composer autoloader detected: madeline.php is incompatible with Composer, please require MadelineProto using composer.');
}

\define('MADELINE_PHP', __FILE__);

class Installer
{
    const RELEASE_TEMPLATE = 'https://phar.madelineproto.xyz/release%s?v=new';
    const PHAR_TEMPLATE = 'https://phar.madelineproto.xyz/madeline%s.phar?v=new';

    /**
     * Phar lock instance.
     *
     * @var resource|null
     */
    private static $lock = null;
    /**
     * Installer lock instance.
     *
     * @var resource|null
     */
    private $lockInstaller = null;
    /**
     * PHP version.
     *
     * @var string
     */
    private $version;
    /**
     * Constructor.
     */
    public function __construct()
    {
        if (\count(\debug_backtrace(0)) === 1) {
            if (isset($GLOBALS['argv']) && !empty($GLOBALS['argv'])) {
                $arguments = \array_slice($GLOBALS['argv'], 1);
            } elseif (isset($_GET['argv']) && !empty($_GET['argv'])) {
                $arguments = $_GET['argv'];
            } else {
                $arguments = [];
            }
            if (\count($arguments) >= 2) {
                \define(\MADELINE_WORKER_TYPE::class, \array_shift($arguments));
                \define(\MADELINE_WORKER_ARGS::class, $arguments);
            } else {
                die('MadelineProto loader: you must include this file in another PHP script, see https://docs.madelineproto.xyz for more info.'.PHP_EOL);
            }
        }
        if ((PHP_MAJOR_VERSION === 7 && PHP_MINOR_VERSION < 1) || PHP_MAJOR_VERSION < 7) {
            throw new \Exception('MadelineProto requires at least PHP 7.1 to run');
        }
        if (PHP_INT_SIZE < 8) {
            throw new \Exception('A 64-bit build of PHP is required to run MadelineProto, PHP 8.0+ recommended.');
        }
        $this->version = (string) \min(80, (int) (PHP_MAJOR_VERSION.PHP_MINOR_VERSION));
        \define('MADELINE_PHAR_GLOB', \getcwd().DIRECTORY_SEPARATOR."madeline-*-{$this->version}.phar");
        \define('MADELINE_RELEASE_URL', \sprintf(self::RELEASE_TEMPLATE, $this->version));
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->lockInstaller) {
            \flock($this->lockInstaller, LOCK_UN);
            \fclose($this->lockInstaller);
            $this->lockInstaller = null;
        }
    }

    /**
     * Extract composer package versions from phar.
     *
     * @param string|null $release
     * @return array<string, string>
     */
    private static function extractVersions($release)
    {
        $phar = "madeline-$release.phar";
        $packages = ['danog/madelineproto' => 'old'];
        if (!\file_exists("phar://$phar/vendor/composer/installed.json")) {
            return $packages;
        }
        $composer = \json_decode(\file_get_contents("phar://$phar/vendor/composer/installed.json"), true) ?: [];
        if (!isset($composer['packages'])) {
            return $packages;
        }
        foreach ($composer['packages'] as $dep) {
            $name = $dep['name'];
            if (\strpos($name, 'phabel/transpiler') === 0) {
                $name = \explode('/', $name, 3)[2];
            }
            $version = $dep['version_normalized'];
            if ($name === 'danog/madelineproto' && \substr($version, 0, 2) === '90') {
                $version = \substr($version, 2);
            }
            $packages[$name] = $version;
        }
        return $packages;
    }


    /**
     * Report installs to composer.
     *
     * @param string $local_release
     * @param string $remote_release
     *
     * @return void
     */
    private static function reportComposer($local_release, $remote_release)
    {
        $previous = self::extractVersions($local_release);
        $current = self::extractVersions($remote_release);
        $postData = ['downloads' => []];
        foreach ($current as $name => $version) {
            if (isset($previous[$name]) && $previous[$name] === $version) {
                continue;
            }
            $postData['downloads'][] = [
                'name' => $name,
                'version' => $version
            ];
        }

        if (\defined('HHVM_VERSION')) {
            $phpVersion = 'HHVM '.HHVM_VERSION;
        } else {
            $phpVersion = 'PHP '.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION;
        }
        $opts = ['http' =>
            [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    \sprintf(
                        'User-Agent: Composer/%s (%s; %s; %s; %s%s)',
                        'MProto v7',
                        \function_exists('php_uname') ? @\php_uname('s') : 'Unknown',
                        \function_exists('php_uname') ? @\php_uname('r') : 'Unknown',
                        $phpVersion,
                        'streams',
                        \getenv('CI') ? '; CI' : ''
                    )
                ],
                'content' => \json_encode($postData),
                'timeout' => 6,
            ],
        ];
        @\file_get_contents("https://packagist.org/downloads/", false, \stream_context_create($opts));
    }

    /**
     * Load phar file.
     *
     * @param string|null $release
     * @return mixed
     */
    private static function load($release)
    {
        if ($release === null) {
            throw new \Exception('Could not download MadelineProto, please check your internet connection and PHP configuration!');
        }
        $phar = "madeline-$release.phar";
        if (!self::$lock) {
            self::$lock = \fopen($phar, 'c');
        }
        \flock(self::$lock, LOCK_SH);
        $result = require_once $phar;
        if (\defined('MADELINE_WORKER_TYPE') && \constant('MADELINE_WORKER_TYPE') === 'madeline-ipc') {
            require_once "phar://$phar/vendor/danog/madelineproto/src/danog/MadelineProto/Ipc/Runner/entry.php";
        }
        return $result;
    }

    /**
     * Lock installer.
     *
     * @param string $version Version file to lock
     *
     * @return bool
     */
    private function lock($version)
    {
        if ($this->lockInstaller) {
            return true;
        }
        $this->lockInstaller = \fopen($version, 'c');
        return \flock($this->lockInstaller, LOCK_EX|LOCK_NB);
    }

    /**
     * Install MadelineProto.
     *
     * @return mixed
     */
    public function install()
    {
        $remote_release = \file_get_contents(MADELINE_RELEASE_URL) ?: null;

        $madeline_version = "madeline-{$this->version}.phar.version";
        if (\file_exists($madeline_version)) {
            $local_release = \file_get_contents($madeline_version) ?: null;
        } else {
            \touch($madeline_version);
            $local_release = null;
        }
        \define('HAD_MADELINE_PHAR', !!$local_release);

        if ($remote_release === $local_release || $remote_release === null) {
            return self::load($local_release);
        }

        if (!$this->lock($madeline_version)) {
            \flock($this->lockInstaller, LOCK_EX);
            return $this->install();
        }

        $madeline_phar = "madeline-$remote_release.phar";
        if (!\file_exists($madeline_phar)) {
            $phar = \file_get_contents(\sprintf(self::PHAR_TEMPLATE, $this->version));
            if (!$phar) {
                return self::load($local_release);
            }

            self::$lock = \fopen($madeline_phar, 'w');
            \flock(self::$lock, LOCK_EX);
            \fwrite(self::$lock, $phar);
            unset($phar);

            self::reportComposer($local_release, $remote_release);
        }
        \file_put_contents($madeline_version, $remote_release);
        return self::load($remote_release);
    }
}

return (new \danog\MadelineProto\Installer)->install();
