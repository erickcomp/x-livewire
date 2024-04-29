<?php

namespace ErickComp\XLivewire\Aux;

use Illuminate\Support\Facades\Session;

trait HasProtectedSessionProps
{
    abstract function getId();
    abstract function getName();

    protected function nonPublicPropToSession(string $propName)
    {
        Session::put($this->nonPublicPropSessionKey($propName), $this->{$propName});
    }

    protected function nonPublicPropFromSession(string $propName, $default)
    {
        $this->{$propName} = Session::exists($this->nonPublicPropSessionKey($propName))
            ? Session::get($this->nonPublicPropSessionKey($propName))
            : $default;
    }

    protected function nonPublicPropSessionKey(string $propName): string
    {
        return 'x-lw-' . $this->getName() . "-$propName-" . $this->getId();
    }
}
