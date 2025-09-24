<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter\DynamoDb;

use Kununu\DataFixtures\Adapter\DynamoDb\Record;
use Kununu\DataFixtures\Adapter\DynamoDb\Value;
use PHPUnit\Framework\TestCase;

final class RecordTest extends TestCase
{
    public function testConstructorWithValues(): void
    {
        $values = [
            Value::stringValue('id', 'test-id'),
            Value::numericValue('count', 42),
            Value::boolValue('active', true),
        ];

        $record = new Record($values);

        self::assertSame($values, $record->getRawValues());
    }

    public function testGetValues(): void
    {
        $values = [
            Value::stringValue('id', 'test-id'),
            Value::numericValue('count', 42),
            Value::boolValue('active', true),
        ];

        $record = new Record($values);
        $dynamoDbValues = $record->getValues();

        $expected = [
            'id'     => ['S' => 'test-id'],
            'count'  => ['N' => '42'],
            'active' => ['BOOL' => true],
        ];

        self::assertSame($expected, $dynamoDbValues);
    }

    public function testGetValueByName(): void
    {
        $idValue = Value::stringValue('id', 'test-id');
        $countValue = Value::numericValue('count', 42);

        $record = new Record([$idValue, $countValue]);

        self::assertSame($idValue, $record->getValue('id'));
        self::assertSame($countValue, $record->getValue('count'));
        self::assertNull($record->getValue('nonexistent'));
    }

    public function testHasAttribute(): void
    {
        $values = [
            Value::stringValue('id', 'test-id'),
            Value::numericValue('count', 42),
        ];

        $record = new Record($values);

        self::assertTrue($record->hasAttribute('id'));
        self::assertTrue($record->hasAttribute('count'));
        self::assertFalse($record->hasAttribute('nonexistent'));
    }

    public function testEmptyRecord(): void
    {
        $record = new Record([]);

        self::assertEmpty($record->getRawValues());
        self::assertEmpty($record->getValues());
        self::assertNull($record->getValue('any'));
        self::assertFalse($record->hasAttribute('any'));
    }

    public function testRecordWithComplexTypes(): void
    {
        $values = [
            Value::stringValue('id', 'test-id'),
            Value::stringSetValue('tags', ['tag1', 'tag2']),
            Value::numericSetValue('scores', [10, 20, 30]),
            Value::mapValue('metadata', ['key' => 'value', 'nested' => ['inner' => 'data']]),
            Value::listValue('items', ['item1', 'item2']),
            Value::nullValue('empty_field'),
            Value::binaryValue('data', 'binary_content'),
        ];

        $record = new Record($values);

        $expected = [
            'id'          => ['S' => 'test-id'],
            'tags'        => ['SS' => ['tag1', 'tag2']],
            'scores'      => ['NS' => ['10', '20', '30']],
            'metadata'    => ['M' => ['key' => 'value', 'nested' => ['inner' => 'data']]],
            'items'       => ['L' => ['item1', 'item2']],
            'empty_field' => ['NULL' => true],
            'data'        => ['B' => 'binary_content'],
        ];

        self::assertSame($expected, $record->getValues());
        self::assertCount(7, $record->getRawValues());
    }

    public function testGetValueWithDuplicateNames(): void
    {
        // Test that the first occurrence is returned when there are duplicate names
        $value1 = Value::stringValue('duplicate', 'first');
        $value2 = Value::stringValue('duplicate', 'second');
        $value3 = Value::numericValue('unique', 123);

        $record = new Record([$value1, $value2, $value3]);

        // Should return the first occurrence
        self::assertSame($value1, $record->getValue('duplicate'));
        self::assertTrue($record->hasAttribute('duplicate'));
        self::assertSame($value3, $record->getValue('unique'));
    }
}
