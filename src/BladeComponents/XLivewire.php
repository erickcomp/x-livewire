<?php

namespace ErickComp\XLivewire\BladeComponents;

use Illuminate\Contracts\View\View as LaravelView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Illuminate\View\Component as BladeComponent;
use Illuminate\View\ComponentAttributeBag;


use Livewire\Mechanisms\ComponentRegistry as LivewireComponentRegistry;
use Livewire\Component as LivewireComponent;

use ErickComp\XLivewire\Features\HasXSlots;
use ErickComp\XLivewire\Features\HasXAttributes;

class XLivewire extends BladeComponent
{
    /**
     * Create a new component instance.
     */

    //protected const LIVEWIRE_COMPONENT_NAME = 'dependent-select';
    //public readonly string $livewireComponent;
    //private string $livewireComponentClass;
    //private array $featuresProps = [];
    protected const LARAVEL_BLADE_SLOTS_DATA_VAR = '__laravel_slots';
    public function __construct(protected LivewireComponentRegistry $livewireComponentRegistry)
    {
        //$this->livewireComponentClass = $this->livewireComponentRegistry->getClass($livewireComponent);
        // $this->livewireComponent = $livewireComponent;

        // $componentClassTraits = \class_uses_recursive($this->livewireComponentClass);


        // $this->hasXSlots = \in_array(HasXSlots::class, $componentClassTraits);
        // $this->hasXAttributes = \in_array(HasXAttributes::class, $componentClassTraits);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): LaravelView|\Closure|string
    {
        return function (array $data) {

            static $livewireClassFeatures = [];

            /** @var ComponentAttributeBag */
            $attributes = $data['attributes'];

            $livewireComponentsAttrs = \array_keys($attributes->whereStartsWith('livewire:')->getAttributes());
            if (count($livewireComponentsAttrs) === 0) {
                throw new \LogicException("You MUST specify the livewire component you want to use using the following format: livewire:<component-name>");
            }

            if (count($livewireComponentsAttrs) > 1) {
                throw new \LogicException("You can only specify one livewire component");
            }

            $attributes = $attributes->whereDoesntStartWith('livewire:');
            $data['attributes'] = $attributes;

            $livewireComponentName = \substr($livewireComponentsAttrs[0], \strlen('livewire:'));
            $livewireComponentClass = $this->livewireComponentRegistry->getClass($livewireComponentName);

            if (!\array_key_exists($livewireComponentClass, $livewireClassFeatures)) {
                $availableFeatures = $this->featuresTraits();
                $classTraits = class_uses_recursive($livewireComponentClass);

                $livewireClassFeatures[$livewireComponentClass] = \array_intersect($classTraits, $availableFeatures);
            }

            $featuresPropsNames = [];
            $featuresBladeProps = [];
            $featuresData = [];

            foreach ($livewireClassFeatures[$livewireComponentClass] as $feature) {
                $propNameMethod = "propName" . Str::studly(\class_basename($feature));

                if (\method_exists($livewireComponentClass, $propNameMethod)) {
                    $featuresPropsNames[] = $livewireComponentClass::$propNameMethod();
                }
            }

            $this->extractLivewireComponentPublicPropsFromWrapperData($data, $livewireComponentClass, $featuresPropsNames);

            foreach ($livewireClassFeatures[$livewireComponentClass] as $feature) {
                $featureCreatePropMethod = 'create' . Str::studly(\class_basename($feature)) . 'Prop';

                if (!\method_exists($livewireComponentClass, $featureCreatePropMethod)) {
                    //throw new \LogicException("Trait [$feature] MUST implement method [$featureCreatePropMethod]");
                    continue;
                }

                [$featurePropName, $featurePropValue] = \call_user_func([$livewireComponentClass, $featureCreatePropMethod], $data);

                $featuresBladeProps[] = ':' . Str::kebab($featurePropName) . '="$' . $featurePropName . '"';
                $featuresData[$featurePropName] = $featurePropValue;
                $featuresPropsNames[] = $livewireComponentClass::{"propName" . Str::studly(\class_basename($feature))}();
            }

            $livewireData = [...$data, ...$featuresData];

            $classProps = \array_map(
                fn(\ReflectionProperty $rProp) => $rProp->getName(),
                static::getLivewireComponentProperties($livewireComponentClass),
            );

            $livewireProps = \array_merge(
                array_filter(
                    \array_map(
                        function (string $propName) use ($data) {
                            if (!\array_key_exists($propName, $data)) {
                                return null;
                            }
                            return ':' . Str::kebab($propName) . '="$' . $propName . '"';
                        },
                        $classProps,
                    ),
                    fn($elem) => $elem !== null
                ),
                $featuresBladeProps,
            );

            // Teste
            //$livewireProps[] = ':attributes="$xAttributesProcessed"';
            //$livewireProps[] = ':_x-attributes-processed="$xAttributesProcessed"';

            //$livewireData['attributes'] = $data['xAttributesProcessed'];s

            $livewireComponentStr = "<livewire:$livewireComponentName " . \implode(' ', $livewireProps) . ' />';

            $rendered = Blade::render(
                $livewireComponentStr,
                $livewireData,
            );

            return $rendered;

            //return '<livewire:dependent-select :$name :$dependsOn :$optionsProvider :x-attributes="$xAttributes" :x-slots="$xSlots"/>';
            //return '<livewire:dependent-select :$name :$dependsOn :$optionsProvider :x-slots="$__laravel_slots"/>';
        };
    }

    private function featuresTraits(): array
    {
        static $traits = null;

        if ($traits === null) {
            $featuresFiles = \glob(__DIR__ . '/../Features/*.php');

            if (!\is_array($featuresFiles)) {
                $featuresFiles = [];
            }

            $ns = \substr(__NAMESPACE__, 0, \strrpos(__NAMESPACE__, '\\') + 1) . 'Features\\';
            $traits = \array_map(fn($item) => $ns . \substr(\basename($item), 0, -4), $featuresFiles);
        }

        return $traits;
    }

    private function extractLivewireComponentPublicPropsFromWrapperData(
        array &$data,
        string $componentClassName,
        array $xLivewireFeaturesPropsNames,
    ): void {
        $classProps = \array_map(
            fn(\ReflectionProperty $rProp) => $rProp->getName(),
            static::getLivewireComponentProperties($componentClassName),
        );

        $componentPropsNames = \array_diff(
            $classProps,
            $xLivewireFeaturesPropsNames,
        );

        /** @var ComponentAttributeBag */
        $attributes = $data['attributes'];
        $componentPropsValues = $attributes->onlyProps($componentPropsNames);
        $attributes = $attributes->exceptProps($componentPropsNames);

        $componentPropsValuesCamelCased = [];
        foreach ($componentPropsValues->all() as $propNameKebabCased => $propVal) {
            $componentPropsValuesCamelCased[Str::camel($propNameKebabCased)] = $propVal;
        }

        $data = [
            ...$data,
            ...['attributes' => $attributes],
            //...$componentPropsValues->all(),
            ...$componentPropsValuesCamelCased,
        ];
    }

    public static function getLivewireComponentProperties(string $componentClassName): array
    {
        return \array_filter(
            (new \ReflectionClass($componentClassName))->getProperties(),
            function (\ReflectionProperty $property) {
                return !$property->isStatic() && $property->getDeclaringClass()->getName() !== LivewireComponent::class;
            }
        );
    }

}
