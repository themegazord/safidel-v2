<?php

namespace App\Livewire\Icons\Pedidos;

use Livewire\Component;

class Delivery extends Component
{
  public ?string $fill = null;
  public function mount(?string $fill): void {
    $this->fill = $fill;
  }
  public function render()
  {
    return <<<'HTML'
      <x-popover>
        <x-slot:trigger>
              <svg width="30px" height="30px" viewBox="0 0 32 32" enable-background="new 0 0 32 32" id="_x3C_Layer_x3E_" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">

              <g id="motorbike_x2C__motorcycle_x2C__scooter">

              <g id="XMLID_2449_">

              <g id="XMLID_2580_">

              <path d="     M19.791,4.999C19.926,5.306,20,5.644,20,6c0,1.38-1.12,2.5-2.5,2.5c-0.593,0-1.138-0.207-1.566-0.552" fill="{{ $fill }}" id="XMLID_2446_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              <path d="     M20.5,24.5v-5.18c0-0.94-0.65-1.75-1.57-1.95l-4.92-1.09l0.52-1.771" fill="{{ $fill }}" id="XMLID_2591_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              <path d="     M14.208,11.5l3.042,3.21c0.16,0.15,0.36,0.25,0.57,0.28l4.06,0.5c0.04,0.01,0.08,0.01,0.12,0.01c0.5,0,0.93-0.37,0.99-0.88     c0.069-0.54-0.32-1.04-0.87-1.11l-3.61-0.45l-2.37-2.8c-0.89-1.06-2.38-1.53-3.659-0.99C9.5,10.54,7.25,19.5,12.05,19.5     c1.83,0,5.45,0.79,5.45,0.79l0.73,5.23c0.149,1.13,1.119,1.98,2.27,1.98" fill="{{ $fill }}" id="XMLID_2584_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              <path d="     M17.5,5h3c0-1.38-1.34-2.5-3-2.5H17c-1.38,0-2.5,1.12-2.5,2.5v0.5c0,0.37,0.11,0.7,0.28,1H16C16.83,6.5,17.5,5.83,17.5,5z" fill="{{ $fill }}" id="XMLID_2633_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              </g>

              <g id="XMLID_2571_">

              <path d="     M11.5,27.5c0,1.66-1.34,3-3,3c-1.112,0-2.08-0.602-2.599-1.498" fill="{{ $fill }}" id="XMLID_2564_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              <path d="     M29.416,25.19c0.663,0.549,1.084,1.379,1.084,2.31c0,1.66-1.34,3-3,3c-1.096,0-2.053-0.584-2.576-1.458" fill="{{ $fill }}" id="XMLID_2561_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              <line fill="{{ $fill }}" id="XMLID_2642_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="18.5" x2="14.5" y1="27.5" y2="27.5"/>

              <path d="     M14.5,21.5v3c0,1.66,1.34,3,3,3h-15l0.76-2.65c0.32-1.126,0.953-2.102,1.789-2.841" fill="{{ $fill }}" id="XMLID_2655_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              <line fill="{{ $fill }}" id="XMLID_2503_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="10.5" x2="6.5" y1="24.5" y2="24.5"/>

              <path d="     M25.4,27.5h-1.9c0-2.21,1.79-4,4-4c1.01,0,1.94,0.38,2.64,1l-1.979,1.89C27.42,27.1,26.43,27.5,25.4,27.5z" fill="{{ $fill }}" id="XMLID_2678_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              <line fill="{{ $fill }}" id="XMLID_2593_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="2.5" x2="7.5" y1="10.5" y2="10.5"/>

              <line fill="{{ $fill }}" id="XMLID_2592_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="1.5" x2="6.5" y1="4.5" y2="4.5"/>

              <line fill="{{ $fill }}" id="XMLID_2497_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="4.5" x2="12.5" y1="2.5" y2="2.5"/>

              <path d="     M26.5,14.5H26c-0.83,0-1.5-0.67-1.5-1.5s0.67-1.5,1.5-1.5h0.5c0.55,0,1,0.45,1,1v1C27.5,14.05,27.05,14.5,26.5,14.5z" fill="{{ $fill }}" id="XMLID_2582_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              <path d="     M18.5,27.5h2c1.104,0,2-0.896,2-2v-8" fill="{{ $fill }}" id="XMLID_2581_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              <path d="     M23.012,14.557c0.918,0.206,1.645,0.957,1.788,1.923l1.136,7.337" fill="{{ $fill }}" id="XMLID_2322_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              </g>

              <path d="    M15,21.5H7.5c-0.55,0-1-0.45-1-1s0.45-1,1-1h4" fill="{{ $fill }}" id="XMLID_2590_" stroke="#263238" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"/>

              </g>

              </g>

              </svg>
        </x-slot:trigger>
        <x-slot:content class="bg-slate-700 text-white text-sm">
          Delivery
        </x-slot:content>
      </x-popover>
      HTML;
  }
}
