<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DynamoDb;

final readonly class Value
{
    public function __construct(
        public string $name,
        public AttributeType $type,
        public mixed $value,
    ) {
    }

    public static function stringValue(string $name, string $value): self
    {
        return new self($name, AttributeType::String, $value);
    }

    public static function numericValue(string $name, int|float $value): self
    {
        return new self($name, AttributeType::Numeric, (string) $value);
    }

    public static function boolValue(string $name, bool $value): self
    {
        return new self($name, AttributeType::Bool, $value);
    }

    public static function binaryValue(string $name, string $value): self
    {
        return new self($name, AttributeType::Binary, $value);
    }

    public static function stringSetValue(string $name, array $value): self
    {
        return new self($name, AttributeType::StringSet, $value);
    }

    public static function numericSetValue(string $name, array $value): self
    {
        return new self($name, AttributeType::NumericSet, array_map(strval(...), $value));
    }

    public static function binarySetValue(string $name, array $value): self
    {
        return new self($name, AttributeType::BinarySet, $value);
    }

    public static function mapValue(string $name, array $value): self
    {
        return new self($name, AttributeType::Map, $value);
    }

    public static function listValue(string $name, array $value): self
    {
        return new self($name, AttributeType::List, $value);
    }

    public static function nullValue(string $name): self
    {
        return new self($name, AttributeType::Null, true);
    }

    /** @return array<string, mixed> */
    public function toDynamoDbAttribute(): array
    {
        return [
            $this->type->value => $this->value,
        ];
    }
}
