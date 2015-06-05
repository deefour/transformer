<?php

namespace spec\Deefour\Transformer;

use Deefour\Transformer\MutableTransformer;
use PhpSpec\ObjectBehavior;

class MutableTransformerSpec extends ObjectBehavior
{
    protected $source = ['foo' => 'some text'];

    public function let()
    {
        $this->beAnInstanceOf(MutableTransformer::class);
    }

    public function it_allows_instantiation_without_constructor_args()
    {
        $this->toArray()->shouldBeLike([]);
    }

    public function it_allows_mutation_of_underlying_source()
    {
        $this->beConstructedWith($this->source);

        $this->get('foo')->shouldReturn('some text');
        $this->get('bar')->shouldBeNull();
        $this->set('bar', 'baz');
        $this->get('bar')->shouldReturn('baz');
        $this->offsetUnset('foo');
        $this->get('foo')->shouldBeNull();
    }

    public function it_allows_mutation_via_magic_setter()
    {
        $this->baz = 'test';

        $this->get('baz')->shouldReturn('test');
    }

    public function it_provides_attribute_mutation_via_magic_call()
    {
        $this->beConstructedWith($this->source);

        $this->callOnWrappedObject('__call', ['foo', []])->shouldReturn('some text');
        $this->callOnWrappedObject('__call', ['foo', ['abc']])->shouldReturn('abc');
    }
}
