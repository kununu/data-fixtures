<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DynamoDb;

final readonly class Record
{
    /**
     * @param array<int, Value> $values
     */
    public function __construct(
        private array $values,
    ) {
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
            $values[$value->getName()] = $value->toDynamoDbAttribute();
        }

        return $values;
    }

    /**
     * Get raw Value objects
     *
     * @return array<int, Value>
     */
    public function getRawValues(): array
    {
        return $this->values;
    }

    /**
     * Get value by attribute name
     */
    public function getValue(string $name): ?Value
    {
        foreach ($this->values as $value) {
            if ($value->getName() === $name) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Check if record has attribute
     */
    public function hasAttribute(string $name): bool
    {
        return $this->getValue($name) !== null;
    }
}
