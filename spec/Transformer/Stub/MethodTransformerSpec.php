<?php namespace spec\Deefour\Transformer\Stub;

use Deefour\Transformer\Stub\MethodTransformer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MethodTransformerSpec extends ObjectBehavior {

  protected $source = [ 'foo' => 'some text', 'bar_baz' => 'jason', 'bing' => 123 ];

  function let() {
    $this->beAnInstanceOf(MethodTransformer::class);
    $this->beConstructedWith($this->source);
  }

  function it_returns_modified_attribute_values() {
    $this->get('foo')->shouldReturn('SOME TEXT');
    $this->get('bar_baz')->shouldReturn('Jason');
  }

  function it_can_return_all_values() {
    $this->all()->shouldReturn([
      'foo'     => 'SOME TEXT',
      'bar_baz' => 'Jason',
      'bing'    => 123
    ]);
  }

  function it_allows_plucking_groups_of_attributes_at_once() {
    $this->only('foo', 'bing')->shouldEqual([ 'foo' => 'SOME TEXT', 'bing' => 123 ]);
    $this->only([ 'foo', 'bing' ])->shouldEqual([ 'foo' => 'SOME TEXT', 'bing' => 123 ]);

    $this->only('poo')->shouldEqual([]);
    $this->only()->shouldEqual([]);
  }

}
