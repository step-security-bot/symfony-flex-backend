<?php
declare(strict_types = 1);
/**
 * /tests/Integration/Controller/v1/User/AttachUserGroupControllerTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */

namespace App\Tests\Integration\Controller\v1\User;

use App\Controller\v1\User\AttachUserGroupController;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Resource\UserGroupResource;
use App\Resource\UserResource;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

/**
 * Class AttachUserGroupControllerTest
 *
 * @package App\Tests\Integration\Controller\v1\User
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */
class AttachUserGroupControllerTest extends KernelTestCase
{
    /**
     * @throws Throwable
     *
     * @testdox Test that `__invoke($user, $userGroup)` method calls expected service methods
     */
    public function testThatInvokeMethodCallsExpectedMethods(): void
    {
        $userResource = $this->getMockBuilder(UserResource::class)->disableOriginalConstructor()->getMock();
        $userGroupResource = $this->getMockBuilder(UserGroupResource::class)->disableOriginalConstructor()->getMock();
        $serializer = $this->getMockBuilder(SerializerInterface::class)->getMock();

        $user = new User();
        $userGroup = (new UserGroup())->setRole(new Role('role'));

        $userResource
            ->expects(self::once())
            ->method('save')
            ->with($user, false)
            ->willReturn($user);

        $userGroupResource
            ->expects(self::once())
            ->method('save')
            ->with($userGroup, true, true)
            ->willReturn($userGroup);

        $serializer
            ->expects(self::once())
            ->method('serialize')
            ->willReturn('[]');

        (new AttachUserGroupController($userResource, $userGroupResource, $serializer))($user, $userGroup);
    }
}
