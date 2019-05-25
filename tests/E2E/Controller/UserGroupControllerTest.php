<?php
declare(strict_types = 1);
/**
 * /tests/E2E/Controller/UserGroupControllerTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Tests\E2E\Controller;

use App\Resource\UserGroupResource;
use App\Resource\UserResource;
use App\Utils\JSON;
use App\Utils\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function sprintf;

/**
 * Class UserGroupControllerTest
 *
 * @package App\Tests\E2E\Controller
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class UserGroupControllerTest extends WebTestCase
{
    private $baseUrl = '/user_group';

    /**
     * @throws Throwable
     */
    public function testThatGetBaseRouteReturn403(): void
    {
        $client = $this->getClient();
        $client->request('GET', $this->baseUrl);

        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);

        /** @noinspection NullPointerExceptionInspection */
        static::assertSame(401, $response->getStatusCode(), "Response:\n" . $response);

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderTestThatGetUserGroupUsersActionReturnsExpected
     *
     * @param int    $userCount
     * @param string $userGroupId
     *
     * @throws Throwable
     */
    public function testThatGetUserGroupUsersActionReturnsExpected(int $userCount, string $userGroupId): void
    {
        $client = $this->getClient('john-root', 'password-root');
        $client->request('GET', $this->baseUrl . '/' . $userGroupId . '/users');

        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);

        /** @noinspection NullPointerExceptionInspection */
        static::assertSame(200, $response->getStatusCode(), $response->getContent() . "\nResponse:\n" . $response);

        /** @noinspection NullPointerExceptionInspection */
        static::assertCount($userCount, JSON::decode($response->getContent()));

        unset($response, $client);
    }

    /**
     * @dataProvider dataProviderTestThatAttachUserActionReturns403ForInvalidUser
     *
     * @param string $username
     * @param string $password
     *
     * @throws Throwable
     */
    public function testThatAttachUserActionReturns403ForInvalidUser(string $username, string $password): void
    {
        /** @var UserGroupResource $userGroupResource */
        $userGroupResource = static::$container->get(UserGroupResource::class);

        /** @var UserResource $userResource */
        $userResource = static::$container->get(UserResource::class);

        $user = $userResource->findOneBy(['username' => $username]);
        $userGroup = $userGroupResource->findOneBy(['name' => 'Root users']);

        /** @noinspection NullPointerExceptionInspection */
        $url = sprintf(
            '%s/%s/user/%s',
            $this->baseUrl,
            $userGroup->getId(),
            $user->getId()
        );

        $client = $this->getClient($username, $password);
        $client->request('POST', $url);

        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);

        /** @noinspection NullPointerExceptionInspection */
        static::assertSame(403, $response->getStatusCode(), "Response:\n" . $response);

        /** @noinspection NullPointerExceptionInspection */
        static::assertJsonStringEqualsJsonString(
            '{"message":"Access denied.","code":0,"status":403}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client, $userGroup, $user, $userResource, $userGroupResource);
    }

    /**
     * @dataProvider dataProviderTestThatAttachUserActionWorksAsExpected
     *
     * @param int $expectedStatus
     *
     * @throws Throwable
     */
    public function testThatAttachUserActionWorksAsExpected(int $expectedStatus): void
    {
        /** @var UserGroupResource $userGroupResource */
        $userGroupResource = static::$container->get(UserGroupResource::class);

        /** @var UserResource $userResource */
        $userResource = static::$container->get(UserResource::class);

        $user = $userResource->findOneBy(['username' => 'john']);
        $userGroup = $userGroupResource->findOneBy(['name' => 'Root users']);

        /** @noinspection NullPointerExceptionInspection */
        $url = sprintf(
            '%s/%s/user/%s',
            $this->baseUrl,
            $userGroup->getId(),
            $user->getId()
        );

        $client = $this->getClient('john-root', 'password-root');
        $client->request('POST', $url);

        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);

        /** @noinspection NullPointerExceptionInspection */
        static::assertSame($expectedStatus, $response->getStatusCode(), "Response:\n" . $response);

        /** @noinspection NullPointerExceptionInspection */
        static::assertCount(2, JSON::decode($response->getContent()));

        unset($response, $client, $userGroup, $user, $userResource, $userGroupResource);
    }

    /**
     * @depends testThatAttachUserActionWorksAsExpected
     * @throws Throwable
     */
    public function testThatDetachUserActionWorksAsExpected(): void
    {
        /** @var UserGroupResource $userGroupResource */
        $userGroupResource = static::$container->get(UserGroupResource::class);

        /** @var UserResource $userResource */
        $userResource = static::$container->get(UserResource::class);

        $user = $userResource->findOneBy(['username' => 'john']);
        $userGroup = $userGroupResource->findOneBy(['name' => 'Root users']);

        /** @noinspection NullPointerExceptionInspection */
        $url = sprintf(
            '%s/%s/user/%s',
            $this->baseUrl,
            $userGroup->getId(),
            $user->getId()
        );

        $client = $this->getClient('john-root', 'password-root');
        $client->request('DELETE', $url);

        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);

        /** @noinspection NullPointerExceptionInspection */
        static::assertSame(200, $response->getStatusCode(), "Response:\n" . $response);

        /** @noinspection NullPointerExceptionInspection */
        static::assertCount(1, JSON::decode($response->getContent()));

        unset($response, $client, $userGroup, $user, $userResource, $userGroupResource);
    }

    /**
     * @depends      testThatDetachUserActionWorksAsExpected
     *
     * @dataProvider dataProviderTestThatDetachUserActionReturns403ForInvalidUser
     *
     * @param string $username
     * @param string $password
     *
     * @throws Throwable
     */
    public function testThatDetachUserActionReturns403ForInvalidUser(string $username, string $password): void
    {
        /** @var UserGroupResource $userGroupResource */
        $userGroupResource = static::$container->get(UserGroupResource::class);

        /** @var UserResource $userResource */
        $userResource = static::$container->get(UserResource::class);

        $user = $userResource->findOneBy(['username' => $username]);
        $userGroup = $userGroupResource->findOneBy(['name' => 'Root users']);

        /** @noinspection NullPointerExceptionInspection */
        $url = sprintf(
            '%s/%s/user/%s',
            $this->baseUrl,
            $userGroup->getId(),
            $user->getId()
        );

        $client = $this->getClient($username, $password);
        $client->request('DELETE', $url);

        $response = $client->getResponse();

        static::assertInstanceOf(Response::class, $response);

        /** @noinspection NullPointerExceptionInspection */
        static::assertSame(403, $response->getStatusCode(), "Response:\n" . $response);

        /** @noinspection NullPointerExceptionInspection */
        static::assertJsonStringEqualsJsonString(
            '{"message":"Access denied.","code":0,"status":403}',
            $response->getContent(),
            "Response:\n" . $response
        );

        unset($response, $client, $userGroup, $user, $userResource, $userGroupResource);
    }

    /**
     * @return array
     */
    public function dataProviderTestThatGetUserGroupUsersActionReturnsExpected(): array
    {
        static::bootKernel();

        /** @var UserGroupResource $userGroupResource */
        $userGroupResource = static::$container->get(UserGroupResource::class);

        /** @noinspection NullPointerExceptionInspection */
        return [
            [1, $userGroupResource->findOneBy(['name' => 'Root users'])->getId()],
            [2, $userGroupResource->findOneBy(['name' => 'Admin users'])->getId()],
            [3, $userGroupResource->findOneBy(['name' => 'Normal users'])->getId()],
            [1, $userGroupResource->findOneBy(['name' => 'Api users'])->getId()],
            [5, $userGroupResource->findOneBy(['name' => 'Logged in users'])->getId()],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderTestThatAttachUserActionReturns403ForInvalidUser(): array
    {
        return [
            ['john',        'password'],
            ['john-api',    'password-api'],
            ['john-logged', 'password-logged'],
            ['john-user',   'password-user'],
            ['john-admin',  'password-admin'],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderTestThatAttachUserActionWorksAsExpected(): array
    {
        return [
            [201],
            [200],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderTestThatDetachUserActionReturns403ForInvalidUser(): array
    {
        return $this->dataProviderTestThatAttachUserActionReturns403ForInvalidUser();
    }
}
