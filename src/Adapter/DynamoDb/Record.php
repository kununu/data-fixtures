<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DynamoDb;

final readonly class Record
{
    /** @param array<int, Value> $values */
    public function __construct(public array $values)
    {
    }

    /**
     * Get values as associative array with attribute names as keys
     *
     * @return array<string, array<string, mixed>>
     */
    public function getValues(): array
    {
        $values = [];
        foreach ($this->values as $value) {
            $values[$value->name] = $value->toDynamoDbAttribute();
        }

        return $values;
    }

    public function getValueByAttribute(string $name): ?Value
    {
        foreach ($this->values as $value) {
            if ($value->name === $name) {
                return $value;
            }
        }

        return null;
    }

    public function hasAttribute(string $name): bool
    {
        return $this->getValueByAttribute($name) !== null;
    }
}
