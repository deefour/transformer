<?php

namespace Deefour\Transformer\Stub;

use Deefour\Transformer\Transformer;

class CastTransformer extends Transformer
{
    protected $casts = [
        'foo' => 'int',
        'bar' => 'json',
        'baz' => 'float',
    ];
}
