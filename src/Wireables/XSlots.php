<?php

namespace ErickComp\XLivewire\Wireables;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Illuminate\View\ComponentSlot;

class XSlots implements \Livewire\Wireable
{
    /** @var ComponentSlot[] */
    private array $slots;
    public function __construct(array $slots)
    {
        $this->slots = $slots;
    }

    public static function fromLivewire($value)
    {
        $slots = \array_map(function ($slotData) {
            return new ComponentSlot(
                \gzuncompress(\base64_decode($slotData['content'])),
                $slotData['attributes']
            );
        }, $value);


        return new static($slots);
    }

    public function toLivewire()
    {
        return \array_map(
            function (ComponentSlot $slot) {
                return [
                    'attributes' => $slot->attributes->getAttributes(),
                    'content' => \base64_encode(\gzcompress($slot->toHtml()))
                ];
            },
            $this->slots
        );
    }

    public function get(string $slotName): Htmlable
    {
        return $this->slots[Str::camel($slotName)] ?? new class implements Htmlable {
            public function toHtml()
            {
                return '';
            }
        };
    }

    public function getDefault(): Htmlable
    {
        return $this->slots['__default'] ?? new class implements Htmlable {
            public function toHtml()
            {
                return '';
            }
        };
    }

    public function getAll(): array
    {
        return $this->slots;
    }
}
