<?php

namespace ErickComp\XLivewire\Features;

use ErickComp\XLivewire\Wireables\XAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\View\ComponentAttributeBag;

trait HasXAttributes
{
    use HasProtectedSessionProps;
    protected const X_LIVEWIRE_HAS_XATTRIBUTES_LARAVEL_BLADE_ATTRIBUTES_DATA_VAR = 'attributes';

    public XAttributes $_xAttributes;

    public static function propNameHasXAttributes(): string
    {
        return \property_exists(static::class, 'xAttributes')
            ? 'xAttributes'
            : '_xAttributes';
    }

    public static function createHasXAttributesProp(array $bladeComponentData)
    {
        return [
            'xAttributes',
            new XAttributes($bladeComponentData[static::X_LIVEWIRE_HAS_XATTRIBUTES_LARAVEL_BLADE_ATTRIBUTES_DATA_VAR]),
        ];
    }

    public function mountHasXAttributes(XAttributes $xAttributes)
    {
        $prop = $this->propNameHasXAttributes();

        $this->{$prop} = $xAttributes;

        if (!(new \ReflectionClass(static::class))->getProperty($prop)->isPublic()) {
            $this->nonPublicPropToSession($prop);
        }


    }

    public function bootHasXAttributes()
    {
        $prop = $this->propNameHasXAttributes();

        if (!(new \ReflectionClass(static::class))->getProperty($prop)->isPublic()) {
            $this->nonPublicPropFromSession($prop, new xAttributes(new ComponentAttributeBag()));
        }
    }

    public function renderingHasXAttributes(View $view, $data)
    {
        $prop = $this->propNameHasXAttributes();

        return $view
            //->with('xAttributes', $this->{$prop}->get())
            ->with('attributes', $this->{$prop})
        ;
    }
}
