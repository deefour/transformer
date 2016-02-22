<?php

namespace Deefour\Transformer\Stub;

use Deefour\Transformer\Transformer;

class MethodTransformer extends Transformer
{
    /**
     * @attribute
     */
    public function foo()
    {
        return strtoupper($this->raw('foo'));
    }

    /**
     * @attribute
     */
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
