<?php

namespace App\Livewire\Icons\FormaPagamento;

use Livewire\Component;

class VRRefeicao extends Component
{
  public function render()
  {
    return <<<'HTML'
      <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
      width="24px" height="24px" viewBox="0 0 256.000000 256.000000"
      preserveAspectRatio="xMidYMid meet">

      <g transform="translate(0.000000,256.000000) scale(0.100000,-0.100000)"
      fill="#53913C" stroke="none">
      <path d="M350 1490 l0 -910 930 0 930 0 0 910 0 910 -930 0 -930 0 0 -910z
      m1488 209 c105 -52 165 -152 166 -274 0 -101 -25 -166 -91 -234 l-54 -56 72
      -185 c39 -102 73 -191 76 -197 4 -10 -42 -13 -211 -13 -119 0 -216 2 -216 4 0
      2 -31 122 -70 266 -38 144 -70 266 -70 271 0 5 25 9 55 9 67 0 109 20 130 60
      22 42 19 66 -11 89 -22 17 -41 21 -103 21 l-77 0 -139 -360 -139 -360 -210 2
      -210 3 -138 375 c-76 206 -138 378 -138 383 0 4 78 7 174 7 l174 0 64 -192 64
      -191 69 269 c37 148 72 284 77 302 l9 32 343 0 343 0 61 -31z"/>
      </g>
      </svg>
    HTML;
  }
}
