<?php namespace Deefour\Transformer;

class MutableTransformer extends Transformer {

  /**
   * ArrayAccess to set an attribute on the source data.
   *
   * @return void
   */
  public function offsetSet($offset, $value) {
    $this->set($offset, $value);
  }

  /**
   * ArrayAccess to remove an attribute from the source data.
   *
   * @return mixed
   */
  public function offsetUnset($offset) {
    unset($this->source[ $offset ]);
  }

  /**
   * {@inheritdoc}
   *
   * Magic setter.
   *
   * @param  string $attribute
   * @param  mixed  $value
   */
  public function __set($attribute, $value) {
    $this->set($attribute, $value);
  }

  /**
   * Set an attribute on the source data.
   *
   * @param  string $attribute
   * @param  mixed  $value
   */
  public function set($attribute, $value) {
    $this->source[ $attribute ] = $value;
  }

}
