<?php
declare(strict_types = 1);
/**
 * /tests/Integration/TestCase/ResourceTestCase.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */

namespace App\Tests\Integration\TestCase;

use App\Entity\Interfaces\EntityInterface;
use App\Repository\BaseRepository;
use App\Rest\Interfaces\RestResourceInterface;
use App\Rest\RestResource;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Throwable;
use function sprintf;

/**
 * Class ResourceTestCase
 *
 * @package App\Tests\Integration\TestCase
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@pinja.com>
 */
abstract class ResourceTestCase extends KernelTestCase
{
    /**
     * @var class-string<EntityInterface>
     */
    protected string $entityClass;

    /**
     * @var class-string<BaseRepository>
     */
    protected string $repositoryClass;

    /**
     * @var class-string<RestResource>
     */
    protected string $resourceClass;

    /**
     * @throws Throwable
     */
    public function testThatGetRepositoryReturnsExpected(): void
    {
        $message = sprintf(
            'getRepository() method did not return expected repository \'%s\'.',
            $this->repositoryClass
        );

        /** @noinspection UnnecessaryAssertionInspection */
        self::assertInstanceOf($this->repositoryClass, $this->getResource()->getRepository(), $message);
    }

    /**
     * @throws Throwable
     */
    public function testThatGetEntityNameReturnsExpected(): void
    {
        $message = sprintf(
            'getEntityName() method did not return expected entity \'%s\'.',
            $this->entityClass
        );

        self::assertSame($this->entityClass, $this->getResource()->getEntityName(), $message);
    }

    private function getResource(): RestResourceInterface
    {
        /** @var RestResourceInterface $resource */
        $resource = self::getContainer()->get($this->resourceClass);

        return $resource;
    }
}
