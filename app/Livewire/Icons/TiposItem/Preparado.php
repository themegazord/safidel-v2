<?php

namespace App\Livewire\Icons\TiposItem;

use Livewire\Component;

class Preparado extends Component
{
  public ?string $width = null;
  public ?string $height = null;
  public ?string $estilo = null;
    public function render()
    {
        return <<<'HTML'
        <svg class="{{ $this->estilo }}" height='{{ $this->width }}' width='{{ $this->height }}' id="Layer_1" data-name="Layer 1"
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
              <path class="cls-1 {{ $this->estilo }}" d="M11,4V2H5V4H2V5H14V4ZM6,4V3h4V4ZM2,13H3v1H13V13h1V6H2Z"/>
            </svg>
        HTML;
    }
}
