<?php

namespace Improv\Http\Request;

/**
 * @coversDefaultClass \Improv\Http\Request\Method
 */
class MethodTest extends \PHPUnit_Framework_TestCase {

  /**
   * @test
   * @dataProvider validMethodProvider
   *
   * @covers ::isValid
   */
  public function testIsValidMethod( $code, $expected ) {

    $actual = Method::isValid( $code );
    $this->assertTrue( $expected === $actual );

  }

  /**
   * @return array
   */
  public function validMethodProvider() {

    return [

      [ 'GET', true ],
      [ 'POST', true ],
      [ 'GET /something', false],
      [ 'JIM', false ]

    ];

  }

}
