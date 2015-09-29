<?php

namespace Improv\DateTime\Factories;

use Improv\DateTime\DateTimeImmutable;
use Improv\DateTime\Factories\Interfaces\IDateTimeFactory;
use Improv\DateTime\Test\AbstractTestCase;
use phpmock\phpunit\PHPMock;

/**
 * @coversDefaultClass \Improv\DateTime\Factories\DateTimeFactory
 */
class DateTimeFactoryTest extends AbstractTestCase
{

    /**
     * @test
     * @covers ::__construct
     */
    public function instanceOfInterface()
    {
        $sut = new DateTimeFactory();
        $this->assertInstanceOf(IDateTimeFactory::class, $sut);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getDefaultTimezone
     */
    public function defaultToUTC()
    {
        $sut  = new DateTimeFactory();
        $zone = $sut->getDefaultTimezone();

        $this->assertSame('UTC', $zone->getName());
    }

    /**
     * @test
     * @covers ::now
     * @covers ::__construct
     */
    public function now()
    {
        /** @var \DateTimeZone $timezone */
        $timezone = $this->getFullMock(\DateTimeZone::class);
        /** @var DateTimeImmutable $datetime */
        $datetime = $this->getFullMock(DateTimeImmutable::class);
        /** @var DateTimeFactory $sut */
        $sut      = $this->getPartialMock(DateTimeFactory::class, ['createDateTime'], [$timezone]);

        $sut->expects($this->once())
            ->method('createDateTime')
            ->with(DateTimeImmutable::NOW, $timezone)
            ->will($this->returnValue($datetime));

        $result_actual = $sut->now();

        $this->assertSame($datetime, $result_actual);
    }

    /**
     * @test
     * @covers ::create
     * @covers ::__construct
     */
    public function create()
    {
        /** @var \DateTimeZone $timezone */
        $timezone = $this->getFullMock(\DateTimeZone::class);
        /** @var DateTimeImmutable $datetime */
        $datetime = $this->getFullMock(DateTimeImmutable::class);
        /** @var DateTimeFactory $sut */
        $sut      = $this->getPartialMock(DateTimeFactory::class, ['createDateTime'], [$timezone]);
        $time     = 'Irrelevant string representation of time';

        $sut->expects($this->once())
            ->method('createDateTime')
            ->with($time, $timezone)
            ->will($this->returnValue($datetime));

        $result_actual = $sut->create($time);

        $this->assertSame($datetime, $result_actual);
    }

    /**
     * @test
     * @covers ::nowInTimeZone
     * @covers ::__construct
     */
    public function nowInTimezone()
    {
        /** @var \DateTimeZone $timezone_configured */
        $timezone_configured = $this->getFullMock(\DateTimeZone::class);
        /** @var \DateTimeZone $timezone_desired */
        $timezone_desired    = $this->getFullMock(\DateTimeZone::class);
        /** @var DateTimeImmutable $datetime */
        $datetime            = $this->getFullMock(DateTimeImmutable::class);

        /** @var DateTimeFactory $sut */
        $sut = $this->getPartialMock(DateTimeFactory::class, ['createDateTime'], [$timezone_configured]);

        $sut->expects($this->once())
            ->method('createDateTime')
            ->with(DateTimeImmutable::NOW, $timezone_desired)
            ->will($this->returnValue($datetime));

        $result_actual = $sut->nowInTimeZone($timezone_desired);

        $this->assertSame($datetime, $result_actual);
    }

    /**
     * @test
     * @covers ::createInTimeZone
     * @covers ::__construct
     */
    public function createInTimezone()
    {
        /** @var \DateTimeZone $timezone_configured */
        $timezone_configured = $this->getFullMock(\DateTimeZone::class);
        /** @var \DateTimeZone $timezone_desired */
        $timezone_desired    = $this->getFullMock(\DateTimeZone::class);
        /** @var DateTimeImmutable $datetime */
        $datetime            = $this->getFullMock(DateTimeImmutable::class);

        /** @var DateTimeFactory $sut */
        $sut  = $this->getPartialMock(DateTimeFactory::class, ['createDateTime'], [$timezone_configured]);
        $time = 'Irrelevant string representation of time';

        $sut->expects($this->once())
            ->method('createDateTime')
            ->with($time, $timezone_desired)
            ->will($this->returnValue($datetime));

        $result_actual = $sut->createInTimeZone($time, $timezone_desired);

        $this->assertSame($datetime, $result_actual);
    }
}
