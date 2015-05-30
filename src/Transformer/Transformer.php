<?php namespace Deefour\Transformer;

use ArrayAccess;
use JsonSerializable;

class Transformer implements JsonSerializable, ArrayAccess {

  /**
   * The raw input attributes.
   *
   * @var array
   */
  protected $source;

  /**
   * Array of casts to be performed. Keys are attribute names, values are
   * type casts.
   *
   * @var array
   */
  protected $casts = [ ];

  /**
   * Constructor.
   *
   * @param  array $input
   */
  public function __construct(array $input) {
    $this->source = $input;
  }

  /**
   * Retrieve a single transformed attribute.
   *
   * @param  string $attribute
   *
   * @return mixed
   */
  public function get($attribute) {
    if ( ! $this->exists($attribute)) {
      return null;
    }

    // If a method transformation exists for the attribute, bypass the default
    // attribute casting.
    $transformerMethod = $this->transformerMethod($attribute);

    if (method_exists($this, $transformerMethod)) {
      return $this->$transformerMethod();
    }

    // Try to cast the attribute value.
    if ($this->hasCast($attribute)) {
      return $this->cast($attribute);
    }

    // If no transformation has been specified, return the raw input.
    return $this->raw($attribute);
  }

  /**
   * The raw attribute value. If no attribute is provided, the raw source is
   * returned (no transformation is performed).
   *
   * @param  string $attribute [optional]
   *
   * @return mixed
   */
  public function raw($attribute = null) {
    if (is_null($attribute)) {
      return $this->source;
    }

    return $this->exists($attribute) ? $this->source[ $attribute ] : null;
  }

  /**
   * Transform the entire input source.
   *
   * @return  array
   */
  public function all() {
    $transformation = [ ];

    foreach (array_keys($this->source) as $attribute) {
      $transformation[ $attribute ] = $this->get($attribute);
    }

    return $transformation;
  }

  /**
   * Retrieve a specific subset of the attributes from the transformation. This
   * is smart enough to understand nested sets of attributes.
   *
   * @return array
   */
  public function only() {
    $whitelist = (array)func_get_args();

    if ( ! empty($whitelist) && is_array($whitelist[0])) {
      $whitelist = $whitelist[0];
    }

    $attributes = $this->toArray();
    $response   = [ ];

    foreach ($whitelist as $key => $value) {
      if (is_string($value)) { // scalar value
        $this->addPermittedValue($response, $attributes, $value);
      } elseif ( ! is_array($value)) { // invalid structure; move on
        continue;
      } elseif (empty($value)) { // arbitrary array/collection
        $this->addPermittedCollection($response, $attributes, $key);
      } else { // recursion
        $response[ $key ] = $this->permit($whitelist[ $key ], $attributes[ $key ]);
      }
    }

    return $response;
  }

  /**
   * Boolean check whether the attribute exists on the source data, even if
   * it's null
   *
   * @param  string $attribute
   *
   * @return boolean
   */
  public function exists($attribute) {
    return array_key_exists($attribute, $this->source);
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   */
  public function jsonSerialize() {
    return $this->all();
  }

  /**
   * {@inheritdoc}
   *
   * @return boolean
   */
  public function offsetExists($offset) {
    return $this->exists($offset);
  }

  /**
   * {@inheritdoc}
   *
   * @return mixed
   */
  public function offsetGet($offset) {
    return $this->get($offset);
  }

  /**
   * {@inheritdoc}
   *
   * This is a void method because the object attributes are immutable.
   *
   * @return void
   */
  public function offsetSet($offset, $value) {
    //
  }

  /**
   * {@inheritdoc}
   *
   * This is a void method because the object attributes are immutable.
   *
   * @return void
   */
  public function offsetUnset($offset) {
    //
  }

  /**
   * Fetch an array representation of the transformed attribute source.
   *
   * @return array
   */
  public function toArray() {
    return $this->all();
  }

  /**
   * {@inheritdoc}
   *
   * @return mixed
   */
  public function __get($attribute) {
    return $this->get($attribute);
  }

  /**
   * {@inheritdoc}
   *
   * @return boolean
   */
  public function __isset($attribute) {
    return $this->exists($attribute);
  }

  /**
   * Determine whether an attribute should be casted to a native type.
   *
   * @param  string $attribute
   *
   * @return bool
   */
  protected function hasCast($attribute) {
    return array_key_exists($attribute, $this->casts);
  }

  /**
   * Get the type of cast for a model attribute.
   *
   * Pulled from Laravel's Illuminate\Database\Eloquent\Model::getCastType
   *
   * @param  string $key
   *
   * @return string
   */
  protected function getCastType($key) {
    return trim(strtolower($this->casts[ $key ]));
  }

  /**
   * Cast an attribute to a native PHP type.
   *
   * Pulled from Laravel's Illuminate\Database\Eloquent\Model::castAttribute
   *
   * @param  mixed $attribute
   *
   * @return mixed
   */
  protected function cast($attribute) {
    $value = $this->raw($attribute);

    if (is_null($value)) {
      return $value;
    }

    switch ($this->getCastType($attribute)) {
      case 'int':
      case 'integer':
        return (int)$value;
      case 'real':
      case 'float':
      case 'double':
        return (float)$value;
      case 'string':
        return (string)$value;
      case 'bool':
      case 'boolean':
        return (bool)$value;
      case 'object':
        return json_decode($value);
      case 'array':
      case 'json':
        return json_decode($value, true);
      default:
        return $value;
    }
  }

  /**
   * Adds a specific attribute to the response object.
   *
   * @param  array  $response
   * @param  mixed  $source
   * @param  string $attribute
   *
   * @return void
   */
  private function addPermittedValue(array &$response, $source, $attribute) {
    if ( ! isset($source[ $attribute ])) {
      return;
    }

    $response[ $attribute ] = $source[ $attribute ];
  }

  /**
   * Adds an arbitrary collection to the response object, by key.
   *
   * @param  array  $response
   * @param  mixed  $source
   * @param  string $attribute
   *
   * @return void
   */
  private function addPermittedCollection(array &$response, $source, $attribute) {
    if ( ! isset($source[ $attribute ]) or ! is_array($source[ $attribute ])) {
      return;
    }

    $response[ $attribute ] = $source[ $attribute ];
  }

  /**
   * Convert a snake-case attribute name into a camel-case method name.
   *
   * @return string
   */
  protected function transformerMethod($attribute) {
    return lcfirst(str_replace(' ', '', ucwords(str_replace([ '-', '_' ], ' ', $attribute))));
  }

}
