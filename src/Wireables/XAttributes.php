<?php

namespace ErickComp\XLivewire\Wireables;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Wireable;

class XAttributes implements Wireable, Htmlable
{
    /** @var ComponentAttributeBag */
    public function __construct(private ComponentAttributeBag $attributes)
    {
    }

    public static function fromLivewire($value)
    {
        return new static(new ComponentAttributeBag($value));
    }

    public function toLivewire()
    {
        return $this->attributes->getAttributes();
    }

    public function toHtml()
    {
        return $this->attributes->toHtml();
    }

    public function __toString()
    {
        return $this->attributes->__toString();
    }

    public function get()
    {
        return $this->attributes;
    }
}
