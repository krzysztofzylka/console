<?php

namespace Krzysztofzylka\Console;

use InvalidArgumentException;

/**
 * Arguments reader
 */
class Args
{

    /**
     * Get args in readable array
     * @param array $argv
     * @return array
     */
    public static function getArgs(array $argv): array
    {
        $return = [
            'path' => $argv[0] ?? '',
            'args' => [],
            'params' => [],
        ];

        $count = count($argv);
        $endOfOptions = false;

        for ($i = 1; $i < $count; $i++) {
            $arg = (string)$argv[$i];

            if ($endOfOptions) {
                $return['args'][] = $arg;
                continue;
            }

            if ($arg === '--') {
                $endOfOptions = true;
                continue;
            }

            if ($arg === '-' || !str_starts_with($arg, '-')) {
                $return['args'][] = $arg;
                continue;
            }

            [$name, $value, $consumedNext] = self::parseOption($arg, $argv, $i);
            self::addParam($return['params'], $name, $value);

            if ($consumedNext) {
                $i++;
            }
        }

        return $return;
    }

    /**
     * Parse arguments using an option schema.
     *
     * Supported schema keys:
     * - type: string|int|float|bool|array
     * - default: mixed
     * - required: bool
     * - multiple: bool
     * - alias / aliases: string|array
     *
     * @param array $argv
     * @param array<string, array<string, mixed>> $schema
     * @return array{path: string, args: array<int, string>, params: array<string, mixed>}
     */
    public static function parse(array $argv, array $schema): array
    {
        $parsed = self::getArgs($argv);
        $normalizedSchema = self::normalizeSchema($schema);
        $resolvedNames = [];
        $resultParams = [];

        foreach ($normalizedSchema as $name => $config) {
            $rawValue = null;

            if (array_key_exists($name, $parsed['params'])) {
                $rawValue = $parsed['params'][$name];
                $resolvedNames[] = $name;
            } else {
                foreach ($config['aliases'] as $alias) {
                    if (!array_key_exists($alias, $parsed['params'])) {
                        continue;
                    }

                    $rawValue = $parsed['params'][$alias];
                    $resolvedNames[] = $alias;
                    break;
                }
            }

            if ($rawValue === null && array_key_exists('default', $config)) {
                $resultParams[$name] = $config['default'];
                continue;
            }

            if ($rawValue === null) {
                if (($config['required'] ?? false) === true) {
                    throw new InvalidArgumentException("Missing required option '{$name}'");
                }

                continue;
            }

            $resultParams[$name] = self::coerceValue($rawValue, $name, $config);
        }

        foreach ($parsed['params'] as $name => $value) {
            if (in_array($name, $resolvedNames, true) || array_key_exists($name, $resultParams)) {
                continue;
            }

            $resultParams[$name] = $value;
        }

        return [
            'path' => $parsed['path'],
            'args' => $parsed['args'],
            'params' => $resultParams,
        ];
    }

    /**
     * @param array $argv
     * @param int $index
     * @return array{0: string, 1: string|bool, 2: bool}
     */
    private static function parseOption(string $arg, array $argv, int $index): array
    {
        $trimmed = ltrim($arg, '-');

        if (str_contains($trimmed, '=')) {
            [$name, $value] = explode('=', $trimmed, 2);

            return [$name, $value, false];
        }

        $next = $argv[$index + 1] ?? null;

        if (is_string($next) && self::canConsumeAsValue($next)) {
            return [$trimmed, $next, true];
        }

        return [$trimmed, true, false];
    }

    private static function canConsumeAsValue(string $value): bool
    {
        if ($value === '-' || $value === '') {
            return true;
        }

        return !str_starts_with($value, '-');
    }

    /**
     * @param array<string, mixed> $params
     * @param string $name
     * @param string|bool $value
     * @return void
     */
    private static function addParam(array &$params, string $name, string|bool $value): void
    {
        if (!array_key_exists($name, $params)) {
            $params[$name] = $value;
            return;
        }

        if (!is_array($params[$name])) {
            $params[$name] = [$params[$name]];
        }

        $params[$name][] = $value;
    }

    /**
     * @param array<string, array<string, mixed>> $schema
     * @return array<string, array<string, mixed>>
     */
    private static function normalizeSchema(array $schema): array
    {
        $normalized = [];

        foreach ($schema as $name => $config) {
            $aliases = [];

            if (isset($config['alias'])) {
                $aliases = is_array($config['alias']) ? $config['alias'] : [$config['alias']];
            }

            if (isset($config['aliases'])) {
                $extraAliases = is_array($config['aliases']) ? $config['aliases'] : [$config['aliases']];
                $aliases = array_merge($aliases, $extraAliases);
            }

            $config['aliases'] = array_values(array_unique(array_map('strval', $aliases)));
            $config['type'] = $config['type'] ?? 'string';
            $config['required'] = (bool)($config['required'] ?? false);
            $config['multiple'] = (bool)($config['multiple'] ?? false);

            $normalized[$name] = $config;
        }

        return $normalized;
    }

    /**
     * @param mixed $rawValue
     * @param string $name
     * @param array<string, mixed> $config
     * @return mixed
     */
    private static function coerceValue(mixed $rawValue, string $name, array $config): mixed
    {
        $multiple = (bool)($config['multiple'] ?? false);
        $type = (string)($config['type'] ?? 'string');
        $values = is_array($rawValue) ? $rawValue : [$rawValue];

        $coerced = array_map(
            fn(mixed $value) => self::coerceScalarValue($value, $type, $name),
            $values
        );

        if ($multiple) {
            return $coerced;
        }

        return end($coerced);
    }

    /**
     * @param mixed $value
     * @param string $type
     * @param string $name
     * @return mixed
     */
    private static function coerceScalarValue(mixed $value, string $type, string $name): mixed
    {
        return match ($type) {
            'string' => (string)$value,
            'int' => self::coerceInt($value, $name),
            'float' => self::coerceFloat($value, $name),
            'bool' => self::coerceBool($value, $name),
            'array' => self::coerceArray($value),
            default => throw new InvalidArgumentException("Unsupported option type '{$type}' for '{$name}'"),
        };
    }

    /**
     * @param mixed $value
     * @param string $name
     * @return int
     */
    private static function coerceInt(mixed $value, string $name): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', $value)) {
            return (int)$value;
        }

        throw new InvalidArgumentException("Option '{$name}' must be an integer");
    }

    /**
     * @param mixed $value
     * @param string $name
     * @return float
     */
    private static function coerceFloat(mixed $value, string $name): float
    {
        if (is_float($value) || is_int($value)) {
            return (float)$value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float)$value;
        }

        throw new InvalidArgumentException("Option '{$name}' must be a float");
    }

    /**
     * @param mixed $value
     * @param string $name
     * @return bool
     */
    private static function coerceBool(mixed $value, string $name): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower((string)$value);

        return match ($normalized) {
            '1', 'true', 'yes', 'y', 'on', 'tak', 't' => true,
            '0', 'false', 'no', 'n', 'off', 'nie' => false,
            default => throw new InvalidArgumentException("Option '{$name}' must be a boolean"),
        };
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private static function coerceArray(mixed $value): array
    {
        if (is_array($value)) {
            return array_map('strval', $value);
        }

        if (is_string($value)) {
            if ($value === '') {
                return [];
            }

            return array_map('trim', explode(',', $value));
        }

        return [(string)$value];
    }

}
