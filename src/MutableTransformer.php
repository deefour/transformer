<?php

namespace Deefour\Transformer;

class MutableTransformer extends Transformer
{
    /**
     * Constructor.
     *
     * @param array $source [optional]
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * ArrayAccess to set an attribute on the source data.
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * ArrayAccess to remove an attribute from the source data.
     *
     * @return mixed
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Set an attribute on the source data.
     *
     * @param string $attribute
     * @param mixed  $value
     */
    public function set($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * Magic setter.
     *
     * @param string $attribute
     * @param mixed  $value
     */
    public function __set($attribute, $value)
    {
        $this->set($attribute, $value);
    }

    /**
     * Accessor/mutator via magic call.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (count($parameters)) {
            $this->set($method, $parameters[0]);
        }

        return $this->get($method);
    }
}
