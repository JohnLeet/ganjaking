<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit11caf98373cb7a73b68997657e2fb396
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Themesquad\\WC_Sales_Report_Email\\' => 33,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Themesquad\\WC_Sales_Report_Email\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit11caf98373cb7a73b68997657e2fb396::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit11caf98373cb7a73b68997657e2fb396::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit11caf98373cb7a73b68997657e2fb396::$classMap;

        }, null, ClassLoader::class);
    }
}
