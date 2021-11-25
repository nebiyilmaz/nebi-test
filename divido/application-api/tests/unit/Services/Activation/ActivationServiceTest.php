<?php

declare(strict_types=1);

namespace Divido\Test\Unit\Services\Activation;

use Divido\ApiExceptions\AbstractException;
use Divido\ApiExceptions\ApplicationActivationNotPossibleException;
use Divido\Proxies\Platform;
use Divido\Services\Activation\Activation;
use Divido\Services\Activation\ActivationDatabaseInterface;
use Divido\Services\Activation\ActivationService;
use Divido\Services\Application\Application;
use Divido\Services\Application\ApplicationService;
use Divido\Services\Event\EventService;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ActivationServiceTest extends MockeryTestCase
{
    private const APPLICATION_ID = '-application-id-';

    /**
     * @var ActivationCreationService
     */
    private $service;

    private $platformMasterDb;

    private $platformReadReplicaDb;

    private $activationDatabaseInterface;

    private $applicationService;

    private $eventService;

    private $platformProxy;

    public function setUp(): void
    {
        $this->platformMasterDb = \Mockery::spy(\PDO::class);
        $this->platformReadReplicaDb = \Mockery::spy(\PDO::class);

        $this->activationDatabaseInterface = new ActivationDatabaseInterface(
            $this->platformMasterDb,
            $this->platformReadReplicaDb
        );

        $this->applicationService = \Mockery::spy(ApplicationService::class);
        $this->eventService = \Mockery::mock(EventService::class);
        $this->platformProxy = \Mockery::mock(Platform::class);

        $this->service = new ActivationService(
            $this->applicationService,
            $this->eventService,
            $this->activationDatabaseInterface,
            $this->platformProxy
        );
    }

    public function test_GetAllActivations_TestProductData_IsObjectContainingObjects()
    {
        $application = (new Application())->setId(self::APPLICATION_ID);

        $this->applicationService->shouldReceive('getOne')->once()->andReturn($application);

        $productData = '{"3":{"name":"NAME_1","sku":"SKU_1","quantity":"1","price":"16.66"},"2":{"sku":"SKU_2","name":"NAME_2","price":"33.33","quantity":"1"},"4":{"quantity":"1","name":"NAME_3","price":"110.00"},"1":{"price":"499.99","name":"NAME_4","sku":"SKU_4","quantity":"1"}}';

        $PDOStatement = \Mockery::spy(\PDOStatement::class);
        $PDOStatement->shouldReceive('fetchAll')->once()->andReturn([
            (object) [
                'id' => '',
                'application_id' => '',
                'status' => '',
                'amount' => 10000,
                'product_data' => $productData,
                'reference' => '',
                'delivery_method' => '',
                'tracking_number' => '',
                'comment' => '',
                'created_at' => '',
                'updated_at' => '',
            ],
        ]);
        $this->platformReadReplicaDb->shouldReceive('prepare')->once()->andReturn($PDOStatement);

        $results = $this->service->getAll($application);
        $data = $results[0]->getProductData();
        $data = json_decode(json_encode($data));

        $this->assertEquals('NAME_1', $data[0]->name);
        $this->assertEquals('SKU_1', $data[0]->sku);
        $this->assertEquals('1', $data[0]->quantity);
        $this->assertEquals("16.66", $data[0]->price);

        $this->assertEquals('NAME_2', $data[1]->name);
        $this->assertEquals('SKU_2', $data[1]->sku);
        $this->assertEquals('1', $data[1]->quantity);
        $this->assertEquals("33.33", $data[1]->price);

        $this->assertEquals('NAME_3', $data[2]->name);
        $this->assertEquals('1', $data[2]->quantity);
        $this->assertEquals("110.00", $data[2]->price);

        $this->assertEquals('NAME_4', $data[3]->name);
        $this->assertEquals('SKU_4', $data[3]->sku);
        $this->assertEquals('1', $data[3]->quantity);
        $this->assertEquals("499.99", $data[3]->price);
    }

    public function test_GetAllActivations_TestProductData_IsEmptyString()
    {
        $application = (new Application())->setId(self::APPLICATION_ID);

        $this->applicationService->shouldReceive('getOne')->once()->andReturn($application);

        $productData = '';
        $PDOStatement = \Mockery::spy(\PDOStatement::class);
        $PDOStatement->shouldReceive('fetchAll')->once()->andReturn([
            (object) [
                'id' => '',
                'application_id' => '',
                'status' => '',
                'amount' => 10000,
                'product_data' => $productData,
                'reference' => '',
                'delivery_method' => '',
                'tracking_number' => '',
                'comment' => '',
                'created_at' => '',
                'updated_at' => '',
            ],
        ]);
        $this->platformReadReplicaDb->shouldReceive('prepare')->once()->andReturn($PDOStatement);

        $results = $this->service->getAll($application);
        $data = $results[0]->getProductData();
        $data = json_decode(json_encode($data));

        $this->assertEquals([], $data);
    }

    public function test_GetAllActivations_TestProductData_IsEmptyArray()
    {
        $application = (new Application())->setId(self::APPLICATION_ID);

        $this->applicationService->shouldReceive('getOne')->once()->andReturn($application);

        $productData = '[]';
        $PDOStatement = \Mockery::spy(\PDOStatement::class);
        $PDOStatement->shouldReceive('fetchAll')->once()->andReturn([
            (object) [
                'id' => '',
                'application_id' => '',
                'status' => '',
                'amount' => 10000,
                'product_data' => $productData,
                'reference' => '',
                'delivery_method' => '',
                'tracking_number' => '',
                'comment' => '',
                'created_at' => '',
                'updated_at' => '',
            ],
        ]);
        $this->platformReadReplicaDb->shouldReceive('prepare')->once()->andReturn($PDOStatement);

        $results = $this->service->getAll($application);
        $data = $results[0]->getProductData();
        $data = json_decode(json_encode($data));

        $this->assertEquals([], $data);
    }

    public function test_GetAllActivations_TestProductData_IsArrayWithOneItem()
    {
        $application = (new Application())->setId(self::APPLICATION_ID);

        $this->applicationService->shouldReceive('getOne')->once()->andReturn($application);

        $productData = '[{"name":"NAME_1","quantity":1,"price":150000,"sku":"SKU_1"}]';
        $PDOStatement = \Mockery::spy(\PDOStatement::class);
        $PDOStatement->shouldReceive('fetchAll')->once()->andReturn([
            (object) [
                'id' => '',
                'application_id' => '',
                'status' => '',
                'amount' => 10000,
                'product_data' => $productData,
                'reference' => '',
                'delivery_method' => '',
                'tracking_number' => '',
                'comment' => '',
                'created_at' => '',
                'updated_at' => '',
            ],
        ]);
        $this->platformReadReplicaDb->shouldReceive('prepare')->once()->andReturn($PDOStatement);

        $results = $this->service->getAll($application);
        $data = $results[0]->getProductData();
        $data = json_decode(json_encode($data));

        $this->assertEquals('NAME_1', $data[0]->name);
        $this->assertEquals('SKU_1', $data[0]->sku);
        $this->assertEquals('1', $data[0]->quantity);
        $this->assertEquals("150000", $data[0]->price);
    }

    public function testCreateActivation(): void
    {
        $service = new ActivationService(
            $this->applicationService,
            $this->eventService,
            $this->createMock(ActivationDatabaseInterface::class),
            $this->platformProxy
        );

        $this->willFetchAnApplication();
        $this->eventService->shouldReceive('supports')->andReturn(true);

        $this->eventService
            ->shouldReceive('newEvent')
            ->once()
            ->with('activation', \Mockery::hasKey('application_activation_id'));

        $service->create($this->getActivation());
    }

    public function testCreateActivationViaPlatform(): void
    {
        $service = new ActivationService(
            $this->applicationService,
            $this->eventService,
            $this->createMock(ActivationDatabaseInterface::class),
            $this->platformProxy
        );

        $this->willFetchAnApplication();
        $this->eventService->shouldReceive('supports')->andReturn(false);

        $this->platformProxy
            ->shouldReceive('trigger')
            ->once()
            ->with(Platform::ACTIVATE_APPLICATION, \Mockery::hasKey('id'));

        $service->create($this->getActivation());
    }

    public function testCreateActivationViaPlatformException(): void
    {
        $service = new ActivationService(
            $this->applicationService,
            $this->eventService,
            $this->createMock(ActivationDatabaseInterface::class),
            $this->platformProxy
        );

        $this->willFetchAnApplication();
        $this->eventService->shouldReceive('supports')->andReturn(false);

        $this->platformProxy
            ->shouldReceive('trigger')
            ->andThrow($this->createMock(AbstractException::class));

        $this->expectException(ApplicationActivationNotPossibleException::class);
        $service->create($this->getActivation());
    }

    private function willFetchAnApplication(): void
    {
        $application = new Application();
        $application
            ->setId(self::APPLICATION_ID)
            ->setStatus('READY')
            ->setPurchasePrice(10000)
            ->setDepositAmount(0)
            ->setActivatedAmount(0)
            ->setCancelledAmount(0);

        $this->applicationService
            ->shouldReceive('getOne')
            ->once()
            ->andReturn($application);
    }

    private function getActivation(): Activation
    {
        $activation = new Activation();
        $activation
            ->setApplicationId(self::APPLICATION_ID)
            ->setStatus('REQUESTED')
            ->setProductData([]);

        return $activation;
    }
}
