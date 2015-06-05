<?php

namespace Deefour\Transformer\Stub;

use Deefour\Transformer\Transformer;

class MethodTransformer extends Transformer
{
    protected function foo()
    {
        return strtoupper($this->raw('foo'));
    }

    protected function barBaz()
    {
        return ucfirst($this->raw('bar_baz'));
    }
}
