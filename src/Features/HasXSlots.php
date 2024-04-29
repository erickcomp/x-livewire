<?php

namespace ErickComp\XLivewire\Features;

use Illuminate\Contracts\View\View;

use Livewire\Attributes\Session;

use ErickComp\XLivewire\Aux\HasProtectedSessionProps;
use ErickComp\XLivewire\Wireables\XSlots;


trait HasXSlots
{
    use HasProtectedSessionProps;

    protected const X_LIVEWIRE_HAS_XSLOTS_LARAVEL_BLADE_SLOTS_DATA_VAR = '__laravel_slots';

    public XSlots $_xSlots;

    public function mountHasXSlots(XSlots $xSlots)
    {
        $prop = \property_exists($this, 'xSlots')
            ? 'xSlots'
            : '_xSlots';

        $this->{$prop} = $xSlots;

        if (!(new \ReflectionClass(static::class))->getProperty($prop)->isPublic()) {
            $this->nonPublicPropToSession($prop);
        }
    }

    public function bootHasXSlots()
    {
        $prop = \property_exists($this, 'xSlots')
            ? 'xSlots'
            : '_xSlots';

        if (!(new \ReflectionClass(static::class))->getProperty($prop)->isPublic()) {
            $this->nonPublicPropFromSession($prop, new XSlots([]));
        }
    }

    public function renderingHasXSlots(View $view, $data)
    {
        $prop = \property_exists($this, 'xSlots')
            ? 'xSlots'
            : '_xSlots';

        $slots = $this->{$prop}->getAll();

        $viewData = [];
        foreach ($slots as $varName => $slotObject) {
            if ($varName === '__default') {
                $viewData['slot'] = $slots['__default'];
            } else {
                $viewData[$varName] = $slots[$varName];
            }
        }

        return $view
            ->with($viewData)
            //->with('xSlots', $this->{$prop})
        ;
    }

    public static function createHasXSlotsProp(array $bladeComponentData)
    {
        return [
            'xSlots',
            new XSlots($bladeComponentData[static::X_LIVEWIRE_HAS_XSLOTS_LARAVEL_BLADE_SLOTS_DATA_VAR])
        ];
    }
}
