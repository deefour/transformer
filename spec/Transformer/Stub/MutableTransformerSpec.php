<?php namespace spec\Deefour\Transformer\Stub;

use Deefour\Transformer\Stub\MutableTransformer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MutableTransformerSpec extends ObjectBehavior {

  protected $source = [ 'foo' => 'some text' ];

  function let() {
    $this->beAnInstanceOf(MutableTransformer::class);
    $this->beConstructedWith($this->source);
  }

  function it_hits_raw_method_on_toArray_by_default() {
    $this->get('foo')->shouldReturn('some text');
    $this->get('bar')->shouldBeNull();
    $this->set('bar', 'baz');
    $this->get('bar')->shouldReturn('baz');
    $this->offsetUnset('foo');
    $this->get('foo')->shouldBeNull();
  }

}
