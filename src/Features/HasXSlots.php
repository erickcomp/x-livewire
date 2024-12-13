<?php

namespace ErickComp\XLivewire\Features;

use ErickComp\XLivewire\Wireables\XSlots;
use Illuminate\Contracts\View\View;

trait HasXSlots
{
    use HasProtectedSessionProps;

    protected const X_LIVEWIRE_HAS_XSLOTS_LARAVEL_BLADE_SLOTS_DATA_VAR = '__laravel_slots';

    public XSlots $_xSlots;

    public static function propNameHasXSlots(): string
    {
        return \property_exists(static::class, 'xSlots')
            ? 'xSlots'
            : '_xSlots';
    }

    public static function createHasXSlotsProp(array $bladeComponentData)
    {
        return [
            'xSlots',
            new XSlots($bladeComponentData[static::X_LIVEWIRE_HAS_XSLOTS_LARAVEL_BLADE_SLOTS_DATA_VAR]),
        ];
    }

    public function mountHasXSlots(XSlots $xSlots)
    {
        $prop = $this->propNameHasXSlots();

        $this->{$prop} = $xSlots;

        if (!(new \ReflectionClass(static::class))->getProperty($prop)->isPublic()) {
            $this->nonPublicPropToSession($prop);
        }
    }

    public function bootHasXSlots()
    {
        $prop = $this->propNameHasXSlots();

        if (!(new \ReflectionClass(static::class))->getProperty($prop)->isPublic()) {
            $this->nonPublicPropFromSession($prop, new XSlots([]));
        }
    }

    public function renderingHasXSlots(View $view, $data)
    {
        $prop = $this->propNameHasXSlots();

        $slots = $this->{$prop}->getAll();

        $slotsData = [];
        foreach ($slots as $varName => $slotObject) {
            if ($varName === '__default') {
                $slotsData['slot'] = $slots['__default'];
            } else {
                $slotsData[$varName] = $slots[$varName];
            }
        }

        return $view
            ->with($slotsData)
            //->with('xSlots', $this->{$prop})
        ;
    }
}
