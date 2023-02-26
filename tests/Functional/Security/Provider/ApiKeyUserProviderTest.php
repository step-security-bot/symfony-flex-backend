<?php
declare(strict_types = 1);
/**
 * /tests/Functional/Security/Provider/ApiKeyUserProviderTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */

namespace App\Tests\Functional\Security\Provider;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use App\Security\ApiKeyUser;
use App\Security\Provider\ApiKeyUserProvider;
use App\Security\RolesService;
use App\Utils\Tests\StringableArrayObject;
use Doctrine\Persistence\ManagerRegistry;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Throwable;
use function array_map;
use function str_pad;

/**
 * Class ApiKeyUserProviderTest
 *
 * @package App\Tests\Functional\Security\Provider
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */
class ApiKeyUserProviderTest extends KernelTestCase
{
    /**
     * @testdox Test that `getApiKeyForToken` method returns expected when using `$shortRole` as token base.
     */
    #[DataProvider('dataProviderTestThatGetApiKeyReturnsExpected')]
    public function testThatGetApiKeyReturnsExpected(string $shortRole): void
    {
        $token = str_pad($shortRole, 40, '_');

        $apiKey = $this->getApiKeyUserProvider()->getApiKeyForToken($token);

        self::assertInstanceOf(ApiKey::class, $apiKey);
    }

    /**
     * @testdox Test that `getApiKeyForToken` method returns null when using `$shortRole` as an invalid token base.
     */
    #[DataProvider('dataProviderTestThatGetApiKeyReturnsExpected')]
    public function testThatGetApiKeyReturnsNullForInvalidToken(string $shortRole): void
    {
        $token = str_pad($shortRole, 40, '-');

        $apiKey = $this->getApiKeyUserProvider()->getApiKeyForToken($token);

        self::assertNull($apiKey);
    }

    /**
     * @throws Throwable
     */
    public function testThatLoadUserByIdentifierThrowsAnExceptionWithInvalidGuid(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('API key is not valid');

        $this->getApiKeyUserProvider()->loadUserByIdentifier((string)time());
    }

    /**
     *
     * @phpstan-param StringableArrayObject<array<int, string>> $roles
     * @psalm-param StringableArrayObject $roles
     *
     * @throws Throwable
     * @testdox Test that `loadUserByIdentifier` returns `ApiKeyUser` with `$roles` roles when using `$token` input
     */
    #[DataProvider('dataProviderTestThatLoadUserByIdentifierWorksAsExpected')]
    public function testThatLoadUserByIdentifierWorksAsExpected(string $token, StringableArrayObject $roles): void
    {
        $apiKeyUser = $this->getApiKeyUserProvider()->loadUserByIdentifier($token);

        self::assertInstanceOf(ApiKeyUser::class, $apiKeyUser);
        self::assertSame($roles->getArrayCopy(), $apiKeyUser->getRoles());
    }

    /**
     * @throws Throwable
     */
    public function testThatRefreshUserThrowsAnException(): void
    {
        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('API key cannot refresh user');

        $user = new InMemoryUser('username', 'password');

        $this->getApiKeyUserProvider()->refreshUser($user);
    }

    /**
     * @testdox Test that `supportsClass` returns `$expected` when using `$class` as an input.
     */
    #[DataProvider('dataProviderTestThatSupportsClassReturnsExpected')]
    public function testThatSupportsClassReturnsExpected(bool $expected, string $class): void
    {
        self::assertSame($expected, $this->getApiKeyUserProvider()->supportsClass($class));
    }

    /**
     * @return array<int, array{0: string}>
     */
    public static function dataProviderTestThatGetApiKeyReturnsExpected(): array
    {
        self::bootKernel();

        $rolesService = static::getContainer()->get(RolesService::class);

        self::assertInstanceOf(RolesService::class, $rolesService);

        $iterator = static fn (string $role): array => [$rolesService->getShort($role)];

        return array_map($iterator, $rolesService->getRoles());
    }

    /**
     * @psalm-return array<int, array{0: string, 1: StringableArrayObject}>
     * @phpstan-return array<int, array{0: string, 1: StringableArrayObject<array<int, string>>}>
     */
    public static function dataProviderTestThatLoadUserByIdentifierWorksAsExpected(): array
    {
        self::bootKernel();

        $managerRegistry = static::getContainer()->get('doctrine');
        $rolesService = static::getContainer()->get(RolesService::class);

        self::assertInstanceOf(ManagerRegistry::class, $managerRegistry);
        self::assertInstanceOf(RolesService::class, $rolesService);

        $repositoryClass = ApiKeyRepository::class;
        $repository = new $repositoryClass($managerRegistry);

        $iterator = static fn (ApiKey $apiKey): array => [
            $apiKey->getToken(),
            new StringableArrayObject(array_merge($rolesService->getInheritedRoles($apiKey->getRoles()))),
        ];

        return array_map($iterator, $repository->findAll());
    }

    /**
     * @return Generator<array{0: boolean, 1: class-string<\Symfony\Component\Security\Core\User\UserInterface>}>
     */
    public static function dataProviderTestThatSupportsClassReturnsExpected(): Generator
    {
        yield [false, InMemoryUser::class];
        yield [true, ApiKeyUser::class];
    }

    private function getApiKeyUserProvider(): ApiKeyUserProvider
    {
        self::bootKernel();

        $managerRegistry = static::getContainer()->get('doctrine');
        $rolesService = static::getContainer()->get(RolesService::class);
        $repository = ApiKeyRepository::class;

        self::assertInstanceOf(ManagerRegistry::class, $managerRegistry);
        self::assertInstanceOf(RolesService::class, $rolesService);

        return new ApiKeyUserProvider(new $repository($managerRegistry), $rolesService);
    }
}
