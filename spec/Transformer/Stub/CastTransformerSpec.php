<?php namespace spec\Deefour\Transformer\Stub;

use Deefour\Transformer\Stub\CastTransformer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CastTransformerSpec extends ObjectBehavior {

  protected $source = [ 'foo' => '1234', 'bar' => null, 'buzz' => '1.2' ];

  function let() {
    $this->beAnInstanceOf(CastTransformer::class);
    $this->beConstructedWith($this->source);
  }

  function it_returns_attributes_with_casting() {
    $this->get('foo')->shouldReturn(1234);
    $this->get('bar')->shouldReturn(null);
    $this->get('baz')->shouldReturn(null);
    $this->get('buzz')->shouldReturn('1.2');
  }

  function it_can_return_all_values() {
    $this->all()->shouldReturn([
      'foo'  => 1234,
      'bar'  => null,
      'buzz' => '1.2',
    ]);
  }

  function it_casts_via_magic_property_access() {
    $this->foo->shouldEqual(1234);
  }

  function it_allows_access_to_raw_source_data() {
    $this->raw('foo')->shouldEqual('1234');
  }

}
