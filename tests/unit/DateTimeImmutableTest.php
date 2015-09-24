<?php

namespace Improv\DateTime;

use Improv\DateTime\Test\AbstractTestCase;
use phpmock\phpunit\PHPMock;

/**
 * @coversDefaultClass \Improv\DateTime\DateTimeImmutable
 */
class DateTimeImmutableTest extends AbstractTestCase
{

    use PHPMock;

    /**
     * @test
     * @covers ::__construct
     */
    public function constructionDefault()
    {
        $timestamp_now   = 1443047538;
        $microtime_value = $timestamp_now + 0.144300;
        $result_expected = '2015-09-23 18:32:18.144299';

        $microtime = $this->getFunctionMock(__NAMESPACE__, 'microtime');
        $microtime->expects($this->once())
            ->with(true)
            ->will($this->returnValue($microtime_value));

        $strtotime = $this->getFunctionMock(__NAMESPACE__, 'strtotime');
        $strtotime->expects($this->once())
            ->with('now')
            ->will($this->returnValue($timestamp_now));

        $sut           = new DateTimeImmutable();
        $result_actual = $sut->format('Y-m-d H:i:s.u');

        $this->assertEquals($result_expected, $result_actual);

    }

    /**
     * @test
     * @covers ::__construct
     */
    public function constructionParameterized()
    {
        $timestamp_now   = 1443047538;
        $date_input      = '2015-09-23 18:32:18';
        $result_expected = $date_input . '.000000';
        // Skipping the mocking here makes testing considerably easier
        //  given the "Immutable" nature of the DateTime object...
        $timezone        = new \DateTimeZone('Europe/Monaco');

        $microtime = $this->getFunctionMock(__NAMESPACE__, 'microtime');
        $microtime->expects($this->never());

        $strtotime = $this->getFunctionMock(__NAMESPACE__, 'strtotime');
        $strtotime->expects($this->once())
            ->with($date_input)
            ->will($this->returnValue($timestamp_now));

        $sut           = new DateTimeImmutable($date_input, $timezone);
        $result_actual = $sut->format('Y-m-d H:i:s.u');

        $this->assertEquals($result_expected, $result_actual);
        $this->assertEquals($timezone, $sut->getTimezone());
    }
}
