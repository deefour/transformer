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

    public function it_tracks_changed_attributes()
    {
        $this->beConstructedWith([ 'foo' => 'aaa', 'bar' => 'bbb' ]);

        $this->dirty()->shouldReturn([]);

        $this->foo = 'ccc';

        $this->dirty()->shouldReturn([ 'foo' ]);

        $this->bar = '999';

        $this->dirty()->shouldReturn([ 'foo', 'bar' ]);

        $this->foo = 'aaa';

        $this->dirty()->shouldReturn([ 'bar' ]);
    }

    public function it_responds_whether_an_attribute_is_dirty()
    {
        $this->beConstructedWith([ 'foo' => 'aaa', 'bar' => 'bbb' ]);

        $this->isDirty()->shouldReturn(false);
        $this->isDirty('foo')->shouldReturn(false);

        $this->foo = 'ccc';

        $this->isDirty()->shouldReturn(true);
        $this->isDirty('foo')->shouldReturn(true);
        $this->isDirty('bar')->shouldReturn(false);
    }

    public function it_maintains_an_original_state_of_modified_attribtes()
    {
        $this->beConstructedWith([ 'foo' => 'aaa', 'bar' => 'bbb' ]);

        $this->original('foo')->shouldReturn('aaa');

        $this->foo = 'bbb';

        $this->get('foo')->shouldReturn('bbb');
        $this->original('foo')->shouldReturn('aaa');
    }

    public function it_will_detail_mapping_of_changed_attributes() {
        $this->beConstructedWith([ 'foo' => 'aaa', 'bar' => 'bbb' ]);

        $this->foo = 'bbb';

        $this->changed()->shouldReturn([ 'foo' => 'bbb' ]);

        $this->foo = 'aaa';

        $this->changed()->shouldReturn([]);
    }
}
