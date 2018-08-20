<?php declare(strict_types = 1);

$classes = [
    \Ds\Collection::class,
    \Ds\Hashable::class,
    \Ds\Sequence::class,
    \Ds\Vector::class,
    \Ds\Deque::class,
    \Ds\Map::class,
    \Ds\Pair::class,
    \Ds\Set::class,
    \Ds\Stack::class,
    \Ds\Queue::class,
    \Ds\PriorityQueue::class,
];

function formatType(?ReflectionType $type, string $default): string {
    if ($type === null) {
        return $default;
    }

    return ($type->allowsNull() ? '?' : '') . $type->getName();
}

$functionMap = [];

foreach ($classes as $class) {
    $reflection = new \ReflectionClass($class);

    foreach ($reflection->getMethods() as $method) {
        $methodKey = $method->getDeclaringClass()->getName() . '::' . $method->getName();

        if (isset($functionMap[$methodKey]) || substr($methodKey, 0, 3) !== 'Ds\\') {
            continue;
        }

        $returnType = formatType($method->getReturnType(), 'void');
        $arguments = [$returnType];

        foreach ($method->getParameters() as $parameter) {
            $parameterKey = ($parameter->isPassedByReference() ? '&' : '')
                . ($parameter->isVariadic() ? '...' : '')
                . $parameter->getName()
                . ($parameter->isOptional() ? '=' : '');

            $arguments[$parameterKey] = formatType($parameter->getType(), 'mixed');
        }

        $functionMap[$methodKey] = $arguments;
    }
}

echo '<?php';
echo PHP_EOL;
echo PHP_EOL;
echo 'return [';
echo PHP_EOL;

foreach ($functionMap as $method => $arguments) {
    $parameters = '';

    foreach ($arguments as $name => $type) {
        if ($name === 0) {
            continue;
        }

        $parameters .= sprintf(
            ', \'%s\'=>\'%s\'',
            $name,
            $type
        );
    }

    echo sprintf(
        '\'%s\' => [\'%s\'%s],',
        $method,
        $arguments[0],
        $parameters
    );

    echo PHP_EOL;
}

echo '];';
echo PHP_EOL;
