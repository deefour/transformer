<?php

namespace spec\Deefour\Transformer\Stub;

use Deefour\Transformer\Stub\IntersectionTransformer;
use PhpSpec\ObjectBehavior;

class IntersectionTransformerSpec extends ObjectBehavior
{
    protected $source = [
        'make'      => 'Subaru',
        'model'     => 'WRX',
        'cylinders' => null
    ];

    public function let()
    {
        $this->beAnInstanceOf(IntersectionTransformer::class);
        $this->beConstructedWith($this->source);
    }

    public function it_omits_null_attributes_from_intersection()
    {
        $this->intersect('cylinders')->shouldReturn([]);
        $this->intersect('cylinders', 'model')->shouldReturn([ 'model' => 'WRX' ]);
        $this->intersect([ 'cylinders', 'model' ])->shouldReturn([ 'model' => 'WRX' ]);
    }
}
