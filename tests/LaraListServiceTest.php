<?php

namespace Tests;

use Illuminate\Support\Facades\App;
use LaraRepo\Contracts\RepositoryInterface;
use LaraService\Services\LaraListService;
use LaraTest\Traits\AccessProtectedTraits;
use LaraTest\Traits\MockTraits;

class LaraListServiceTest extends \TestCase
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
        $this->methodWillReturnTrue('findListBased', $this->service);
        $this->assertTrue($this->service->findList('RepositoryInterface'));
    }

    /**
     *
     */
    public function testFindListWhenRepositoryIsString() {
        $this->service->expects($this->any())->method('findListBased')->willReturn(true);
        $this->assertEquals([true, true], $this->service->findList(['RepositoryInterfaceOne', 'RepositoryInterfaceTwo']));
    }

    /**
     *
     */
    public function testFindListBased()
    {
        $service = new LaraListService();
        $repository = 'Repository';
        $object = $this->getMockObjectWithMockedMethods(['findList']);
        $this->methodWillReturnTrue('findList', $object);
        App::shouldReceive('make')->withArgs([$repository])->andReturn($object);
        $this->assertTrue($this->invokeMethod($service, 'findListBased', ['Repository']));
    }

}
