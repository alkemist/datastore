<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app'                      => [
        'path'       => 'app.js',
        'entrypoint' => true,
    ],
    'easyadmin'                => [
        'path'       => 'easyadmin.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus'       => [
        'version' => '3.2.2',
    ],
    'chart.js/auto'            => [
        'version' => '3.9.1',
    ],
    '@simplewebauthn/browser'  => [
        'version' => '7.4.0',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
];
