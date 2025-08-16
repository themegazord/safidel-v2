<?php

namespace App\Livewire\Views\Aplicacao\Empresa;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{

  #[Layout('components.layouts.empresa')]
  #[Title('Dashboard')]
  public function render()
  {
    return view('livewire.views.aplicacao.empresa.dashboard');
  }
}
