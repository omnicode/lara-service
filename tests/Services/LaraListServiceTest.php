<?php

namespace Tests\Services;

use Illuminate\Support\Facades\App;
use LaraRepo\Contracts\RepositoryInterface;
use LaraService\Services\LaraListService;
use LaraTest\Traits\AccessProtectedTraits;
use LaraTest\Traits\MockTraits;
use Tests\TestCase;

class LaraListServiceTest extends TestCase
{
    use MockTraits, AccessProtectedTraits;

    /**
     * @var
     */
    protected $repository;


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
        $this->repository = $this->getMockForAbstractClass(RepositoryInterface::class);
        $this->service = $this->getMockBuilder(LaraListService::class)
            ->setMethods(['findListBased'])
            ->getMock();
    }

    /**
     *
     */
    public function testFindListWhenRepositoryIsNotString()
    {
        $this->methodWillReturnTrue($this->service, 'findListBased');
        $this->assertTrue($this->service->findList('RepositoryInterface'));
    }

    /**
     *
     */
    public function testFindListWhenRepositoryIsString() {
        $this->methodWillReturnTrue($this->service, 'findListBased', [], 'any');
        $this->assertEquals([true, true], $this->service->findList(['RepositoryInterfaceOne', 'RepositoryInterfaceTwo']));
    }

    /**
     *
     */
    public function testFindListBased()
    {
        $repository = 'Repository';

        $service = new LaraListService();
        $object = $this->getMockObjectWithMockedMethods(['findList']);
        $this->methodWillReturnTrue($object, 'findList');
        App::shouldReceive('make')
            ->once()
            ->with($repository)
            ->andReturn($object);

        $this->assertTrue($this->callProtectedMethod($service, 'findListBased', [$repository]));
    }

}
