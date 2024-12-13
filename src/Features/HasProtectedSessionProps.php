<?php

namespace ErickComp\XLivewire\Features;

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

    public function mountHasProtectedSessionProps()
    {
        $debug = \func_get_args();

        $todoMsg = 'TODO: [' . __FILE__ . ']:' . __LINE__ . ' - '
            . 'Get all non-public and non-static vars and put them on session for using when hydrating back on subsequent requests';

        throw new \Exception($todoMsg);

    }

    public function mountedHasProtectedSessionProps()
    {
        $debug = \func_get_args();
    }
}
