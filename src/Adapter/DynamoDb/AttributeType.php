<?php
declare(strict_types=1);

namespace Kununu\DataFixtures\Adapter\DynamoDb;

enum AttributeType: string
{
    case String = 'S';
    case Numeric = 'N';
    case Binary = 'B';
    case StringSet = 'SS';
    case NumericSet = 'NS';
    case BinarySet = 'BS';
    case Map = 'M';
    case List = 'L';
    case Null = 'NULL';
    case Bool = 'BOOL';
}
