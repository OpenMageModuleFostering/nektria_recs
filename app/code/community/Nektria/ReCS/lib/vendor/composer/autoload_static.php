<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit16937a77cbefa6ddc05910943311ce45
{
    public static $files = array (
        'ad155f8f1cf0d418fe49e248db8c661b' => __DIR__ . '/..' . '/react/promise/src/functions_include.php',
    );

    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'React\\Promise\\' => 14,
        ),
        'N' => 
        array (
            'Nektria\\Recs\\MerchantApi\\' => 25,
        ),
        'G' => 
        array (
            'GuzzleHttp\\Subscriber\\Log\\' => 26,
            'GuzzleHttp\\Stream\\' => 18,
            'GuzzleHttp\\Ring\\' => 16,
            'GuzzleHttp\\Command\\Guzzle\\' => 26,
            'GuzzleHttp\\Command\\' => 19,
            'GuzzleHttp\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'React\\Promise\\' => 
        array (
            0 => __DIR__ . '/..' . '/react/promise/src',
        ),
        'Nektria\\Recs\\MerchantApi\\' => 
        array (
            0 => __DIR__ . '/..' . '/nektria/recs-sdk-php/src',
        ),
        'GuzzleHttp\\Subscriber\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/log-subscriber/src',
        ),
        'GuzzleHttp\\Stream\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/streams/src',
        ),
        'GuzzleHttp\\Ring\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/ringphp/src',
        ),
        'GuzzleHttp\\Command\\Guzzle\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/guzzle-services/src',
        ),
        'GuzzleHttp\\Command\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/command/src',
        ),
        'GuzzleHttp\\' => 
        array (
            0 => __DIR__ . '/..' . '/guzzlehttp/guzzle/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 
            array (
                0 => __DIR__ . '/..' . '/psr/log',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit16937a77cbefa6ddc05910943311ce45::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit16937a77cbefa6ddc05910943311ce45::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit16937a77cbefa6ddc05910943311ce45::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}