<?php

namespace spec\Deefour\Transformer\Stub;

use Deefour\Transformer\Stub\MethodTransformer;
use PhpSpec\ObjectBehavior;

class MethodTransformerSpec extends ObjectBehavior
{
    protected $source = ['foo' => 'some text', 'bar_baz' => 'jason', 'bing' => 123];

    public function let()
    {
        $this->beAnInstanceOf(MethodTransformer::class);
        $this->beConstructedWith($this->source);
    }

    public function it_returns_modified_attribute_values()
    {
        $this->get('foo')->shouldReturn('SOME TEXT');
        $this->get('bar_baz')->shouldReturn('Jason');
    }

    public function it_includes_method_only_attributes_in_bulk_output()
    {
      $this->get('method_attribute')->shouldBe(true);
      $this->raw('method_attribute')->shouldBe(null);
      $this->all()->shouldHaveKey('method_attribute');

      $this->only('method_attribute')->shouldHaveKey('method_attribute');
    }

    public function it_ignores_tagged_method_attributes()
    {
      $this->get('ignore_me')->shouldBe(null);
      $this->all()->shouldNotHaveKey('ignore_me');
    }

    public function it_allows_plucking_groups_of_attributes_at_once()
    {
        $this->only('foo', 'bing')->shouldEqual(['foo' => 'SOME TEXT', 'bing' => 123]);
        $this->only(['foo', 'bing'])->shouldEqual(['foo' => 'SOME TEXT', 'bing' => 123]);

        $this->only('poo')->shouldEqual([]);
        $this->only()->shouldEqual([]);

        $this->only('method_attribute')->shouldEqual(['method_attribute' => true]);
    }
}
