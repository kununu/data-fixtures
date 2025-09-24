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

        self::assertSame('test_name', $value->getName());
        self::assertSame(AttributeType::String, $value->getType());
        self::assertSame('test_value', $value->getValue());
        self::assertSame(['S' => 'test_value'], $value->toDynamoDbAttribute());
    }

    public function testNumericValueWithInteger(): void
    {
        $value = Value::numericValue('count', 42);

        self::assertSame('count', $value->getName());
        self::assertSame(AttributeType::Numeric, $value->getType());
        self::assertSame('42', $value->getValue());
        self::assertSame(['N' => '42'], $value->toDynamoDbAttribute());
    }

    public function testNumericValueWithFloat(): void
    {
        $value = Value::numericValue('price', 19.99);

        self::assertSame('price', $value->getName());
        self::assertSame(AttributeType::Numeric, $value->getType());
        self::assertSame('19.99', $value->getValue());
        self::assertSame(['N' => '19.99'], $value->toDynamoDbAttribute());
    }

    public function testBoolValue(): void
    {
        $value = Value::boolValue('is_active', true);

        self::assertSame('is_active', $value->getName());
        self::assertSame(AttributeType::Bool, $value->getType());
        self::assertTrue($value->getValue());
        self::assertSame(['BOOL' => true], $value->toDynamoDbAttribute());
    }

    public function testBinaryValue(): void
    {
        $value = Value::binaryValue('data', 'binary_data');

        self::assertSame('data', $value->getName());
        self::assertSame(AttributeType::Binary, $value->getType());
        self::assertSame('binary_data', $value->getValue());
        self::assertSame(['B' => 'binary_data'], $value->toDynamoDbAttribute());
    }

    public function testStringSetValue(): void
    {
        $stringSet = ['value1', 'value2', 'value3'];
        $value = Value::stringSetValue('tags', $stringSet);

        self::assertSame('tags', $value->getName());
        self::assertSame(AttributeType::StringSet, $value->getType());
        self::assertSame($stringSet, $value->getValue());
        self::assertSame(['SS' => $stringSet], $value->toDynamoDbAttribute());
    }

    public function testNumericSetValue(): void
    {
        $numericSet = [1, 2, 3];
        $value = Value::numericSetValue('scores', $numericSet);

        self::assertSame('scores', $value->getName());
        self::assertSame(AttributeType::NumericSet, $value->getType());
        self::assertSame(['1', '2', '3'], $value->getValue());
        self::assertSame(['NS' => ['1', '2', '3']], $value->toDynamoDbAttribute());
    }

    public function testBinarySetValue(): void
    {
        $binarySet = ['data1', 'data2'];
        $value = Value::binarySetValue('files', $binarySet);

        self::assertSame('files', $value->getName());
        self::assertSame(AttributeType::BinarySet, $value->getType());
        self::assertSame($binarySet, $value->getValue());
        self::assertSame(['BS' => $binarySet], $value->toDynamoDbAttribute());
    }

    public function testMapValue(): void
    {
        $mapData = ['key1' => 'value1', 'key2' => 'value2'];
        $value = Value::mapValue('metadata', $mapData);

        self::assertSame('metadata', $value->getName());
        self::assertSame(AttributeType::Map, $value->getType());
        self::assertSame($mapData, $value->getValue());
        self::assertSame(['M' => $mapData], $value->toDynamoDbAttribute());
    }

    public function testListValue(): void
    {
        $listData = ['item1', 'item2', 'item3'];
        $value = Value::listValue('items', $listData);

        self::assertSame('items', $value->getName());
        self::assertSame(AttributeType::List, $value->getType());
        self::assertSame($listData, $value->getValue());
        self::assertSame(['L' => $listData], $value->toDynamoDbAttribute());
    }

    public function testNullValue(): void
    {
        $value = Value::nullValue('empty_field');

        self::assertSame('empty_field', $value->getName());
        self::assertSame(AttributeType::Null, $value->getType());
        self::assertTrue($value->getValue());
        self::assertSame(['NULL' => true], $value->toDynamoDbAttribute());
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

        self::assertSame($name, $result->getName());
        self::assertSame($expectedType, $result->getType());
        self::assertSame($expectedValue, $result->getValue());
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
