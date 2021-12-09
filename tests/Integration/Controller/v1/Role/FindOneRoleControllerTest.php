<?php
declare(strict_types = 1);
/**
 * /tests/Integration/Controller/v1/Role/FindOneRoleControllerTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */

namespace App\Tests\Integration\Controller\v1\Role;

use App\Controller\v1\Role\FindOneRoleController;
use App\Entity\Role;
use App\Resource\RoleResource;
use App\Rest\ResponseHandler;
use App\Utils\Tests\RestIntegrationControllerTestCase;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Class FindOneRoleControllerTest
 *
 * @package App\Tests\Integration\Controller\v1\Role
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 *
 * @method FindOneRoleController getController()
 */
class FindOneRoleControllerTest extends RestIntegrationControllerTestCase
{
    /**
     * @var class-string
     */
    protected string $controllerClass = FindOneRoleController::class;

    /**
     * @var class-string
     */
    protected string $resourceClass = RoleResource::class;

    /**
     * @throws Throwable
     *
     * @testdox Test that `__invoke($role)` method calls expected service methods
     */
    public function testThatInvokeMethodCallsExpectedMethods(): void
    {
        $resource = $this->getMockBuilder(RoleResource::class)->disableOriginalConstructor()->getMock();
        $responseHandler = $this->getMockBuilder(ResponseHandler::class)->disableOriginalConstructor()->getMock();

        $role = new Role('role');
        $request = Request::create('/');

        $resource
            ->expects(self::once())
            ->method('findOne')
            ->with('role', true)
            ->willReturn($role);

        $responseHandler
            ->expects(self::once())
            ->method('createResponse')
            ->with($request, $role, $resource);

        (new FindOneRoleController($resource))
            ->setResponseHandler($responseHandler)
            ->__invoke($request, 'role');
    }
}
