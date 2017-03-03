<?php

namespace spec\Deefour\Transformer\Stub;

use Deefour\Transformer\Stub\HiddenAttributeTransformer;
use PhpSpec\ObjectBehavior;

class HiddenAttributeTransformerSpec extends ObjectBehavior
{
    protected $source = [
        'make'      => 'Subaru',
        'model'     => 'WRX',
        'cylinders' => 4,
    ];

    public function let()
    {
        $this->beAnInstanceOf(HiddenAttributeTransformer::class);
        $this->beConstructedWith($this->source);
    }

    public function it_still_allows_access_to_hidden_attributes()
    {
        $this->get('cylinders')->shouldReturn(4);
    }

    public function it_is_missing_from_various_bulk_accessors()
    {
        $check = [ 'make' => 'Subaru', 'model' => 'WRX' ];

        $this->all()->shouldReturn($check);
        $this->toArray()->shouldReturn($check);
        $this->jsonSerialize()->shouldReturn($check);
        $this->except('make')->shouldReturn([ 'model' => 'WRX' ]);
        $this->intersect('make')->shouldReturn([ 'make' => 'Subaru' ]);
    }
}
