<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter\DynamoDb;

use Kununu\DataFixtures\Adapter\DynamoDb\Record;
use Kununu\DataFixtures\Adapter\DynamoDb\Value;
use PHPUnit\Framework\TestCase;

final class RecordTest extends TestCase
{
    public function testGetValues(): void
    {
        $values = [
            Value::stringValue('id', 'user-123'),
            Value::numericValue('age', 30),
            Value::boolValue('active', true),
        ];

        $record = new Record($values);

        $expected = [
            'id'     => ['S' => 'user-123'],
            'age'    => ['N' => '30'],
            'active' => ['BOOL' => true],
        ];

        self::assertSame($expected, $record->getValues());
    }

    public function testGetRawValues(): void
    {
        $values = [
            Value::stringValue('name', 'John'),
            Value::numericValue('score', 100),
        ];

        $record = new Record($values);

        self::assertSame($values, $record->getRawValues());
    }

    public function testGetValueExisting(): void
    {
        $nameValue = Value::stringValue('name', 'John');
        $ageValue = Value::numericValue('age', 25);

        $record = new Record([$nameValue, $ageValue]);

        self::assertSame($nameValue, $record->getValue('name'));
        self::assertSame($ageValue, $record->getValue('age'));
    }

    public function testGetValueNonExisting(): void
    {
        $record = new Record([Value::stringValue('name', 'John')]);

        self::assertNull($record->getValue('nonexistent'));
    }

    public function testHasAttributeExisting(): void
    {
        $record = new Record([
            Value::stringValue('name', 'John'),
            Value::numericValue('age', 25),
        ]);

        self::assertTrue($record->hasAttribute('name'));
        self::assertTrue($record->hasAttribute('age'));
    }

    public function testHasAttributeNonExisting(): void
    {
        $record = new Record([Value::stringValue('name', 'John')]);

        self::assertFalse($record->hasAttribute('nonexistent'));
    }

    public function testEmptyRecord(): void
    {
        $record = new Record([]);

        self::assertSame([], $record->getValues());
        self::assertSame([], $record->getRawValues());
        self::assertNull($record->getValue('any'));
        self::assertFalse($record->hasAttribute('any'));
    }

    public function testComplexRecord(): void
    {
        $values = [
            Value::stringValue('id', 'user-456'),
            Value::stringSetValue('tags', ['developer', 'php']),
            Value::mapValue('address', [
                'street' => ['S' => '123 Main St'],
                'city'   => ['S' => 'New York'],
            ]),
            Value::nullValue('deleted_at'),
        ];

        $record = new Record($values);

        $expected = [
            'id'      => ['S' => 'user-456'],
            'tags'    => ['SS' => ['developer', 'php']],
            'address' => ['M' => [
                'street' => ['S' => '123 Main St'],
                'city'   => ['S' => 'New York'],
            ]],
            'deleted_at' => ['NULL' => true],
        ];

        self::assertSame($expected, $record->getValues());
        self::assertTrue($record->hasAttribute('id'));
        self::assertTrue($record->hasAttribute('tags'));
        self::assertTrue($record->hasAttribute('address'));
        self::assertTrue($record->hasAttribute('deleted_at'));
        self::assertFalse($record->hasAttribute('nonexistent'));
    }
}
