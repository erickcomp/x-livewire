<?php

namespace ErickComp\XLivewire\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

use ErickComp\XLivewire\BladeComponents\XLivewire;

class XLivewireServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot()
    {
        Blade::component('livewire', XLivewire::class, config('erickcomp-x-livewire.namespace', ''));
    }
}
