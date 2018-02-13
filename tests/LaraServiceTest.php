<?php

namespace Tests;

use LaraService\Services\LaraService;
use LaraRepo\Contracts\RepositoryInterface;
use LaraRepo\Criteria\Where\WhereCriteria;
use LaraTest\Traits\AccessProtectedTraits;
use LaraTest\Traits\MockTraits;
use LaraValidation\LaraValidator;
use phpmock\MockBuilder;

class LaraServiceTest extends \TestCase
{
    use MockTraits, AccessProtectedTraits;

    /**
     * @var
     */
    protected $repository;

    /**
     * @var
     */
    protected $validator;

    /**
     * @var
     */
    protected $service;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->validator = $this->getMockValidator(LaraValidator::class, ['isValid', 'getErrors']);
        $this->repository = $this->getMockForAbstractClass(
            RepositoryInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['pushCriteria']
        );
        $this->service = $this->getMockLaraService(['validate', 'getRelationForSaveAssociated']);
        $this->service->setBaseRepository($this->repository);
        $this->service->setBaseValidator($this->validator);
    }

    /**
     *
     */
    public function testSetRepository()
    {
        $this->service->setBaseRepository($this->repository);
        $this->assertEquals($this->repository, $this->service->getBaseRepository());
    }

    /**
     *
     */
    public function testGetBaseRepository()
    {
        $this->service->setBaseRepository($this->repository);
        $this->assertEquals($this->repository, $this->service->getBaseRepository());
    }

    /**
     *
     */
    public function testSetBaseValidator()
    {
        $this->service->setBaseValidator($this->validator);
        $this->assertEquals($this->validator, $this->service->getBaseValidator());
    }

    /**
     *
     */
    public function testGetBaseValidator ()
    {
        $this->service->setBaseValidator($this->validator);
        $this->assertEquals($this->validator, $this->service->getBaseValidator());
    }

    /**
     *
     */
    public function testSetValidationErrors()
    {
        $error = 'error';
        $this->service->setValidationErrors($error);
        $this->assertEquals($error, $this->service->getValidationErrors());
    }

    /**
     *
     */
    public function testGetValidationErrors()
    {
        $error = 'error';
        $this->service->setValidationErrors($error);
    }

    /**
     *
     */
    public function testPaginateWhenSortIsNotEmpty()
    {
        $sort = ['id' => 'asc'];
        $this->methodWillThrowException('pushCriteria', $this->repository);
        $this->expectException(\Exception::class);
        $this->service->paginate($sort);
    }

    /**
     *
     */
    public function testPaginateWhenSortIsEmpty()
    {
        $service = $this->getMockLaraService(['paginateRepository']);
        $this->methodWillReturnTrue('paginateRepository', $service);
        $this->assertTrue($service->paginate());
    }

    /**
     *
     */
    public function testCreate()
    {
        $service = $this->getMockLaraService(['createWithRelations']);
        $this->methodWillReturnTrue('createWithRelations', $service);
        $this->assertTrue($service->create([]));
    }

    /**
     *
     */
    public function testCreateWithRelationsWhenValidateIsFalse()
    {
        $this->assertFalse($this->service->createWithRelations([]));
    }

    /**
     *
     */
    public function testCreateWithRelationsWhenValidateIsTrue()
    {
        $this->methodWillReturnTrue('validate', $this->service);
        $this->methodWillReturnArguments('getRelationForSaveAssociated', $this->service);
        $this->methodWillReturnArguments('saveAssociated', $this->repository);

        $relations = ['associated' => 'categories'];
        $data = ['id' => 12];
        $expected = [$data, [$relations], null];
        $this->assertEquals($expected, $this->service->createWithRelations($data, $relations));
    }

    /**
     *
     */
    public function testFindForShow()
    {
        $this->methodWillReturnTrue('findForShow', $this->repository);
        $this->assertTrue($this->service->findForShow(1));
    }

    /**
     *
     */
    public function testFind()
    {
        $this->methodWillReturnTrue('findFillable', $this->repository);
        $this->assertTrue($this->service->find(1));
    }

    /**
     *
     */
    public function testUpdate()
    {
        $service = $this->getMockLaraService(['updateWithRelations']);
        $this->methodWillReturnTrue('updateWithRelations', $service);
        $this->assertTrue($service->update(1, []));
    }

    /**
     *
     */
    public function testUpdateWithRelationsWhenValidateIsFalse()
    {
        $this->methodWillReturn('id', 'getKeyName', $this->repository);
        $this->assertFalse($this->service->updateWithRelations(1, [], []));
    }

    /**
     *
     */
    public function testUpdateWithRelationsWhenValidateIsTrue_WhenFindFillableIsFalse()
    {
        $this->methodWillReturn('id', 'getKeyName', $this->repository);
        $this->methodWillReturnTrue('validate', $this->service);
        $this->assertFalse($this->service->updateWithRelations(1, [], []));
    }

    /**
     *
     */
    public function testUpdateWithRelationsWhenValidateIsTrue_WhenFindFillableIsTrue()
    {
        $this->methodWillReturn('id', 'getKeyName', $this->repository);
        $this->methodWillReturnTrue('validate', $this->service);
        $this->methodWillReturnArguments('findFillable', $this->repository);
        $this->methodWillReturnArguments('saveAssociated', $this->repository);
        $this->methodWillReturnArguments('getRelationForSaveAssociated', $this->service);

        $id = 1;
        $data = ['name' => 'name'];
        $relations = ['associated' => 'categories'];
        $expected = [
            array_merge($data, ['id' => $id]),
            [$relations],
            [$id]
        ];
        $this->assertEquals($expected, $this->service->updateWithRelations(1, $data, $relations));
    }

    /**
     *
     */
    public function testDestroy()
    {
        $this->methodWillReturnTrue('destroy', $this->repository);
        $this->assertTrue($this->service->destroy(1));
    }

    /**
     *
     */
    public function testValidate_WhenIsValidIsFalse()
    {
        $service = new LaraService();
        $this->methodWillReturnTrue('getErrors', $this->validator);
        $this->assertFalse($service->validate($this->validator, []));
        $this->assertTrue($service->getValidationErrors());
    }

    /**
     *
     */
    public function testValidate_WhenIsValidIsTrue()
    {
        $service = new LaraService();
        $this->methodWillReturnTrue('isValid', $this->validator);
        $this->assertTrue($service->validate($this->validator, []));
    }

    /**
     *
     */
    public function testPaginateRepositoryWhere_WhenIsNotEmpty_Column_Val()
    {
        $service = $this->getMockLaraService(['paginateRepository']);
        $this->methodWillThrowExceptionWithArgument('pushCriteria', $this->repository);

        $attribute = 'name';
        $value = 'value';
        $message = $this->getExceptionArgumentsMessage([new WhereCriteria($attribute, $value)]);
        $this->expectExceptionMessage($message);
        $service->paginateRepositoryWhere($this->repository, 'list', $attribute, $value);
    }

    /**
     *
     */
    public function testPaginateRepositoryWhere_WhenIsNotEmpty_Column_Val_CheckReturn()
    {
        $service = $this->getMockLaraService(['paginateRepository']);
        $this->methodWillReturnTrue('paginateRepository', $service);
        $attribute = 'name';
        $value = 'value';
        $this->assertTrue($service->paginateRepositoryWhere($this->repository, 'list', $attribute, $value));
    }

    /**
     *
     */
    public function testPaginateRepositoryWhere_WhenEmpty_Column_OR_Val()
    {
        $service = $this->getMockLaraService(['paginateRepository']);
        $this->methodWillReturnTrue('paginateRepository', $service);
        $this->assertTrue($service->paginateRepositoryWhere($this->repository));
    }

    /**
     *
     */
    public function testPaginateRepository()
    {
        $this->methodWillReturnArguments('getIndexableColumns', $this->repository);
        $this->methodWillReturnArguments('paginate', $this->repository);

       $expected = [
           [
               20,
               [],
               'list'
           ],
           [
               true,
               false,
               'list'
           ],
       ];

       $this->assertEquals($expected, $this->service->paginateRepository($this->repository));
    }

    /**
     *
     */
    public function testPaginateRepositorySetSortingOptions()
    {
        $service = $this->getMockLaraService(['setSortingOptions']);
        $this->methodWillThrowExceptionWithArgument('setSortingOptions', $service);
        $message = $this->getExceptionArgumentsMessage([$this->repository, [], 'list']);
        $this->expectExceptionMessage($message);
        $service->paginateRepositoryWhere($this->repository, 'list');
    }

    /**
     *
     */
    public function testSetSortingOptionsWhenOptionsIsEmpty()
    {
        $mock = new MockBuilder();
        $mock->setNamespace('LaraService\Services');
        $mock->setName('app');

        $options = [
            'column' => 'column',
            'order' => 'order'
        ];

        $mock->setFunction(
            function () use ($options) {
                $request = $this->getMockBuilder(\stdClass::class)->setMethods(['all'])->getMock();
                $this->methodWillReturn($options, 'all', $request);
                $object = new \stdClass();
                $object->request = $request;
                return $object;
            }
        );

        $mock = $mock->build();
        $mock->enable();

        $service = new LaraService();
        $this->methodWillThrowExceptionWithArgument('setSortingOptions', $this->repository);
        $message =$this->getExceptionArgumentsMessage([$options['column'], $options['order'], 'list']);
        $this->expectExceptionMessage($message);
        $this->invokeMethod($service, 'setSortingOptions', [$this->repository]);
    }

    /**
     *
     */
    public function testSetSortingOptionsWhenOptionsHasNotKeysColumnOrOrder()
    {
        $service = new LaraService();
        $options = [
            'column' => 'column',
            'order' => 'order'
        ];
        $this->assertNull($this->invokeMethod($service, 'setSortingOptions', [$this->repository, $options]));
    }

    /**
     *
     */
    public function testSetSortingOptionsWhenOptionsIsNotEmpty()
    {
        $service = new LaraService();
        $this->methodWillThrowExceptionWithArgument('setSortingOptions', $this->repository);
        $options = [
            'column' => 'column',
            'order' => 'order'
        ];
        $message =$this->getExceptionArgumentsMessage([$options['column'], $options['order'], 'list']);
        $this->expectExceptionMessage($message);
        $this->invokeMethod($service, 'setSortingOptions', [$this->repository, $options]);
    }

    /**
     *
     */
    public function testGetRelationForSaveAssociated_WhenDataIsString()
    {
        $service = new LaraService();
        $data = 'some-string';
        $expected = ['associated' => [$data]];
        $this->assertEquals($expected, $this->invokeMethod($service, 'getRelationForSaveAssociated', [$data]));
    }

    /**
     *
     */
    public function testGetRelationForSaveAssociated_WhenDataIaArray()
    {
        $service = new LaraService();
        $data = ['some-string'];
        $expected = ['associated' => $data];
        $this->assertEquals($expected, $this->invokeMethod($service, 'getRelationForSaveAssociated', [$data]));
    }

    /**
     *
     */
    public function testGetRelationForSaveAssociated_WhenDataIsEmpty()
    {
        $service = new LaraService();
        $data = [];
        $this->assertEquals([], $this->invokeMethod($service, 'getRelationForSaveAssociated', [$data]));
    }

    /**
     * @param array $methods
     * @return LaraService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockLaraService($methods = [])
    {
        $service = $this->getMockBuilder(LaraService::class)
            ->setMethods($methods)
            ->getMock();

        $service->setBaseRepository($this->repository);
        $service->setBaseValidator($this->validator);
        return $service;
    }

}
