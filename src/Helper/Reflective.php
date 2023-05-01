<?php

declare(strict_types=1);

namespace Wnull\CookieFilterer\Helper;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Wnull\CookieFilterer\Exception\NotBooleanReturnTypeException;

trait Reflective
{
    /**
     * @throws ReflectionException
     */
    public function reflectionPropertyValue(object $instance, string $property): array
    {
        $reflection = new ReflectionClass($instance);

        return $reflection->getProperty($property)->getValue($instance);
    }

    /**
     * @throws ReflectionException
     */
    public function reflectionIsBooleanReturnTypeClosure(Closure $closure): bool
    {
        $reflection = new ReflectionFunction($closure);

        if ($reflection->getReturnType()->getName() !== 'bool') {
            throw new NotBooleanReturnTypeException('Method must always return a bool');
        }

        return true;
    }
}
