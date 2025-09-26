<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Tests\Adapter\DynamoDb;

use Kununu\DataFixtures\Adapter\DynamoDb\AttributeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AttributeTypeTest extends TestCase
{
    #[DataProvider('attributeTypeDataProvider')]
    public function testAttributeTypeValues(AttributeType $type, string $expectedValue): void
    {
        self::assertEquals($expectedValue, $type->value);
    }

    public function testAllAttributeTypesAreCovered(): void
    {
        $expectedTypes = [
            'String'     => 'S',
            'Numeric'    => 'N',
            'Binary'     => 'B',
            'StringSet'  => 'SS',
            'NumericSet' => 'NS',
            'BinarySet'  => 'BS',
            'Map'        => 'M',
            'List'       => 'L',
            'Null'       => 'NULL',
            'Bool'       => 'BOOL',
        ];

        $cases = AttributeType::cases();

        foreach ($cases as $case) {
            self::assertArrayHasKey($case->name, $expectedTypes);
            self::assertEquals($expectedTypes[$case->name], $case->value);
        }
    }

    public static function attributeTypeDataProvider(): array
    {
        return [
            'string_type'      => [AttributeType::String, 'S'],
            'numeric_type'     => [AttributeType::Numeric, 'N'],
            'binary_type'      => [AttributeType::Binary, 'B'],
            'string_set_type'  => [AttributeType::StringSet, 'SS'],
            'numeric_set_type' => [AttributeType::NumericSet, 'NS'],
            'binary_set_type'  => [AttributeType::BinarySet, 'BS'],
            'map_type'         => [AttributeType::Map, 'M'],
            'list_type'        => [AttributeType::List, 'L'],
            'null_type'        => [AttributeType::Null, 'NULL'],
            'bool_type'        => [AttributeType::Bool, 'BOOL'],
        ];
    }
}
