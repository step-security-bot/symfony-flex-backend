<?php
declare(strict_types = 1);
/**
 * /src/Validator/Constraints/TimezoneValidatorTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */

namespace App\Tests\Integration\Validator\Constraints;

use App\Entity\User;
use App\Service\Localization;
use App\Validator\Constraints\Timezone;
use App\Validator\Constraints\TimezoneValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * Class TimezoneValidatorTest
 *
 * @package App\Tests\Integration\Validator\Constraints
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */
class TimezoneValidatorTest extends KernelTestCase
{
    /**
     * @testdox Test that `TimezoneValidator::validate` method calls expected service methods
     */
    public function testThatValidateCallsExpectedMethods(): void
    {
        $localizationMock = $this->getMockBuilder(Localization::class)->disableOriginalConstructor()->getMock();
        $contextMock = $this->getMockBuilder(ExecutionContext::class)->disableOriginalConstructor()->getMock();
        $builderMock = $this->getMockBuilder(ConstraintViolationBuilderInterface::class)->getMock();

        // Create new user
        $user = (new User())
            ->setTimezone('foo/bar');

        $localizationMock
            ->expects(static::once())
            ->method('getTimezones')
            ->willReturn(['bar/foo']);

        $contextMock
            ->expects(static::once())
            ->method('buildViolation')
            ->with(Timezone::MESSAGE)
            ->willReturn($builderMock);

        $builderMock
            ->expects(static::once())
            ->method('setParameter')
            ->willReturn($builderMock);

        $builderMock
            ->expects(static::once())
            ->method('setCode')
            ->with(Timezone::INVALID_TIMEZONE)
            ->willReturn($builderMock);

        $builderMock
            ->expects(static::once())
            ->method('addViolation');

        // Run validator
        $validator = new TimezoneValidator($localizationMock);
        $validator->initialize($contextMock);
        $validator->validate($user, new Timezone());
    }
}
