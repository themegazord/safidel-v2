<?php

namespace App\Livewire\Icons\Pedidos;

use Livewire\Component;

class Mesa extends Component
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
          <svg fill="{{ $fill }}" width="30px" height="30px" viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M10.585938 11L0.5859375 21L49.414062 21L39.414062 11L10.585938 11 z M 0 22L0 28L50 28L50 22L0 22 z M 3 29L3 50L9 50L9 29L3 29 z M 11 29L11 43L17 43L17 29L11 29 z M 33 29L33 43L39 43L39 29L33 29 z M 41 29L41 50L47 50L47 29L41 29 z"/></svg>
        </x-slot:trigger>
        <x-slot:content class="bg-slate-700 text-white text-sm">
          Atendimento em mesa
        </x-slot:content>
      </x-popover>
    HTML;
  }
}
