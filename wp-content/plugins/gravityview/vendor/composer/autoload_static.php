<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf3742a7b1f85311889dff226b15a354c
{
    public static $files = array (
        '5255c38a0faeba867671b61dfda6d864' => __DIR__ . '/..' . '/paragonie/random_compat/lib/random.php',
        '72579e7bd17821bb1321b87411366eae' => __DIR__ . '/..' . '/illuminate/support/helpers.php',
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        '3109cb1a231dcd04bee1f9f620d46975' => __DIR__ . '/..' . '/paragonie/sodium_compat/autoload.php',
    );

    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'TrustedLogin\\' => 13,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Component\\HttpFoundation\\' => 33,
            'Symfony\\Component\\Finder\\' => 25,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'K' => 
        array (
            'Katzgrau\\KLogger\\' => 17,
        ),
        'I' => 
        array (
            'Illuminate\\Validation\\' => 22,
            'Illuminate\\Translation\\' => 23,
            'Illuminate\\Support\\' => 19,
            'Illuminate\\Filesystem\\' => 22,
            'Illuminate\\Contracts\\' => 21,
            'Illuminate\\Container\\' => 21,
        ),
        'G' => 
        array (
            'GravityKit\\Foundation\\' => 22,
            'Gettext\\Languages\\' => 18,
            'Gettext\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'TrustedLogin\\' => 
        array (
            0 => __DIR__ . '/..' . '/trustedlogin/client/src',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Component\\HttpFoundation\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/http-foundation',
        ),
        'Symfony\\Component\\Finder\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/finder',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Katzgrau\\KLogger\\' => 
        array (
            0 => __DIR__ . '/..' . '/katzgrau/klogger/src',
        ),
        'Illuminate\\Validation\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/validation',
        ),
        'Illuminate\\Translation\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/translation',
        ),
        'Illuminate\\Support\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/support',
        ),
        'Illuminate\\Filesystem\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/filesystem',
        ),
        'Illuminate\\Contracts\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/contracts',
        ),
        'Illuminate\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/illuminate/container',
        ),
        'GravityKit\\Foundation\\' => 
        array (
            0 => __DIR__ . '/..' . '/gravitykit/foundation/src',
        ),
        'Gettext\\Languages\\' => 
        array (
            0 => __DIR__ . '/..' . '/gettext/languages/src',
        ),
        'Gettext\\' => 
        array (
            0 => __DIR__ . '/..' . '/gettext/gettext/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'D' => 
        array (
            'Doctrine\\Common\\Inflector\\' => 
            array (
                0 => __DIR__ . '/..' . '/doctrine/inflector/lib',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Katzgrau\\KLogger\\Logger' => __DIR__ . '/..' . '/katzgrau/klogger/src/Logger.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf3742a7b1f85311889dff226b15a354c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf3742a7b1f85311889dff226b15a354c::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitf3742a7b1f85311889dff226b15a354c::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitf3742a7b1f85311889dff226b15a354c::$classMap;

        }, null, ClassLoader::class);
    }
}
