<?php

namespace ErickComp\XLivewire\Features;

use Illuminate\Contracts\View\View;

use Illuminate\View\ComponentAttributeBag;

use ErickComp\XLivewire\Aux\HasProtectedSessionProps;
use ErickComp\XLivewire\Wireables\XAttributes;

trait HasXAttributes
{
    use HasProtectedSessionProps;
    protected const X_LIVEWIRE_HAS_XATTRIBUTES_LARAVEL_BLADE_ATTRIBUTES_DATA_VAR = 'attributes';

    public XAttributes $_xAttributes;

    public function mountHasXAttributes(XAttributes $xAttributes)
    {
        $prop = \property_exists($this, 'xAttributes')
            ? 'xAttributes'
            : '_xAttributes';

        $this->{$prop} = $xAttributes;

        if (!(new \ReflectionClass(static::class))->getProperty($prop)->isPublic()) {
            $this->nonPublicPropToSession($prop);
        }
    }

    public function bootHasXAttributes()
    {
        $prop = \property_exists($this, 'xAttributes')
            ? 'xAttributes'
            : '_xAttributes';

        if (!(new \ReflectionClass(static::class))->getProperty($prop)->isPublic()) {
            $this->nonPublicPropFromSession($prop, new xAttributes(new ComponentAttributeBag()));
        }
    }

    public function renderingHasXAttributes(View $view, $data)
    {
        $prop = \property_exists($this, 'xAttributes')
            ? 'xAttributes'
            : '_xAttributes';

        return $view
            //->with('xAttributes', $this->{$prop})
            ->with('attributes', $this->{$prop}->get())
        ;
    }

    public static function createHasXAttributesProp(array $bladeComponentData)
    {
        return [
            'xAttributes',
            new XAttributes($bladeComponentData[static::X_LIVEWIRE_HAS_XATTRIBUTES_LARAVEL_BLADE_ATTRIBUTES_DATA_VAR])
        ];
    }
}
