<?php

namespace MVC\Filesystem;

use MVC\Kernel;

/**
 * Used to find all classes in specific namespace
 * @author [Fabien Sa](https://stackoverflow.com/a/40229665)
 */
class ClassFinder
{
    public static function getClassesInNamespace($namespace): array
    {
        $files = scandir(self::getNamespaceDirectory($namespace));

        $classes = array_map(function ($file) use ($namespace) {
            return $namespace . '\\' . str_replace('.php', '', $file);
        }, $files);


        return array_filter($classes, function ($possibleClass) {
            return class_exists($possibleClass);
        });
    }

    private static function getNamespaceDirectory($namespace): false|string
    {
        $composerNamespaces = self::getDefinedNamespaces();

        $namespaceFragments = explode('\\', $namespace);
        $undefinedNamespaceFragments = [];

        while ($namespaceFragments) {
            $possibleNamespace = implode('\\', $namespaceFragments) . '\\';

            if (array_key_exists($possibleNamespace, $composerNamespaces)) {
                return realpath(
                    Kernel::projectDir() . '/' . $composerNamespaces[$possibleNamespace] . implode(
                        '/',
                        $undefinedNamespaceFragments
                    )
                );
            }

            array_unshift($undefinedNamespaceFragments, array_pop($namespaceFragments));
        }

        return false;
    }

    private static function getDefinedNamespaces(): array
    {
        $composerJsonPath = Kernel::projectDir() . '/composer.json';
        $composerConfig = json_decode(file_get_contents($composerJsonPath));

        return (array)$composerConfig->autoload->{'psr-4'};
    }
}