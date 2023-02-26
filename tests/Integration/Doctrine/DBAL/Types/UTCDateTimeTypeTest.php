<?php
declare(strict_types = 1);
/**
 * /tests/Integration/Doctrine/DBAL/Types/UTCDateTimeTypeTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */

namespace App\Tests\Integration\Doctrine\DBAL\Types;

use App\Doctrine\DBAL\Types\UTCDateTimeType;
use App\Utils\Tests\PhpUnitUtil;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;

/**
 * Class UTCDateTimeTypeTest
 *
 * @package App\Tests\Integration\Doctrine\DBAL\Types
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */
class UTCDateTimeTypeTest extends KernelTestCase
{
    /**
     * @throws Throwable
     *
     * @testdox Test that `convertToDatabaseValue` method works as expected
     */
    public function testThatDateTimeConvertsToDatabaseValue(): void
    {
        $type = $this->getType();
        $platform = $this->getPlatform();

        $dateInput = new DateTime('1981-04-07 10:00:00', new DateTimeZone('Europe/Helsinki'));
        $dateExpected = clone $dateInput;

        $expected = $dateExpected
            ->setTimezone(new DateTimeZone('UTC'))
            ->format($platform->getDateTimeTzFormatString());

        self::assertSame($expected, $type->convertToDatabaseValue($dateInput, $platform));
    }

    /**
     * @throws Throwable
     *
     * @testdox Test that `convertToDatabaseValue` method creates DateTimeZone instance as expected
     */
    public function testThatConvertToDatabaseValueCreatesTimeZoneInstanceIfItIsNull(): void
    {
        $type = $this->getType();
        $platform = $this->getPlatform();

        PhpUnitUtil::setProperty('utc', null, $type);

        self::assertNull(PhpUnitUtil::getProperty('utc', $type));

        $dateInput = new DateTime('1981-04-07 10:00:00', new DateTimeZone('Europe/Helsinki'));

        $type->convertToDatabaseValue($dateInput, $platform);

        $property = PhpUnitUtil::getProperty('utc', $type);

        self::assertNotNull($property);
        self::assertInstanceOf(DateTimeZone::class, $property);
        self::assertSame('UTC', $property->getName());
    }

    /**
     * @throws Throwable
     *
     * @testdox Test that `convertToPHPValue` method converts `$value` to `$expected`
     */
    #[DataProvider('dataProviderTestDateTimeConvertsToPHPValue')]
    public function testDateTimeConvertsToPHPValue(string $expected, string | DateTime $value): void
    {
        $type = $this->getType();
        $platform = $this->getPlatform();

        $date = $type->convertToPHPValue($value, $platform);

        self::assertInstanceOf(DateTime::class, $date);
        self::assertSame($expected, $date->format('Y-m-d H:i:s'));
    }

    /**
     * @throws Throwable
     *
     * @testdox Test that `convertToPHPValue` method creates DateTimeZone instance as expected
     */
    public function testThatConvertToPHPValueCreatesTimeZoneInstanceIfItIsNull(): void
    {
        $type = $this->getType();
        $platform = $this->getPlatform();

        PhpUnitUtil::setProperty('utc', null, $type);

        self::assertNull(PhpUnitUtil::getProperty('utc', $type));

        $type->convertToPHPValue('1981-04-07 10:00:00', $platform);

        $property = PhpUnitUtil::getProperty('utc', $type);

        self::assertNotNull($property);
        self::assertInstanceOf(DateTimeZone::class, $property);
        self::assertSame('UTC', $property->getName());
    }

    /**
     * @throws Throwable
     *
     * @testdox Test that `convertToPHPValue` method throws an exception when invalid value is used
     */
    public function testThatConvertToPHPValueThrowsAnExceptionWithInvalidValue(): void
    {
        $this->expectException(ConversionException::class);

        $type = $this->getType();
        $platform = $this->getPlatform();

        $type->convertToPHPValue('foobar', $platform);
    }

    /**
     * @throws Throwable
     *
     * @return Generator<array{0: string, 1: string|DateTime}>
     */
    public static function dataProviderTestDateTimeConvertsToPHPValue(): Generator
    {
        yield [
            '1981-04-07 10:00:00',
            '1981-04-07 10:00:00',
        ];

        yield [
            '1981-04-07 07:00:00',
            new DateTime('1981-04-07 10:00:00', new DateTimeZone('Europe/Helsinki')),
        ];

        yield [
            '1981-04-07 10:00:00',
            new DateTime('1981-04-07 10:00:00', new DateTimeZone('UTC')),
        ];
    }

    private function getPlatform(): AbstractPlatform
    {
        return new MySQLPlatform();
    }

    /**
     * @throws Throwable
     */
    private function getType(): UTCDateTimeType
    {
        Type::hasType('datetime')
            ? Type::overrideType('datetime', UTCDateTimeType::class)
            : Type::addType('datetime', UTCDateTimeType::class);

        $type = Type::getType('datetime');

        self::assertInstanceOf(UTCDateTimeType::class, $type);

        return $type;
    }
}
