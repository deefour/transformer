<?php namespace spec\Deefour\Transformer;

use Deefour\Transformer\Transformer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TransformerSpec extends ObjectBehavior {

  protected $source = [
    'foo' => '1234',
    'bar' => null,
    'profile' => [
      'first_name' => 'Jason',
      'last_name'  => 'Daly',
      'state'      => 'CT',
      'country'    => 'USA',
    ],
  ];

  function let() {
    $this->beAnInstanceOf(Transformer::class);
    $this->beConstructedWith($this->source);
  }


  function it_returns_unmofified_attribute_values() {
    $this->get('foo')->shouldReturn('1234');
    $this->get('bar')->shouldReturn(null);
  }

  function it_returns_null_for_unknown_values() {
    $this->get('unknown')->shouldReturn(null);
  }

  function it_can_return_all_values() {
    $this->all()->shouldReturn($this->source);
  }

  function it_allows_array_access() {
    $this['foo']->shouldReturn('1234');
  }

  function it_does_not_modify_attributes_through_array_access() {
    $this['foo'] = 'omg';

    $this['foo']->shouldReturn('1234');
  }

  function it_does_not_unset_attributes_through_array_access() {
    unset($this['foo']);

    $this['foo']->shouldReturn('1234');
  }

  function it_responds_to_magic_isset() {
    $this->__isset('foo')->shouldReturn(true);
    $this->__isset('bar')->shouldReturn(true);
    $this->__isset('baz')->shouldReturn(false);
  }

  function it_tests_for_existence_of_attributes() {
    $this->exists('foo')->shouldReturn(true);
    $this->exists('baz')->shouldReturn(false);
  }

  function it_can_be_serialized_to_json() {
    $this->jsonSerialize()->shouldEqual($this->source);
  }

  function it_responds_to_magic_property_access() {
    $this->foo->shouldEqual('1234');
    $this->baz->shouldEqual(null);
  }

  function it_allows_attributes_to_be_plucked() {
    $this->only('foo')->shouldReturn([ 'foo' => '1234' ]);
    $this->only('foo', 'bar')->shouldReturn([ 'foo' => '1234', 'bar' => null ]);
  }

  function it_rejects_unknown_attributes_during_pluck() {
    $this->only('foo', 'INVALID')->shouldReturn([ 'foo' => '1234' ]);
  }

  function it_allows_plucking_via_array_of_keys() {
    $this->only([ 'foo' ])->shouldReturn([ 'foo' => '1234' ]);
    $this->only([ 'foo', 'bar' ])->shouldReturn([ 'foo' => '1234', 'bar' => null ]);
  }

  function it_allows_deep_plucking_via_nested_arrays() {
    $this->only('profile')->shouldReturn([ 'profile' => $this->source['profile'] ]);
    $this->only([ 'profile' => [ 'first_name' ] ])->shouldReturn([ 'profile' => [ 'first_name' => 'Jason' ]]);
  }

  function it_provides_attribute_access_via_magic_call() {
    $this->callOnWrappedObject('__call', [ 'foo', [] ])->shouldReturn('1234');
    $this->callOnWrappedObject('__call', [ 'bar', [] ])->shouldReturn(null);
    $this->callOnWrappedObject('__call', [ 'baz', [] ])->shouldReturn(null);
  }

  function it_disallows_attribute_mutation_via_magic_call() {
    $this->callOnWrappedObject('__call', [ 'foo', [] ])->shouldReturn('1234');
    $this->callOnWrappedObject('__call', [ 'foo', [ 'abc' ] ])->shouldReturn('1234');
  }

}
