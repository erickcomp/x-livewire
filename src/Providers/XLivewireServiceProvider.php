<?php

namespace ErickComp\XLivewire\Providers;

use ErickComp\XLivewire\BladeComponents\XLivewire;
use ErickComp\XLivewire\Features\HasXAttributes;
use Illuminate\Support\Collection;
use function Livewire\on;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\ComponentHook;
use Livewire\Component as LivewireComponent;

class XLivewireServiceProvider extends ServiceProvider
{
    public function register() {}

    public function boot()
    {
        Blade::component('livewire', XLivewire::class, config('erickcomp-x-livewire.namespace', ''));
        $this->addComponentMountHook();
    }

    private function addComponentMountHook()
    {
        $this->app['livewire']->componentHook(new class () extends ComponentHook {
            static function provide()
            {
                on('mount', function ($component, $params, $key, $parent) {

                    if (!static::componentHasXAttributes($component)) {
                        return;
                    }

                    static::assignNonPublicAndNonStaticProps($component, $params);


                    //$component
                });
            }

            private static function componentHasXAttributes(LivewireComponent $component): bool
            {
                return \in_array(HasXAttributes::class, class_uses_recursive($component::class));
            }

            private static function assignNonPublicAndNonStaticProps(LivewireComponent $component, array $params)
            {
                $reflProperties = static::getNonStaticAndNonPublicReflectionProperties($component);

                // Assign all public component properties that have matching parameters.
                collect(
                    array_intersect_key(
                        $params,
                        static::getNonStaticAndNonPublicPropsExcludingLivewireComponentOnes($component),
                    ),
                )->each(function ($value, $property) use ($component, $reflProperties) {
                    $reflectionProperty = $reflProperties[$property];

                    $reflectionProperty->setAccessible(true); // Make it accessible
                    $reflectionProperty->setValue($component, $value);
                });


            }

            private static function getNonStaticAndNonPublicPropsExcludingLivewireComponentOnes(LivewireComponent $target): array
            {
                return static::getNonStaticAndNonPublicProps($target, function ($property) {
                    // Filter out any properties from the first-party Component class...
                    return $property->getDeclaringClass()->getName() !== LivewireComponent::class;
                });
            }

            private static function getNonStaticAndNonPublicProps(LivewireComponent $target, $filter = null): array
            {
                return static::getNonStaticAndNonPublicReflectionProperties($target)
                    ->filter($filter ?? fn() => true)
                    ->mapWithKeys(function ($property) use ($target) {
                        // Ensures typed property is initialized in PHP >=7.4, if so, return its value,
                        // if not initialized, return null (as expected in earlier PHP Versions)
                        if (method_exists($property, 'isInitialized') && !$property->isInitialized($target)) {
                            // If a type of `array` is given with no value, let's assume users want
                            // it prefilled with an empty array...
                            $value = (method_exists($property, 'getType') && $property->getType() && method_exists($property->getType(), 'getName') && $property->getType()->getName() === 'array')
                                ? [] : null;
                        } else {
                            $value = $property->getValue($target);
                        }

                        return [$property->getName() => $value];
                    })
                    ->all();
            }

            private static function getNonStaticAndNonPublicReflectionProperties(LivewireComponent $target): Collection
            {
                $propsHash = [];
                foreach ((new \ReflectionObject($target))->getProperties() as $reflProp) {
                    if ($reflProp->isPublic() || $reflProp->isStatic() || !$reflProp->isDefault()) {
                        continue;
                    }

                    $propsHash[$reflProp->getName()] = $reflProp;
                }

                return collect($propsHash);
            }

        });
    }
}
