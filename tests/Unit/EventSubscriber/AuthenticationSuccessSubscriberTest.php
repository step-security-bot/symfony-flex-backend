<?php
declare(strict_types = 1);
/**
 * /tests/Unit/EventSubscriber/AuthenticationSuccessSubscriberTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\AuthenticationSuccessSubscriber;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Class AuthenticationSuccessSubscriberTest
 *
 * @package App\Tests\Unit\EventSubscriber
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class AuthenticationSuccessSubscriberTest extends KernelTestCase
{
    public function testThatGetSubscribedEventsReturnsExpected(): void
    {
        $expected = [
            'lexik_jwt_authentication.on_authentication_success' => 'onAuthenticationSuccess',
        ];

        static::assertSame($expected, AuthenticationSuccessSubscriber::getSubscribedEvents());
    }
}
