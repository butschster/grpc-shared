<?php

declare(strict_types=1);

namespace Shared\gRPC\Validation;

use Shared\gRPC\Command\Common\v1\DTO\ValidationError;
use Shared\gRPC\Command\Common\v1\DTO\ValidationErrorMeta;
use Ramsey\Uuid\Uuid;

final class Assert
{
    public static function uuid(mixed $value, string $message = 'uuid_expected'): \Generator
    {
        if (!Uuid::isValid($value)) {
            yield new ValidationError($message);
        }
    }

    public static function uuidOrNull(mixed $value, string $message = 'uuid_expected'): \Generator
    {
        if ($value !== null && !Uuid::isValid($value)) {
            yield new ValidationError($message);
        }
    }

    /**
     * @deprecated
     */
    public static function password(mixed $value, string $message = 'password_requirements_not_met'): \Generator
    {
        yield from self::string($value);

        if (!\preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $value)) {
            yield new ValidationError(
                message: $message,
                meta: [
                    new ValidationErrorMeta(
                        key: 'req',
                        value: 'uppercase',
                    ),
                    new ValidationErrorMeta(
                        key: 'req',
                        value: 'lowercase',
                    ),
                    new ValidationErrorMeta(
                        key: 'req',
                        value: 'digit',
                    ),
                ],
            );
        }
    }

    public static function string(mixed $value, string $message = 'string_expected'): \Generator
    {
        if (!\is_string($value)) {
            yield new ValidationError($message);
        }
    }

    public static function stringNotEmpty(mixed $value, string $message = 'not_empty_string_expected'): \Generator
    {
        yield from self::string($value);

        if ('' === $value) {
            yield new ValidationError($message);
        }
    }

    public static function integer(mixed $value, string $message = 'int_expected'): \Generator
    {
        if (!\is_int($value)) {
            yield new ValidationError($message);
        }
    }

    public static function positiveInteger(mixed $value, string $message = 'positive_int_expected'): \Generator
    {
        yield from self::integer($value);

        if (!\is_int($value) || $value < 1) {
            yield new ValidationError($message);
        }
    }

    public static function unsignedInteger(mixed $value, string $message = 'unsigned_int_expected'): \Generator
    {
        yield from self::integer($value);

        if (!\is_int($value) || $value < 0) {
            yield new ValidationError($message);
        }
    }

    public static function float(mixed $value, string $message = 'float_expected'): \Generator
    {
        if (!\is_float($value)) {
            yield new ValidationError($message);
        }
    }

    public static function numeric(mixed $value, string $message = 'numeric_expected'): \Generator
    {
        if (!\is_numeric($value)) {
            yield new ValidationError($message);
        }
    }

    public static function boolean(mixed $value, string $message = 'boolean_expected'): \Generator
    {
        if (!\is_bool($value)) {
            yield new ValidationError($message);
        }
    }

    public static function scalar(mixed $value, string $message = 'scalar_expected'): \Generator
    {
        if (!\is_scalar($value)) {
            yield new ValidationError($message);
        }
    }

    public static function object(mixed $value, string $message = 'object_expected'): \Generator
    {
        if (!\is_object($value)) {
            yield new ValidationError($message);
        }
    }

    public static function isCallable(mixed $value, string $message = 'callable_expected'): \Generator
    {
        if (!\is_callable($value)) {
            yield new ValidationError($message);
        }
    }

    public static function isArray(mixed $value, string $message = 'array_expected'): \Generator
    {
        if (!\is_array($value)) {
            yield new ValidationError($message);
        }
    }

    public static function isEmpty(mixed $value, string $message = 'empty_expected'): \Generator
    {
        if (!empty($value)) {
            yield new ValidationError($message);
        }
    }

    public static function notEmpty(mixed $value, string $message = 'not_empty_expected'): \Generator
    {
        if (empty($value)) {
            yield new ValidationError($message);
        }
    }

    public static function null(mixed $value, string $message = 'null_expected'): \Generator
    {
        if (null !== $value) {
            yield new ValidationError($message);
        }
    }

    public static function notNull(mixed $value, string $message = 'not_null_expected'): \Generator
    {
        if (null === $value) {
            yield new ValidationError($message);
        }
    }

    public static function ip(mixed $value, string $message = 'ip_expected'): \Generator
    {
        yield from self::string($value);

        if (false === \filter_var($value, \FILTER_VALIDATE_IP)) {
            yield new ValidationError($message);
        }
    }

    public static function ipv4(mixed $value, string $message = 'ipv4_expected'): \Generator
    {
        yield from self::string($value);

        if (false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            yield new ValidationError($message);
        }
    }

    public static function ipv6(mixed $value, string $message = 'ipv6_expected'): \Generator
    {
        yield from self::string($value);

        if (false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
            yield new ValidationError($message);
        }
    }

    public static function email(mixed $value, string $message = 'email_expected'): \Generator
    {
        yield from self::string($value);

        if (false === \filter_var($value, FILTER_VALIDATE_EMAIL)) {
            yield new ValidationError($message);
        }
    }

    public static function uniqueValues(array $values, string $message = 'unique_values_expected'): \Generator
    {
        $allValues = \count($values);
        $uniqueValues = \count(\array_unique($values));

        if ($allValues !== $uniqueValues) {
            $difference = $allValues - $uniqueValues;

            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'difference',
                    value: self::valueToString($difference)
                ),
            ]);
        }
    }

    public static function equal(string $value, string $expect, string $message = 'equal_expected'): \Generator
    {
        if ($expect != $value) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'expect',
                    value: self::valueToString($expect)
                ),
            ]);
        }
    }

    public static function notEqual(string $value, string $expect, string $message = 'not_equal_expected'): \Generator
    {
        if ($expect == $value) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'expect',
                    value: self::valueToString($expect)
                ),
            ]);
        }
    }

    public static function same(string $value, string $expect, string $message = 'same_expected'): \Generator
    {
        if ($expect !== $value) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'expect',
                    value: self::valueToString($expect)
                ),
            ]);
        }
    }

    public static function notSame(string $value, string $expect, string $message = 'not_same_expected'): \Generator
    {
        if ($expect === $value) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'expect',
                    value: self::valueToString($expect)
                ),
            ]);
        }
    }

    public static function greaterThan(
        float|int $value,
        float|int $limit,
        string $message = 'greater_than_expected',
    ): \Generator {
        if ($value <= $limit) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'limit',
                    value: self::valueToString($limit)
                ),
            ]);
        }
    }

    public static function greaterThanEqual(
        float|int $value,
        float|int $limit,
        string $message = 'greater_than_or_equal_expected',
    ): \Generator {
        if ($value < $limit) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'limit',
                    value: self::valueToString($limit)
                ),
            ]);
        }
    }

    public static function lessThan(
        float|int $value,
        float|int $limit,
        string $message = 'less_than_expected',
    ): \Generator {
        if ($value >= $limit) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'limit',
                    value: self::valueToString($limit)
                ),
            ]);
        }
    }

    public static function lessThanEqual(
        float|int $value,
        float|int $limit,
        string $message = 'less_than_or_equal_expected',
    ): \Generator {
        if ($value > $limit) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'limit',
                    value: self::valueToString($limit)
                ),
            ]);
        }
    }

    public static function range(
        float|int $value,
        float|int $min,
        float|int $max,
        string $message = 'range_expected',
    ): \Generator {
        if ($value < $min || $value > $max) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'min',
                    value: self::valueToString($min)
                ),
                new ValidationErrorMeta(
                    key: 'max',
                    value: self::valueToString($max)
                ),
            ]);
        }
    }

    public static function inArray(mixed $value, array $values, string $message = 'in_array_expected'): \Generator
    {
        if (!\in_array($value, $values, true)) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'values',
                    value: \implode(', ', \array_map([self::class, 'valueToString'], $values))
                ),
            ]);
        }
    }

    public static function count(array $array, int $number, string $message = 'count_expected'): \Generator
    {
        if (\count($array) !== $number) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'number',
                    value: self::valueToString($number)
                ),
            ]);
        }
    }

    public static function minCount(array $array, int $min, string $message = 'min_count_expected'): \Generator
    {
        if (\count($array) < $min) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'min',
                    value: self::valueToString($min)
                ),
            ]);
        }
    }

    public static function maxCount(array $array, int $max, string $message = 'max_count_expected'): \Generator
    {
        if (\count($array) > $max) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'max',
                    value: self::valueToString($max)
                ),
            ]);
        }
    }

    public static function countBetween(
        array $array,
        int $min,
        int $max,
        string $message = 'count_between_expected',
    ): \Generator {
        $count = \count($array);

        if ($count < $min || $count > $max) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'min',
                    value: self::valueToString($min)
                ),
                new ValidationErrorMeta(
                    key: 'max',
                    value: self::valueToString($max)
                ),
            ]);
        }
    }

    public static function contains(string $value, string $subString, string $message = 'contains_expected'): \Generator
    {
        if (!\str_contains($value, $subString)) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'sub_string',
                    value: self::valueToString($subString)
                ),
            ]);
        }
    }

    public static function notContains(
        string $value,
        string $subString,
        string $message = 'not_contains_expected',
    ): \Generator {
        if (\str_contains($value, $subString)) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'sub_string',
                    value: self::valueToString($subString)
                ),
            ]);
        }
    }

    public static function notWhitespaceOnly(
        string $value,
        string $message = 'not_whitespace_only_expected',
    ): \Generator {
        if (\preg_match('/^\s*$/', $value)) {
            yield new ValidationError($message);
        }
    }

    public static function startsWith(
        string $value,
        string $prefix,
        string $message = 'starts_with_expected',
    ): \Generator {
        if (!\str_starts_with($value, $prefix)) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'prefix',
                    value: self::valueToString($prefix)
                ),
            ]);
        }
    }

    public static function notStartsWith(
        string $value,
        string $prefix,
        string $message = 'not_starts_with_expected',
    ): \Generator {
        if (\str_starts_with($value, $prefix)) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'prefix',
                    value: self::valueToString($prefix)
                ),
            ]);
        }
    }

    public static function endsWith(string $value, string $suffix, string $message = 'ends_with_expected'): \Generator
    {
        if (!\str_ends_with($value, $suffix)) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'suffix',
                    value: self::valueToString($suffix)
                ),
            ]);
        }
    }

    public static function notEndsWith(
        string $value,
        string $suffix,
        string $message = 'not_ends_with_expected',
    ): \Generator {
        if (\str_ends_with($value, $suffix)) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'suffix',
                    value: self::valueToString($suffix)
                ),
            ]);
        }
    }

    public static function alpha(mixed $value, string $message = 'alpha_expected'): \Generator
    {
        yield from self::string($value);
        \assert(\is_string($value));

        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_alpha($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            yield new ValidationError($message);
        }
    }

    public static function digits(mixed $value, string $message = 'digits_expected'): \Generator
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_digit($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            yield new ValidationError($message);
        }
    }

    public static function alnum(string $value, string $message = 'alnum_expected'): \Generator
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_alnum($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            yield new ValidationError($message);
        }
    }

    public static function lower(string $value, string $message = 'lower_expected'): \Generator
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_lower($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            yield new ValidationError($message);
        }
    }

    public static function upper(string $value, string $message = 'upper_expected'): \Generator
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_upper($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            yield new ValidationError($message);
        }
    }

    public static function length(string $value, int $length, string $message = 'length_expected'): \Generator
    {
        if ($length !== self::strlen($value)) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'length',
                    value: self::valueToString($length)
                ),
            ]);
        }
    }

    public static function minLength(string $value, int $min, string $message = 'min_length_expected'): \Generator
    {
        if (self::strlen($value) < $min) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'min',
                    value: self::valueToString($min)
                ),
            ]);
        }
    }

    public static function maxLength(string $value, int $max, string $message = 'max_length_expected'): \Generator
    {
        if (self::strlen($value) > $max) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'max',
                    value: self::valueToString($max)
                ),
            ]);
        }
    }

    public static function lengthBetween(
        string $value,
        int $min,
        int $max,
        string $message = 'length_between_expected',
    ): \Generator {
        $length = self::strlen($value);

        if ($length < $min || $length > $max) {
            yield new ValidationError($message, [
                new ValidationErrorMeta(
                    key: 'min',
                    value: self::valueToString($min)
                ),
                new ValidationErrorMeta(
                    key: 'max',
                    value: self::valueToString($max)
                ),
            ]);
        }
    }

    private static function valueToString(mixed $value): string
    {
        if (null === $value) {
            return 'null';
        }

        if (true === $value) {
            return 'true';
        }

        if (false === $value) {
            return 'false';
        }

        if (\is_array($value)) {
            return 'array';
        }

        if (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                return \get_class($value) . ': ' . self::valueToString($value->__toString());
            }

            if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable) {
                return \get_class($value) . ': ' . self::valueToString($value->format('c'));
            }

            return \get_class($value);
        }

        if (\is_resource($value)) {
            return 'resource';
        }

        if (\is_string($value)) {
            return '"' . $value . '"';
        }

        return (string)$value;
    }

    private static function strlen(string $value): int
    {
        if (!\function_exists('mb_detect_encoding')) {
            return \strlen($value);
        }

        if (false === $encoding = \mb_detect_encoding($value)) {
            return \strlen($value);
        }

        return \mb_strlen($value, $encoding);
    }
}
