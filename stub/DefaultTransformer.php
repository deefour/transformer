<?php

namespace Deefour\Transformer\Stub;

use Deefour\Transformer\Transformer;

class DefaultTransformer extends Transformer
{
    protected $fallbacks = [
        'foo' => 1,
        'bar' => 2,
        'baz' => 3,
    ];
}
