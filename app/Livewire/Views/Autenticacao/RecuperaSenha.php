<?php

namespace App\Livewire\Views\Autenticacao;

use App\Models\User;
use App\Notifications\EmailRecuperaSenhaNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class RecuperaSenha extends Component
{
  use Toast;
  public string $step = 'email';
  public ?string $email = null;
  public ?string $token = null;
  public ?string $tokenValidacao = null;
  public bool $isCompleted = false;
  public ?string $novaSenha = null;
  public ?string $confirmarNovaSenha = null;

  #[Layout('components.layouts.autenticacao')]
  #[Title('Recuperar Senha')]
  public function render(): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
  {
    return view('livewire.views.autenticacao.recupera-senha');
  }

  public function enviarEmail(): void {
    if ($usuario = User::query()->where('email', $this->email)->first()) {
      $this->token = rand(100000, 999999);
      $usuario->notify(new EmailRecuperaSenhaNotification($this->token));
      $this->step = 'token';
    }
  }

  public function validarToken(): void
  {
    $this->validate(
      rules: [
        'tokenValidacao' => [
          'required',
          'numeric',
          'digits:6',
        ]
      ],
      messages: [
        'tokenValidacao.required' => 'O código de validação é obrigatório.',
        'tokenValidacao.numeric'  => 'O código deve conter apenas números.',
        'tokenValidacao.digits'   => 'O código deve ter exatamente 6 dígitos.',
      ]
    );

    if (!hash_equals((string) $this->token, (string) $this->tokenValidacao)) {
      $this->addError('tokenValidacao', 'Código inválido. Verifique e tente novamente.');
      return;
    }

    $this->step = 'nova-senha';
  }

  public function alterarSenha(): void {
    $this->validate(rules: [
      'novaSenha' => 'required|min:8',
      'confirmarNovaSenha' => 'required|same:novaSenha',
    ]);

    $usuario = User::query()->where('email', $this->email)->first();
    $usuario->password = \Hash::make($this->novaSenha);
    $usuario->save();

    $this->success('Senha alterada com sucesso!', redirectTo: route('autenticacao.login'));
  }
}
