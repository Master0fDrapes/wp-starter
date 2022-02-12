<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb538a095cd9528de6716b8e6f1916ce4
{
    public static $files = array (
        '6632f90381dd49c5fe745d09406b9abb' => __DIR__ . '/..' . '/htmlburger/carbon-field-number/field.php',
        '5f41e59e00dd36ab5d9cd1567d7710fd' => __DIR__ . '/..' . '/ynacorp/carbon-field-uniqid/field.php',
    );

    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Carbon_Fields\\' => 14,
            'Carbon_Field_UniqID\\' => 20,
            'Carbon_Field_Number\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Carbon_Fields\\' => 
        array (
            0 => __DIR__ . '/..' . '/htmlburger/carbon-fields/core',
        ),
        'Carbon_Field_UniqID\\' => 
        array (
            0 => __DIR__ . '/..' . '/ynacorp/carbon-field-uniqid/core',
        ),
        'Carbon_Field_Number\\' => 
        array (
            0 => __DIR__ . '/..' . '/htmlburger/carbon-field-number/core',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb538a095cd9528de6716b8e6f1916ce4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb538a095cd9528de6716b8e6f1916ce4::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb538a095cd9528de6716b8e6f1916ce4::$classMap;

        }, null, ClassLoader::class);
    }
}
