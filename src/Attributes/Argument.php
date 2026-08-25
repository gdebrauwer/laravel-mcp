<?php

declare(strict_types=1);

namespace Laravel\Mcp\Attributes;

use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use ReflectionNamedType;
use ReflectionParameter;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Argument implements ContextualAttribute
{
    public function __construct(public ?string $name = null) {}

    public static function resolve(self $attribute, Container $container, ReflectionParameter $parameter): mixed
    {
        /** @var Request $request */
        $request = $container->make(Request::class);
        $type = $parameter->getType();

        $key = $attribute->name ?? $parameter->getName();
        $arguments = $request->all();

        if (! array_key_exists($key, $arguments)) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            if ($type && $type->allowsNull()) {
                return null;
            }

            throw ValidationException::withMessages([
                $key => ["The {$key} field is required."],
            ]);
        }

        $value = $arguments[$key];

        if ($value === null) {
            return null;
        }

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return $value;
        }

        $class = $type->getName();

        if (! is_a($class, Model::class, true)) {
            return $value;
        }

        if ($value instanceof $class) {
            return $value;
        }

        $model = new $class;

        $resolved = $model->resolveRouteBinding(
            $value,
            $model->getRouteKeyName(),
        );

        if ($resolved === null) {
            throw (new ModelNotFoundException)->setModel($class, [$value]);
        }

        return $resolved;
    }
}
