<?php

namespace App\Livewire\Icons\TiposItem;

use Livewire\Component;

class Industrializado extends Component
{
  public ?string $width = null;
  public ?string $height = null;

  public function render()
  {
    return <<<'HTML'
        <svg class="cls-3" width="{{ $this->width }}" height="{{ $this->height }}" viewBox="0 0 32 32" enable-background="new 0 0 32 32"
                 version="1.1"
                 xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">

              <g id="Layer_1"/>

              <g id="Layer_2">

                <g>

                  <polyline class="cls-3" points="    2,10 2,6 6,6   " stroke-linecap="round"
                            stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>

                  <polyline class="cls-3" points="    30,10 30,6 26,6   " stroke-linecap="round"
                            stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>

                  <polyline class="cls-3" points="    2,22 2,26 6,26   " stroke-linecap="round"
                            stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>

                  <polyline class="cls-3" points="    30,22 30,26 26,26   " stroke-linecap="round"
                            stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>

                  <line class="cls-3" stroke-linecap="round" stroke-linejoin="round"
                        stroke-miterlimit="10" stroke-width="2" x1="6" x2="6" y1="9" y2="15"/>

                  <line class="cls-3" stroke-linecap="round" stroke-linejoin="round"
                        stroke-miterlimit="10" stroke-width="2" x1="11" x2="11" y1="9" y2="15"/>

                  <line class="cls-3" stroke-linecap="round" stroke-linejoin="round"
                        stroke-miterlimit="10" stroke-width="2" x1="26" x2="26" y1="9" y2="15"/>

                  <line class="cls-3" stroke-linecap="round" stroke-linejoin="round"
                        stroke-miterlimit="10" stroke-width="2" x1="21" x2="21" y1="9" y2="15"/>

                  <line class="cls-3" stroke-linecap="round" stroke-linejoin="round"
                        stroke-miterlimit="10" stroke-width="2" x1="16" x2="16" y1="9" y2="15"/>

                  <line class="cls-3" stroke-linecap="round" stroke-linejoin="round"
                        stroke-miterlimit="10" stroke-width="2" x1="2" x2="30" y1="18" y2="18"/>

                  <line class="cls-3" stroke-linecap="round" stroke-linejoin="round"
                        stroke-miterlimit="10" stroke-width="2" x1="6" x2="6" y1="21" y2="23"/>

                  <line class="cls-3" stroke-linecap="round" stroke-linejoin="round"
                        stroke-miterlimit="10" stroke-width="2" x1="11" x2="11" y1="21" y2="22"/>

                  <line class="cls-3" stroke-linecap="round" stroke-linejoin="round"
                        stroke-miterlimit="10" stroke-width="2" x1="26" x2="26" y1="21" y2="23"/>

                  <line class="cls-3" stroke-linecap="round" stroke-linejoin="round"
                        stroke-miterlimit="10" stroke-width="2" x1="21" x2="21" y1="21" y2="22"/>

                  <line class="cls-3" stroke-linecap="round" stroke-linejoin="round"
                        stroke-miterlimit="10" stroke-width="2" x1="16" x2="16" y1="21" y2="22"/>

                </g>

              </g>

            </svg>
        HTML;
  }
}
