[![Build Status](https://travis-ci.org/improvframework/datetime.svg?branch=master)](https://travis-ci.org/improvframework/datetime)
[![Dependency Status](https://www.versioneye.com/user/projects/56038f55f5f2eb001a000cca/badge.svg?style=flat)](https://www.versioneye.com/user/projects/56038f55f5f2eb001a000cca)
[![Code Climate](https://codeclimate.com/github/improvframework/datetime/badges/gpa.svg)](https://codeclimate.com/github/improvframework/datetime)
[![Coverage Status](https://coveralls.io/repos/improvframework/datetime/badge.svg?branch=master&service=github)](https://coveralls.io/github/improvframework/datetime?branch=master)
[![HHVM Status](http://hhvm.h4cc.de/badge/improvframework/datetime.svg)](http://hhvm.h4cc.de/package/improvframework/datetime)

# Improv Framework - DateTime

DateTime library intended to augment PHP's base DateTime-related functionality

## Overview

This package was created to provide a common interface for the creation and injection of DateTime objects,
because **time is depedency**.  It additionally offers microtime information as part of a limited set of
relative creation strings.

## Installation

### Via Composer (Recommended)

This package is most-easily installed as a dependency of your project by using [Composer](https://getcomposer.org/ "Click to Learn More")

```
composer require improvframework/datetime
```

### Packaged Artifact

Each release is available via a [Github zip file](https://github.com/improvframework/datetime/releases).

Alternatively, you may fork, clone, and [build the package](#buildpackage).

### Phar Package

Not yet available. Hopefully this will be offered shortly.

## Usage

```php
use Improv\DateTime\Factories;

// By default, with no parameters, the factory will generate all DateTime objects in UTC.
$factory = new DateTimeFactory();

// Create an \Improv\DateTime\DateTimeImmutable object with the current
//  system time in the configured TimeZone (in this case, the default UTC).
$now     = $factory->now();

// Create an Improv DateTimeImmutable object from a string, suitable for strtotime(...)
//  Still in UTC.
$tues    = $factory->create( 'next tuesday' );

// Create an Improv DateTimeImmutable with the current system time in a non-default Timezone
$now_nyc = $factory->nowInTimeZone( new \DateTimeZone( 'America/New_York' ) );

// Configure another factory with a different default timezone
$factory = new DateTimeFactory( new \DateTimeZone( 'Europe/Monaco' ) );

// Object will be created with Europe/Monaco TimeZone, as has been configured above
$now_eu  = $factory->now();

// This is basically equal to $now, above (overlooking microsecond/second differences in script execution)
$now_utc = $factory->createInTimeZone( \Improv\DateTime\DateTimeImmutable::NOW, new \DateTimeZone( 'UTC' ) ); 
```

You may wish to implement `\Improv\DateTime\Factories\Interfaces\IDateTimeFactory` to return
an instance of PHP's core `DateTimeImmutable` object (as opposed to Improv's, which is included by default),
or any other implementation of the `\DateTimeInterface`.

## Examples

Below are some examples of how an `IDateTimeFactory` can be mocked to improve testing by inverting dependencies.

```php
namespace My\Interesting\Application;

class UserRepositoryDatabase implements IUserRepository {

    /**
     * @var IDatabase
     */
    private $db;

    public function __construct(IDatabase $db)
    {
        $this->db = $db;
    }

    public function add(IUser $user)
    {
        // Update DateCreated property
        $now = new \DateTimeImmutable();
        $user->setDateCreated($now);

        // Database operations
        $insert_params = [
            'date_created' => $now->getTimestamp(),
            /*...*/
        ];

        return $this->db->insert($insert_params);
    }
}
```

There is an implied functional requirement evident in the example above. Namely, that a User object
should receive a DateTime object reflecting the current system time at which the user is persisted. But how
can we test that this is actually the case?

```php
namespace My\Interesting\Application;

class UserRepositoryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function testUserCreate()
    {

        $datetime = new \DateTimeImmutable(); // Create a "now" to test with
        $user     = $this->getMock(IUser::class);
        $db       = $this->getMock(IDatabase::class);

        // Here, we assert that our object gets the above "now" associated with it
        $user->expects($this->once())
            ->method('setDateCreated')
            ->with($datetime);

        // Let's ensure that the DB gets the right parameters with our new timestamp
        $db->expects($this->once())
            ->method('insert')
            ->with([
                'date_created' => $datetime->getTimestamp()
                /*...*/
            ])
            ->will($this->returnValue($result_expected = 'abcdef') );

        $repository    = new UserRepositoryDatabase($db);

        // Act upon the System Under Test (SUT)
        $result_actual = $repository->add($user);

        // Assert the invocation returned the result of the DB call
        $this->assertEquals($result_expected, $result_actual);
    }
}
```

There are several issues with the above test.  For one, **this test may fail randomly**! This can be extremely
difficult to track down on a remote build slave in a continuous integration environment. The reason for this is
because we are ultimately trying to assert that two separately-created objects are equal in their value.  The fact
is, this is not a guarantee.

We are obtaining our first DateTime object as the first line of our test...  However, it is
not until the second-to-last line of our test that the actual invocation of our SUT is made, which ultimately
yields a call to our implementation of `add` being executed.  With this call, we create our second DateTime object
and set it in our User object before grabbing its timestamp for the DB.  We are using assertions to check to see whether
these two distinct objects represent the same value.  As they are separated by several lines, there is the distinct
possibility that they do not represent the same exact timestamp with enough precision to be considered "equal".
If the first object is created at `2015-10-05 23:59:59.998` and the second one at `2015-10-06 00:00:00.001`, then this
test will fail, and rightly so.

There are ways to mitigate this, such as moving the calls "closer" together in execution steps, or being less rigid
about the check for "equality", but neither of these are ideal for obvious reasons.

Another issue is that we are testing the actual implementation of the DateTimeImmutable's `getTimestamp` method, which
may be a trivial issue as it's a simple value-object, but it fundamentally undermines our effort to limit the scope of
this test case to one, single "unit" of coverage.

Finally, we are ultimately relying on the system time, which may be a serious limitation in some cases. Imagine that
we need to test the scenario in which the "current" time is *between* two different times extracted from
a database field.  Something like:

```php
class CouponProcessor
{
    public function couponIsValid(Coupon $coupon)
    {
        $start = $coupon->getDateActive()->getTimestamp();
        $end   = $coupon->getDateExpiration()->getTimestamp();
        $now   = ( new \DateTimeImmutable() )->getTimestamp();
    }
}
```

If the "current" time in our test is a dynamic "now", as per our above example, then this
representation of "now" will be different each time that the test is executed.
At some point, our tests fall out of date, as "now" is no longer "between" the values we're testing, as it exceeds the
upper bound and our test fails. The only way around this is to dynamically set the upper bound as part of the test suite
but, again, we then find ourselves in the business of testing a lot more than a single "unit" of functionality.

Since our system may rely on the value of time, it could be said that time is a dependency of our system and, thus,
should be injected just as we inject all other dependencies. This is where the Factory pattern can be of great assistance.

```php
namespace My\Interesting\Application;

use Improv\DateTime\Factories\Interfaces;

class UserRepositoryDatabase implements IUserRepository {

    /**
     * @var IDatabase
     */
    private $db;
    
    /**
     * @var IDateTimeFactory
     */
    private $factory_datetime;

    public function __construct(IDatabase $db, IDateTimeFactory $factory_datetime) {

        $this->db               = $db;
        $this->factory_datetime = $factory_datetime;

    }

    public function add(IUser $user) {

        // Update DateCreated property
        $now = new \DateTimeImmutable();
        $user->setDateCreated($now);

        // Database operations
        $insert_params = [
            'date_created' => $now->getTimestamp(),
            /*...*/
        ];

        return $this->db->insert($insert_params);

    }
}
```

There is an implied functional requirement evident in the example above. Namely, that a User object
should receive a DateTime object reflecting the current system time that the user is persisted. But how
can we test that this is actually the case?

```php
namespace My\Interesting\Application;

use Improv\DateTime\Factories\Interfaces;

class UserRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testUserCreate()
    {

        $now              = new \DateTimeImmutable(); // Create a "now" to test with
        $factory_datetime = $this->getMock(IDateTimeFactory::class);
        $user             = $this->getMock(IUser::class);
        $db               = $this->getMock(IDatabase::class);

        // Here, we assert that our object gets the above "now" associated with it
        $user->expects($this->once())
            ->method('setDateCreated')
            ->with($now);

        // Let's ensure that the DB gets the right parameters with our new timestamp
        $db->expects($this->once())
            ->method('insert')
            ->with([
                'date_created' => $now->getTimestamp()
                /*...*/
            ])
            ->will($this->returnValue($result_expected = 'abcdef') );

        $repository    = new UserRepositoryDatabase($db);

        // Act upon the System Under Test (SUT)
        $result_actual = $repository->add($user);

        // Assert the invocation returned the result of the DB call
        $this->assertEquals($result_expected, $result_actual);
    }
}
```

Now we have fine-grained control over the time that our system uses during testing. This allows us to more-easily test
all scenarios, including edge cases and failures.  With this, we can assert that our software is executing in precisely
the way that we desire.

## Known Issues

Currently, all "relative" string values like "+1 day" will not necessarily receive the exact microtime value as today's
"now" receives.  Support for this is anticipated, but not yet available.

For example:

```php
new \Improv\DateTime\DateTimeImmutable( 'now' );      // Output, e.g., 2015-10-05 19:57:28.581304
new \Improv\DateTime\DateTimeImmutable( '+1 day' );   // Output, e.g., 2015-10-06 19:57:28.000000
                                                      //   instead of, 2015-10-06 19:57:28.581304
```

## Additional Documentation

You may [run the API Doc build target](#buildtargets) to produce and peruse API documentation for this package.

## <a name="buildtest"></a>Running the Build/Test Suite

This package makes extensive use of the [Phing](https://www.phing.info/ "Click to Learn More") build tool.

Below is a list of notable build targets, but please feel free to peruse the `build.xml` file for more insight.

### Default Target

`./vendor/bin/phing` will execute the `build` target (the same as executing `./vendor/bin/phing build`).
This performs a linting, syntax check, runs all static analysis tools, the test suite, and produces API documentation.

### <a name="buildpackage"></a>"Full" Packaging Target

Executing `./vendor/bin/phing package` will run all above checks and, if passing, package the source into a shippable file
with only the relevant source included therein.

### <a name="buildtargets"></a>Selected Individual Targets
 
- Run the Tests
    - `./vendor/bin/phing test`
    - `./vendor/bin/phpunit`
- Perform Static Analysis
    - `./vendor/bin/phing static-analysis`
    - The generated reports are in `./build/output/reports`
- Produce API Documentation
    - `./vendor/bin/phing documentapi`
    - The generated documentation is in `./build/docs/api`
- Build Package from Source
    - `./vendor/bin/phing package`
    - The artifacts are in `./build/output/artifacts`

## Contributing

- Learn how to [run the test suite](#buildtest)
    - There are static analysis requirements, linting, PSR2-checks, etc, which will fail the build if not satisfied
    - There may be subjective reasons for rejection outside of the analysis (particularly if test coverage is lacking)
    - Please include appropriate `@covers` in all cases, and include any `@uses` when completely necessary 
    - Ensure no tests are marked as "Risky"
- Include thorough and thoughtful Docblocks in accordance with [PHPDocumenter](http://www.phpdoc.org/ "Click to Learn More")

[![License](https://poser.pugx.org/improvframework/datetime/license)](https://packagist.org/packages/improvframework/datetime)
[![Latest Stable Version](https://poser.pugx.org/improvframework/datetime/v/stable)](https://packagist.org/packages/improvframework/datetime)
[![Latest Unstable Version](https://poser.pugx.org/improvframework/datetime/v/unstable)](https://packagist.org/packages/improvframework/datetime)
[![Total Downloads](https://poser.pugx.org/improvframework/datetime/downloads)](https://packagist.org/packages/improvframework/datetime)