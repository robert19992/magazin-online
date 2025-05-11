<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PriceFormat extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public float $price,
        public string $currency = 'RON'
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.price-format');
    }

    public function formatted(): string
    {
        return number_format($this->price, 2, ',', '.') . ' ' . $this->currency;
    }
}
