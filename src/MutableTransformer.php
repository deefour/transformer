<?php

namespace Deefour\Transformer;

class MutableTransformer extends Transformer
{
    /**
     * A collection of attributes that have been modified.
     *
     * @var array
     */
    protected $changes = [];

    /**
     * ArrayAccess to set an attribute on the source data.
     *
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
        $original = $this->original($attribute);

        if ($original === $value) {
            unset($this->changes[$attribute]);
        } else {
            $this->changes[$attribute] = [$original, $value];
        }

        $this->attributes[$attribute] = $value;
    }

    /**
     * Fetch a list of dirty attributes.
     *
     * @return mixed
     */
    public function dirty()
    {
        return array_keys($this->changes);
    }

    /**
     * Boolean check if an attribute is dirty. If no attribute is passed, a check
     * will be made to see if _any_ attribute on the transformer is dirty.
     *
     * @param string $attribute
     * @return boolean
     */
    public function isDirty($attribute = null)
    {
        if (is_null($attribute)) {
            return ! empty($this->changes);
        }

        return array_key_exists($attribute, $this->changes);
    }

    /**
     * Fetch the original value of the attribute. If no attribute is specified,
     * return a merge of the originals from the changeset with untouched attributes.
     *
     * @param  string $attribute [optional]
     * @return mixed
     */
    public function original($attribute = null)
    {
        if ( ! $this->isDirty($attribute)) {
            return $this->raw($attribute);
        }

        return $this->changes[$attribute][0];
    }

    /**
     * Output key/value mapping of only the changed attributes.
     *
     * @return array
     */
    public function changes()
    {
        $attributes = array_keys($this->changes);
        $values     = array_map(function ($change) {
            return $change[1];
        }, $this->changes);

        return array_combine($attributes, $values);
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
