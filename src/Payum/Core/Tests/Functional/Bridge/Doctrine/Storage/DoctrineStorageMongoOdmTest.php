<?php

namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Storage;

use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Model\Identity;
use Payum\Core\Tests\Functional\Bridge\Doctrine\MongoTest;
use Payum\Core\Tests\Mocks\Document\TestModel;

class DoctrineStorageMongoOdmTest extends MongoTest
{
    public function testShouldUpdateModelAndSetId()
    {
        $storage = new DoctrineStorage(
            $this->dm,
            TestModel::class
        );

        $model = $storage->create();

        $storage->update($model);

        $this->assertNotNull($model->getId());
    }

    public function testShouldGetModelIdentifier()
    {
        $storage = new DoctrineStorage(
            $this->dm,
            TestModel::class
        );

        $model = $storage->create();

        $storage->update($model);

        $this->assertNotNull($model->getId());

        $identity = $storage->identify($model);

        $this->assertInstanceOf(Identity::class, $identity);
        $this->assertInstanceOf($identity->getClass(), $model);
        $this->assertEquals($model->getId(), $identity->getId());
    }

    public function testShouldFindModelById()
    {
        $storage = new DoctrineStorage(
            $this->dm,
            TestModel::class
        );

        $model = $storage->create();

        $storage->update($model);

        $requestId = $model->getId();

        $this->dm->clear();

        $model = $storage->find($requestId);

        $this->assertInstanceOf(TestModel::class, $model);
        $this->assertEquals($requestId, $model->getId());
    }

    public function testShouldFindModelByIdentity()
    {
        $storage = new DoctrineStorage(
            $this->dm,
            TestModel::class
        );

        $model = $storage->create();

        $storage->update($model);

        $requestId = $model->getId();

        $this->dm->clear();

        $identity = $storage->identify($model);

        $foundModel = $storage->find($identity);

        $this->assertInstanceOf(TestModel::class, $foundModel);
        $this->assertEquals($requestId, $foundModel->getId());
    }

    public function testShouldFindByCurrency()
    {
        $storage = new DoctrineStorage(
            $this->dm,
            TestModel::class
        );

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('USD');
        $storage->update($model);

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('USD');
        $storage->update($model);

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('EUR');
        $storage->update($model);

        $result = $storage->findBy([
            'currency' => 'USD',
        ]);

        $this->assertCount(2, $result);
        $this->assertContainsOnly(TestModel::class, $result);

        $result = $storage->findBy([
            'currency' => 'EUR',
        ]);

        $this->assertCount(1, $result);
        $this->assertContainsOnly(TestModel::class, $result);
    }

    public function testShouldFindByAllIfCriteriaIsEmpty()
    {
        $storage = new DoctrineStorage(
            $this->dm,
            TestModel::class
        );

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('USD');
        $storage->update($model);

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('USD');
        $storage->update($model);

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('EUR');
        $storage->update($model);

        $result = $storage->findBy([]);

        $this->assertCount(3, $result);
        $this->assertContainsOnly(TestModel::class, $result);
    }
}
