<?php namespace Deefour\Transformer\Stub;

use Deefour\Transformer\Transformer;

class RawOverrideTransformer extends Transformer {

  public function raw($attribute) {
    return trim(parent::raw($attribute));
  }

}
