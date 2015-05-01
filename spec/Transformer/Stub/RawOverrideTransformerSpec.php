<?php namespace spec\Deefour\Transformer\Stub;

use Deefour\Transformer\Stub\RawOverrideTransformer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RawOverrideTransformerSpec extends ObjectBehavior {

  protected $source = [ 'foo' => '   some text' ];

  function let() {
    $this->beAnInstanceOf(RawOverrideTransformer::class);
    $this->beConstructedWith($this->source);
  }

  function it_hits_raw_method_on_toArray_by_default() {
    $this->raw('foo')->shouldReturn('some text');
    $this->get('foo')->shouldReturn('some text');
  }

}
