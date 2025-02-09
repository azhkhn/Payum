<?php

namespace Payum\Core\Storage;

use LogicException;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;

final class CryptoStorageDecorator implements StorageInterface
{
    /**
     * @var StorageInterface
     */
    private $decoratedStorage;

    /**
     * @var CypherInterface
     */
    private $crypto;

    public function __construct(StorageInterface $decoratedStorage, CypherInterface $crypto)
    {
        $this->decoratedStorage = $decoratedStorage;
        $this->crypto = $crypto;
    }

    public function create()
    {
        $model = $this->decoratedStorage->create();

        $this->assertCrypted($model);

        return $model;
    }

    public function support($model)
    {
        return $this->decoratedStorage->support($model);
    }

    public function update($model)
    {
        $this->assertCrypted($model);

        $model->encrypt($this->crypto);

        $this->decoratedStorage->update($model);
    }

    public function delete($model)
    {
        $this->decoratedStorage->delete($model);
    }

    public function find($id)
    {
        $model = $this->decoratedStorage->find($id);

        $this->assertCrypted($model);

        $model->decrypt($this->crypto);

        return $model;
    }

    public function findBy(array $criteria)
    {
        $models = $this->decoratedStorage->findBy($criteria);

        foreach ($models as $model) {
            $this->assertCrypted($model);

            $model->decrypt($this->crypto);
        }

        return $models;
    }

    public function identify($model)
    {
        return $this->decoratedStorage->identify($model);
    }

    /**
     * @param object $model
     */
    private function assertCrypted($model)
    {
        if (false == $model instanceof  CryptedInterface) {
            throw new LogicException(sprintf(
                'The model %s must implement %s interface. It is required for this decorator.',
                get_class($model),
                CryptedInterface::class
            ));
        }
    }
}
