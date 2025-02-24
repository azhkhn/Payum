<?php

namespace Payum\Core\Storage;

use Payum\Core\Exception\InvalidArgumentException;

abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @param $modelClass
     */
    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function create()
    {
        return new $this->modelClass();
    }

    public function support($model)
    {
        return $model instanceof $this->modelClass;
    }

    public function update($model)
    {
        $this->assertModelSupported($model);

        $this->doUpdateModel($model);
    }

    public function find($id)
    {
        if ($id instanceof IdentityInterface) {
            if (ltrim($id->getClass(), '\\') === ltrim($this->modelClass, '\\')) {
                return $this->doFind($id->getId());
            }

            return;
        }

        return $this->doFind($id);
    }

    public function delete($model)
    {
        $this->assertModelSupported($model);

        $this->doDeleteModel($model);
    }

    public function identify($model)
    {
        $this->assertModelSupported($model);

        return $this->doGetIdentity($model);
    }

    /**
     * @param object $model
     */
    abstract protected function doUpdateModel($model);

    /**
     * @param object $model
     */
    abstract protected function doDeleteModel($model);

    /**
     * @param object $model
     *
     * @return IdentityInterface
     */
    abstract protected function doGetIdentity($model);

    /**
     * @param mixed $id
     *
     * @return object|null
     */
    abstract protected function doFind($id);

    /**
     * @param object $model
     *
     * @throws InvalidArgumentException
     */
    protected function assertModelSupported($model)
    {
        if (false == $this->support($model)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid model given. Should be instance of %s but it is %s',
                $this->modelClass,
                is_object($model) ? get_class($model) : gettype($model)
            ));
        }
    }
}
