<?php

namespace spec\Deefour\Transformer;

use Deefour\Transformer\Transformer;
use PhpSpec\ObjectBehavior;

class TransformerSpec extends ObjectBehavior
{
    protected $source = [
        'foo' => '1234',
        'bar' => null,
        'profile' => [
            'first_name' => 'Jason',
            'last_name'  => 'Daly',
            'state'      => 'CT',
            'country'    => 'USA',
        ],
        'zap' => [
            'bop' => true,
            'pob' => false
        ]
    ];

    public function let()
    {
        $this->beAnInstanceOf(Transformer::class);
        $this->beConstructedWith($this->source);
    }

    public function it_returns_unmofified_attribute_values()
    {
        $this->get('foo')->shouldReturn('1234');
        $this->get('bar')->shouldReturn(null);
    }

    public function it_returns_null_for_unknown_values()
    {
        $this->get('unknown')->shouldReturn(null);
    }

    public function it_can_return_all_values()
    {
        $this->all()->shouldReturn($this->source);
    }

    public function it_allows_array_access()
    {
        $this['foo']->shouldReturn('1234');
    }

    public function it_does_not_modify_attributes_through_array_access()
    {
        $this['foo'] = 'omg';

        $this['foo']->shouldReturn('1234');
    }

    public function it_does_not_unset_attributes_through_array_access()
    {
        unset($this['foo']);

        $this['foo']->shouldReturn('1234');
    }

    public function it_responds_to_magic_isset()
    {
        $this->__isset('foo')->shouldReturn(true);
        $this->__isset('bar')->shouldReturn(true);
        $this->__isset('baz')->shouldReturn(false);
    }

    public function it_tests_for_existence_of_attributes()
    {
        $this->exists('foo')->shouldReturn(true);
        $this->exists('baz')->shouldReturn(false);

        $this->contains('foo')->shouldReturn(true);
        $this->contains('baz')->shouldReturn(false);

        $this->has('foo')->shouldReturn(true);
        $this->has('baz')->shouldReturn(false);
    }

    public function it_can_be_serialized_to_json()
    {
        $this->jsonSerialize()->shouldEqual($this->source);
    }

    public function it_responds_to_magic_property_access()
    {
        $this->foo->shouldEqual('1234');
        $this->baz->shouldEqual(null);
    }

    public function it_allows_attributes_to_be_plucked()
    {
        $this->only('foo')->shouldReturn(['foo' => '1234']);
        $this->only('foo', 'bar')->shouldReturn(['foo' => '1234', 'bar' => null]);
        $this->only([ 'zap' => [ 'bop' ] ])->shouldReturn([ 'zap' => [ 'bop' => true ] ]);
    }

    public function it_silently_ignores_unknown_properties_during_pluck()
    {
        $this->only('foo')->shouldReturn(['foo' => '1234']);
        $this->only('foo', 'bar')->shouldReturn(['foo' => '1234', 'bar' => null]);
    }

    public function it_rejects_unknown_attributes_during_pluck()
    {
        $this->only('foo', 'oops')->shouldReturn(['foo' => '1234']);
        $this->only('foo', [ 'baz' => [ 'oops' ] ])->shouldReturn(['foo' => '1234']);
    }

    public function it_allows_plucking_via_array_of_keys()
    {
        $this->only(['foo'])->shouldReturn(['foo' => '1234']);
        $this->only(['foo', 'bar'])->shouldReturn(['foo' => '1234', 'bar' => null]);
    }

    public function it_allows_deep_plucking_via_nested_arrays()
    {
        $this->only('profile')->shouldReturn(['profile' => $this->source['profile']]);
        $this->only(['profile' => ['first_name']])->shouldReturn(['profile' => ['first_name' => 'Jason']]);
    }

    public function it_allows_blacklisted_pluck()
    {
        $this->except('foo', 'profile')->shouldReturn([ 'bar' => null, 'zap' => [ 'bop' => true, 'pob' => false ] ]);
        $this->except(2)->shouldReturn($this->source);
    }

    public function it_allows_deep_blacklist_plucking_via_nested_arrays()
    {
        $this->except('profile', [ 'zap' => [ 'bop' ] ])->shouldReturn([ 'foo' => '1234', 'bar' => null, 'zap' => [ 'pob' => false ] ]);
    }

    public function it_provides_attribute_access_via_magic_call()
    {
        $this->callOnWrappedObject('__call', ['foo', []])->shouldReturn('1234');
        $this->callOnWrappedObject('__call', ['bar', []])->shouldReturn(null);
        $this->callOnWrappedObject('__call', ['baz', []])->shouldReturn(null);
    }

    public function it_disallows_attribute_mutation_via_magic_call()
    {
        $this->callOnWrappedObject('__call', ['foo', []])->shouldReturn('1234');
        $this->callOnWrappedObject('__call', ['foo', ['abc']])->shouldReturn('1234');
    }

    public function it_returns_expected_value_for_known_attribute_when_default_exists()
    {
        $this->get('foo', 'default')->shouldReturn('1234');
    }

    public function it_returns_default_on_get_for_unknown_attribute()
    {
        $this->get('invalid', 'default')->shouldReturn('default');
    }

    public function it_returns_evaluated_default_for_unknown_attribute()
    {
        $this->get('invalid', function () {
            return true;
        })->shouldReturn(true);
    }

    public function it_returns_attribute_keys()
    {
        $this->keys()->shouldReturn(['foo', 'bar', 'profile', 'zap']);
    }
}
