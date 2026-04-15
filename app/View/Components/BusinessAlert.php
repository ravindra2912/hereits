<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BusinessAlert extends Component
{
    public $businessDetails;

    /**
     * Create a new component instance.
     */
    public function __construct($businessDetails = array())
    {
        $this->businessDetails = $businessDetails;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.business-alert');
    }
}
