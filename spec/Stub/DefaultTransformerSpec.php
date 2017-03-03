<?php

namespace spec\Deefour\Transformer\Stub;

use Deefour\Transformer\Stub\DefaultTransformer;
use Deefour\Transformer\Transformer;
use PhpSpec\ObjectBehavior;

class DefaultTransformerSpec extends ObjectBehavior
{
    protected $source = [ 'foo' => -1, 'baz' => null ];

    public function let()
    {
        $this->beAnInstanceOf(DefaultTransformer::class);
        $this->beConstructedWith($this->source);
    }

    public function it_should_allow_direct_access_to_defaults()
    {
        $this->fallback('foo')->shouldReturn(1);
        $this->fallback('bar')->shouldReturn(2);
        $this->fallback('unknown')->shouldReturn(null);
    }

    public function it_should_return_defaults_via_regular_gets()
    {
        $this->get('foo')->shouldReturn(-1);
        $this->get('bar')->shouldReturn(2);
        $this->get('baz')->shouldReturn(3);
        $this->raw('baz')->shouldBeNull();
        $this->get('unknown')->shouldReturn(null);
    }

    public function it_should_return_all_defaults_by_default()
    {
        $this->fallback()->shouldEqual([ 'foo' => 1, 'bar' => 2, 'baz' => 3 ]);
    }

    public function it_should_merge_defaults_into_all()
    {
        $this->all()->shouldEqual([ 'foo' => -1, 'bar' => 2, 'baz' => 3 ]);
    }

    public function it_should_skip_defaults_when_requested()
    {
        $this->get('baz')->shouldReturn(3);

        Transformer::preferNullValues();

        $this->get('baz')->shouldReturn(null);
        $this->all()->shouldEqual([ 'foo' => -1, 'bar' => 2, 'baz' => null ]);

        Transformer::preferNullValues(false);

        $this->get('baz')->shouldReturn(3);
        $this->all()->shouldEqual([ 'foo' => -1, 'bar' => 2, 'baz' => 3 ]);
    }
}
