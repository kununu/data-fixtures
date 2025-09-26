<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter\DynamoDb;

use Kununu\DataFixtures\Adapter\DynamoDb\AttributeType;
use Kununu\DataFixtures\Adapter\DynamoDb\Value;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValueTest extends TestCase
{
    public function testStringValue(): void
    {
        $value = Value::stringValue('test_name', 'test_value');

        self::assertEquals('test_name', $value->name);
        self::assertEquals(AttributeType::String, $value->type);
        self::assertEquals('test_value', $value->value);
        self::assertEquals(['S' => 'test_value'], $value->toDynamoDbAttribute());
    }

    public function testNumericValueWithInteger(): void
    {
        $value = Value::numericValue('count', 42);

        self::assertEquals('count', $value->name);
        self::assertEquals(AttributeType::Numeric, $value->type);
        self::assertEquals('42', $value->value);
        self::assertEquals(['N' => '42'], $value->toDynamoDbAttribute());
    }

    public function testNumericValueWithFloat(): void
    {
        $value = Value::numericValue('price', 19.99);

        self::assertEquals('price', $value->name);
        self::assertEquals(AttributeType::Numeric, $value->type);
        self::assertEquals('19.99', $value->value);
        self::assertEquals(['N' => '19.99'], $value->toDynamoDbAttribute());
    }

    public function testBoolValue(): void
    {
        $value = Value::boolValue('is_active', true);

        self::assertEquals('is_active', $value->name);
        self::assertEquals(AttributeType::Bool, $value->type);
        self::assertTrue($value->value);
        self::assertEquals(['BOOL' => true], $value->toDynamoDbAttribute());
    }

    public function testBinaryValue(): void
    {
        $value = Value::binaryValue('data', 'binary_data');

        self::assertEquals('data', $value->name);
        self::assertEquals(AttributeType::Binary, $value->type);
        self::assertEquals('binary_data', $value->value);
        self::assertEquals(['B' => 'binary_data'], $value->toDynamoDbAttribute());
    }

    public function testStringSetValue(): void
    {
        $stringSet = ['value1', 'value2', 'value3'];
        $value = Value::stringSetValue('tags', $stringSet);

        self::assertEquals('tags', $value->name);
        self::assertEquals(AttributeType::StringSet, $value->type);
        self::assertEquals($stringSet, $value->value);
        self::assertEquals(['SS' => $stringSet], $value->toDynamoDbAttribute());
    }

    public function testNumericSetValue(): void
    {
        $numericSet = [1, 2, 3];
        $value = Value::numericSetValue('scores', $numericSet);

        self::assertEquals('scores', $value->name);
        self::assertEquals(AttributeType::NumericSet, $value->type);
        self::assertEquals(['1', '2', '3'], $value->value);
        self::assertEquals(['NS' => ['1', '2', '3']], $value->toDynamoDbAttribute());
    }

    public function testBinarySetValue(): void
    {
        $binarySet = ['data1', 'data2'];
        $value = Value::binarySetValue('files', $binarySet);

        self::assertEquals('files', $value->name);
        self::assertEquals(AttributeType::BinarySet, $value->type);
        self::assertEquals($binarySet, $value->value);
        self::assertEquals(['BS' => $binarySet], $value->toDynamoDbAttribute());
    }

    public function testMapValue(): void
    {
        $mapData = ['key1' => 'value1', 'key2' => 'value2'];
        $value = Value::mapValue('metadata', $mapData);

        self::assertEquals('metadata', $value->name);
        self::assertEquals(AttributeType::Map, $value->type);
        self::assertEquals($mapData, $value->value);
        self::assertEquals(['M' => $mapData], $value->toDynamoDbAttribute());
    }

    public function testListValue(): void
    {
        $listData = ['item1', 'item2', 'item3'];
        $value = Value::listValue('items', $listData);

        self::assertEquals('items', $value->name);
        self::assertEquals(AttributeType::List, $value->type);
        self::assertEquals($listData, $value->value);
        self::assertEquals(['L' => $listData], $value->toDynamoDbAttribute());
    }

    public function testNullValue(): void
    {
        $value = Value::nullValue('empty_field');

        self::assertEquals('empty_field', $value->name);
        self::assertEquals(AttributeType::Null, $value->type);
        self::assertTrue($value->value);
        self::assertEquals(['NULL' => true], $value->toDynamoDbAttribute());
    }

    #[DataProvider('staticFactoryMethodsDataProvider')]
    public function testStaticFactoryMethods(
        string $method,
        string $name,
        mixed $value,
        AttributeType $expectedType,
        mixed $expectedValue,
    ): void {
        $result = Value::$method($name, $value);

        self::assertEquals($name, $result->name);
        self::assertEquals($expectedType, $result->type);
        self::assertEquals($expectedValue, $result->value);
    }

    public static function staticFactoryMethodsDataProvider(): array
    {
        return [
            'string_value'        => ['stringValue', 'name', 'value', AttributeType::String, 'value'],
            'numeric_value_int'   => ['numericValue', 'count', 100, AttributeType::Numeric, '100'],
            'numeric_value_float' => ['numericValue', 'price', 10.5, AttributeType::Numeric, '10.5'],
            'bool_value_true'     => ['boolValue', 'active', true, AttributeType::Bool, true],
            'bool_value_false'    => ['boolValue', 'inactive', false, AttributeType::Bool, false],
            'binary_value'        => ['binaryValue', 'data', 'binary', AttributeType::Binary, 'binary'],
            'string_set_value'    => ['stringSetValue', 'tags', ['a', 'b'], AttributeType::StringSet, ['a', 'b']],
            'numeric_set_value'   => ['numericSetValue', 'nums', [1, 2], AttributeType::NumericSet, ['1', '2']],
            'binary_set_value'    => ['binarySetValue', 'bins', ['x', 'y'], AttributeType::BinarySet, ['x', 'y']],
            'map_value'           => ['mapValue', 'map', ['k' => 'v'], AttributeType::Map, ['k' => 'v']],
            'list_value'          => ['listValue', 'list', [1, 2, 3], AttributeType::List, [1, 2, 3]],
        ];
    }
}
