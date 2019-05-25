<?php
declare(strict_types = 1);
/**
 * /tests/Integration/Resource/GenericResourceTest.php
 *
 * @author TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Tests\Integration\Resource;

use App\DTO\RestDtoInterface;
use App\DTO\User as UserDto;
use App\Entity\EntityInterface;
use App\Entity\User as UserEntity;
use App\Repository\UserRepository;
use App\Resource\UserResource;
use App\Rest\RepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Generator;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use function get_class;

/**
 * Class GenericResourceTest
 *
 * @package App\Tests\Integration\Resource
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class GenericResourceTest extends KernelTestCase
{
    private $dtoClass = UserDto::class;
    private $resourceClass = UserResource::class;
    private $entityClass = UserEntity::class;

    /**
     * @var UserResource
     */
    private $resource;

    /**
     * @return EntityManagerInterface|Object
     */
    private static function getEntityManager(): EntityManagerInterface
    {
        return static::$container->get('doctrine')->getManager();
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp /DTO class not specified for '.*' resource/
     */
    public function testThatGetDtoClassThrowsAnExceptionWithoutDto(): void
    {
        $this->resource->setDtoClass('');
        $this->resource->getDtoClass();
    }

    public function testThatGetDtoClassReturnsExpectedDto(): void
    {
        $this->resource->setDtoClass('foobar');

        static::assertSame('foobar', $this->resource->getDtoClass());
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp /FormType class not specified for '.*' resource/
     */
    public function testGetFormTypeClassThrowsAnExceptionWithoutFormType(): void
    {
        $this->resource->setFormTypeClass('');
        $this->resource->getFormTypeClass();
    }

    public function testThatGetFormTypeClassReturnsExpectedDto(): void
    {
        $this->resource->setFormTypeClass('foobar');

        static::assertSame('foobar', $this->resource->getFormTypeClass());
    }

    public function testThatGetEntityNameCallsExpectedRepositoryMethod(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('getEntityName');

        $this->resource->setRepository($repository);
        $this->resource->getEntityName();

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatGetReferenceCallsExpectedRepositoryMethod(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('getReference');

        $this->resource->setRepository($repository);
        $this->resource->getReference('some id');

        unset($repository);
    }

    public function testThatGetAssociationsCallsExpectedRepositoryMethod(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('getAssociations');

        $this->resource->setRepository($repository);
        $this->resource->getAssociations();

        unset($repository);
    }

    public function testThatGetDtoForEntityCallsExpectedRepositoryMethod(): void
    {
        $entity = $this->getEntityMock();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn($entity);

        /** @var MockObject|RestDtoInterface $dto */
        $dto = $this->getDtoMockBuilder()->getMock();

        $this->resource->setRepository($repository);

        /** @noinspection UnnecessaryAssertionInspection */
        static::assertInstanceOf(
            RestDtoInterface::class,
            $this->resource->getDtoForEntity('some id', get_class($dto))
        );

        unset($dto, $repository, $entity);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not found
     */
    public function testThatGetDtoForEntityThrowsAnExceptionIfEntityWasNotFound(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn(null);

        /** @var MockObject|RestDtoInterface $dto */
        $dto = $this->getDtoMockBuilder()->getMock();

        $this->resource->setRepository($repository);
        $this->resource->getDtoForEntity('some id', get_class($dto));

        unset($repository);
    }

    /**
     * @dataProvider dataProviderTestThatFindCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     *
     * @throws Throwable
     */
    public function testThatFindCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findByAdvanced')
            ->with(...$expectedArguments);

        $this->resource->setRepository($repository);
        $this->resource->find(...$arguments);

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatFindOneCallsExpectedRepositoryMethod(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findAdvanced')
            ->withAnyParameters();

        $this->resource->setRepository($repository);
        $this->resource->findOne('some id');

        unset($repository);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not found
     *
     * @throws Throwable
     */
    public function testThatFindOneThrowsAnExceptionIfEntityWasNotFound(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findAdvanced')
            ->withAnyParameters()
            ->willReturn(null);

        $this->resource->setRepository($repository);
        $this->resource->findOne('some id', true);

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatFindOneWontThrowAnExceptionIfEntityWasFound(): void
    {
        $entity = $this->getEntityMock();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findAdvanced')
            ->withAnyParameters()
            ->willReturn($entity);

        $this->resource->setRepository($repository);

        static::assertSame($entity, $this->resource->findOne('some id', true));

        unset($repository, $entity);
    }

    /**
     * @dataProvider dataProviderTestThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     *
     * @throws Throwable
     */
    public function testThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->with(...$expectedArguments);

        $this->resource->setRepository($repository);
        $this->resource->findOneBy(...$arguments);

        unset($repository);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not found
     *
     * @throws Throwable
     */
    public function testThatFindOneByThrowsAnExceptionIfEntityWasNotFound(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->withAnyParameters()
            ->willReturn(null);

        $this->resource->setRepository($repository);
        $this->resource->findOneBy([], null, true);

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatFindOneByWontThrowAnExceptionIfEntityWasFound(): void
    {
        $entity = $this->getEntityMock();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findOneBy')
            ->withAnyParameters()
            ->willReturn($entity);

        $this->resource->setRepository($repository);

        static::assertSame($entity, $this->resource->findOneBy([], null, true));

        unset($repository, $entity);
    }

    /**
     * @dataProvider dataProviderTestThatCountCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     *
     * @throws Throwable
     */
    public function testThatCountCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('countAdvanced')
            ->with(...$expectedArguments);

        $this->resource->setRepository($repository);
        $this->resource->count(...$arguments);

        unset($repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatSaveMethodCallsExpectedRepositoryMethod(): void
    {
        $entity = $this->getEntityInterfaceMock();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('save')
            ->with($entity);

        $this->resource->setRepository($repository);

        static::assertSame($entity, $this->resource->save($entity));

        unset($repository, $entity);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     *
     * @throws Throwable
     */
    public function testThatCreateMethodThrowsAnErrorWithInvalidDto(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $dto = new $this->dtoClass();

        $this->resource->setRepository($repository);
        $this->resource->create($dto);

        unset($dto, $repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatCreateMethodCallsExpectedMethods(): void
    {
        /** @var MockObject|UserRepository|RepositoryInterface $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('getEntityName')
            ->willReturn($this->entityClass);

        $repository
            ->expects(static::once())
            ->method('save');

        /** @var MockObject|ValidatorInterface $validator */
        $validator = $this->getMockBuilder(ValidatorInterface::class)->getMock();

        /** @var MockObject|UserRepository|ConstraintViolationListInterface $repository */
        $constraintViolationList = $this->getMockBuilder(ConstraintViolationListInterface::class)->getMock();

        $constraintViolationList
            ->expects(static::exactly(2))
            ->method('count')
            ->willReturn(0);

        $validator
            ->expects(static::exactly(2))
            ->method('validate')
            ->willReturn($constraintViolationList);

        /** @var MockObject|RestDtoInterface $dto */
        $dto = $this->getDtoMockBuilder()->getMock();

        $dto
            ->expects(static::once())
            ->method('update');

        $this->resource->setRepository($repository);
        $this->resource->setValidator($validator);
        $this->resource->create($dto);

        unset($dto, $validator, $repository);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not found
     *
     * @throws Throwable
     */
    public function testThatUpdateMethodThrowsAnExceptionIfEntityWasNotFound(): void
    {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn(null);

        $dto = new $this->dtoClass();

        $this->resource->setRepository($repository);
        $this->resource->update('some id', $dto);

        unset($dto, $repository);
    }

    /**
     * @throws Throwable
     */
    public function testThatUpdateCallsExpectedRepositoryMethod(): void
    {
        $dto = new $this->dtoClass();
        $entity = new $this->entityClass();

        $methods = [
            'setUsername'   => 'username',
            'setFirstname'  => 'firstname',
            'setSurname'    => 'surname',
            'setEmail'      => 'test@test.com',
        ];

        foreach ($methods as $method => $value) {
            $dto->$method($value);
            $entity->$method($value);
        }

        /** @var MockObject|UserRepository|RepositoryInterface $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::exactly(2))
            ->method('find')
            ->with('some id')
            ->willReturn($entity);

        $repository
            ->expects(static::once())
            ->method('save')
            ->with($entity);

        $this->resource->setRepository($repository);
        $this->resource->update('some id', $dto);

        unset($repository, $entity, $dto);
    }

    /**
     * @throws Throwable
     */
    public function testThatDeleteMethodCallsExpectedRepositoryMethod(): void
    {
        $entity = $this->getEntityMock();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('find')
            ->with('some id')
            ->willReturn($entity);

        $repository
            ->expects(static::once())
            ->method('remove')
            ->with($entity);

        $this->resource->setRepository($repository);

        static::assertSame($entity, $this->resource->delete('some id'));

        unset($repository, $entity);
    }

    /**
     * @dataProvider dataProviderTestThatGetIdsCallsExpectedRepositoryMethodWithCorrectParameters
     *
     * @param array $expectedArguments
     * @param array $arguments
     *
     * @throws Throwable
     */
    public function testThatGetIdsCallsExpectedRepositoryMethodWithCorrectParameters(
        array $expectedArguments,
        array $arguments
    ): void {
        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::once())
            ->method('findIds')
            ->with(...$expectedArguments);

        $this->resource->setRepository($repository);
        $this->resource->getIds(...$arguments);

        unset($repository);
    }

    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    /**
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     *
     * @throws Throwable
     */
    public function testThatSaveMethodThrowsAnExceptionWithInvalidEntity(): void
    {
        $entity = new $this->entityClass();

        /** @var MockObject|UserRepository $repository */
        $repository = $this->getRepositoryMockBuilder()->disableOriginalConstructor()->getMock();

        $repository
            ->expects(static::never())
            ->method('save')
            ->with($entity);

        $this->resource->setRepository($repository);
        $this->resource->save($entity);

        unset($repository, $entity);
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatCountCallsExpectedRepositoryMethodWithCorrectParameters(): Generator
    {
        yield [
            [[], []],
            [null, null],
        ];

        yield [
            [['foo'], []],
            [['foo'], null],
        ];

        yield [
            [['foo'], ['bar']],
            [['foo'], ['bar']],
        ];
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatFindCallsExpectedRepositoryMethodWithCorrectParameters(): Generator
    {
        yield [
            [[], [], 0, 0, []],
            [null, null, null, null, null],
        ];

        yield [
            [['foo'], [], 0, 0, []],
            [['foo'], null, null, null, null],
        ];

        yield [
            [['foo'], ['foo'], 0, 0, []],
            [['foo'], ['foo'], null, null, null],
        ];

        yield [
            [['foo'], ['foo'], 1, 0, []],
            [['foo'], ['foo'], 1, null, null],
        ];

        yield [
            [['foo'], ['foo'], 1, 2, []],
            [['foo'], ['foo'], 1, 2, null],
        ];

        yield [
            [['foo'], ['foo'], 1, 2, ['foo']],
            [['foo'], ['foo'], 1, 2, ['foo']],
        ];
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatFindOneByCallsExpectedRepositoryMethodWithCorrectParameters(): Generator
    {
        yield [
            [[], []],
            [[], null],
        ];

        yield [
            [['foo'], []],
            [['foo'], null],
        ];

        yield [
            [['foo'], ['bar']],
            [['foo'], ['bar']],
        ];
    }

    /**
     * @return Generator
     */
    public function dataProviderTestThatGetIdsCallsExpectedRepositoryMethodWithCorrectParameters(): Generator
    {
        yield [
            [[], []],
            [null, null],
        ];

        yield [
            [['foo'], []],
            [['foo'], null],
        ];

        yield [
            [['foo'], ['bar']],
            [['foo'], ['bar']],
        ];
    }

    protected function setUp(): void
    {
        gc_enable();

        parent::setUp();

        static::bootKernel();

        $this->resource = static::$container->get($this->resourceClass);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->resource);

        gc_collect_cycles();
    }

    /**
     * @return MockBuilder
     */
    private function getRepositoryMockBuilder(): MockBuilder
    {
        return $this
            ->getMockBuilder(UserRepository::class)
            ->setConstructorArgs([static::getEntityManager(), new ClassMetadata($this->entityClass)]);
    }

    /**
     * @return MockObject|EntityInterface
     */
    private function getEntityInterfaceMock(): MockObject
    {
        return $this
            ->getMockBuilder(EntityInterface::class)
            ->getMock();
    }

    /**
     * @return MockObject|UserEntity
     */
    private function getEntityMock(): MockObject
    {
        return $this->createMock($this->entityClass);
    }

    /**
     * @return MockBuilder
     */
    private function getDtoMockBuilder(): MockBuilder
    {
        return $this->getMockBuilder($this->dtoClass);
    }
}
