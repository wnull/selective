<?php

declare(strict_types=1);

namespace Wnull\Selective\Helper;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use Wnull\Selective\Exception\NotBooleanReturnTypeException;

trait Reflective
{
    /**
     * @throws ReflectionException
     */
    protected function reflectionPropertyValue(object $instance, string $property): array
    {
        $reflection = new ReflectionClass($instance);

        return $reflection->getProperty($property)->getValue($instance);
    }

    /**
     * @throws ReflectionException
     */
    protected function reflectionIsBooleanReturnTypeClosure(Closure $closure): bool
    {
        $reflection = new ReflectionFunction($closure);

        if (!$reflection->getReturnType() || $reflection->getReturnType()->getName() !== 'bool') {
            throw new NotBooleanReturnTypeException('Method must always return a bool');
        }

        return true;
    }
}
