<?php

namespace Deefour\Transformer\Stub;

use Deefour\Transformer\Transformer;

class MethodTransformer extends Transformer
{
    public function foo()
    {
        return strtoupper($this->raw('foo'));
    }

    public function barBaz()
    {
        return ucfirst($this->raw('bar_baz'));
    }

    /**
     * @attribute
     */
    public function methodAttribute()
    {
        return true;
    }

    public function ignoreMe()
    {
        return true;
    }
}
