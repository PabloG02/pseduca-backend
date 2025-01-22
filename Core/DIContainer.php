<?php

namespace Core;

use PDO;
use ReflectionClass;
use ReflectionObject;

/**
 * Dependency Injection Container for managing service instantiation and injection.
 *
 * Provides a lightweight, native PHP dependency injection mechanism
 * using PHP attributes and reflection.
 */
class DIContainer {
    /** Storage for resolved service instances. */
    private static array $services = [];

    /**
     * Manually register a service instance in the container.
     *
     * @param class-string $class Fully qualified class name
     * @param object $instance Instance of the service to register
     * @return void
     */
    public static function register(string $class, object $instance): void
    {
        self::$services[$class] = $instance;
    }

    /**
     * Resolve a class and its dependencies.
     *
     * Recursively creates an instance of the given class,
     * resolving and injecting all constructor dependencies.
     *
     * @template T of object
     * @param class-string<T> $className Fully qualified class name to resolve
     * @return T Instantiated class with resolved dependencies
     * @throws ReflectionException If class cannot be reflected
     */
    public static function resolve(string $className)
    {
        // Check if service is already instantiated
        if (isset(self::$services[$className])) {
            return self::$services[$className];
        }

        if ($className === PDO::class) {
            return Database::getConnection();
        }

        // Use Reflection to inspect the class
        $reflectionClass = new ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();

        // If no constructor, create a simple instance
        if (!$constructor) {
            return new $className();
        }

        // Resolve constructor dependencies
        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            // Only resolve non-primitive types (classes/interfaces)
            if ($type && !$type->isBuiltin()) {
                $dependencyClass = $type->getName();

                // Recursively resolve dependencies
                $dependencies[] = self::resolve($dependencyClass);
            }
        }

        // Create instance with resolved dependencies
        $instance = $reflectionClass->newInstanceArgs($dependencies);

        // Cache the instance for future use
        self::$services[$className] = $instance;

        return $instance;
    }

    /**
     * Inject dependencies into an existing object using attributes.
     *
     * Scans object properties with the #[Inject] attribute
     * and resolves/injects their dependencies.
     *
     * @param object $object Object to inject dependencies into
     * @return void
     * @throws ReflectionException If property reflection fails
     */
    public static function injectDependencies(object $object): void
    {
        $reflectionObject = new ReflectionObject($object);

        // Find properties with Inject attribute
        foreach ($reflectionObject->getProperties() as $property) {
            $attributes = $property->getAttributes(Inject::class);

            if (!empty($attributes)) {
                // Make the property accessible
                $property->setAccessible(true);

                // Get the injection attribute
                $injectAttribute = $attributes[0]->newInstance();

                // Resolve and inject the dependency
                $dependencyClass = $injectAttribute->service ?? $property->getType()->getName();
                $dependency = self::resolve($dependencyClass);

                // Set the property value
                $property->setValue($object, $dependency);
            }
        }
    }

    /**
     * Clear all registered services.
     *
     * Useful for testing or resetting the container state.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$services = [];
    }
}
