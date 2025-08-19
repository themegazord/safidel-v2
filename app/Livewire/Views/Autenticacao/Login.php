<?php

namespace App\Livewire\Views\Autenticacao;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
  public string $email = '';
  public string $password = '';
  public bool $manterConectado = false;
  public bool $isLoading = false;

  protected $rules = [
    'email' => 'required|email',
    'password' => 'required',
  ];

  public function logar()
  {
    $this->validate();

    if (!auth()->attempt([
      'email' => $this->email,
      'password' => $this->password,
    ])) {
      $this->addError('email', 'Email ou senha inválidos');
    }

    Auth::login($usuario = \App\Models\User::where('email', $this->email)->first(), $this->manterConectado);

    if (!$usuario->isEmpresa()) {

    } else {
      $this->redirect(route('aplicacao.empresa.dashboard'), navigate: true);
    }

  }

  #[Layout('components.layouts.autenticacao')]
  #[Title('Login')]
  public function render()
  {
    return view('livewire.views.autenticacao.login');
  }
}
