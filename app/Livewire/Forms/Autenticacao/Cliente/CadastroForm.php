<?php

namespace App\Livewire\Forms\Autenticacao\Cliente;

use Livewire\Attributes\Validate;
use Livewire\Form;

#[Validate(
  rule: [
    'nome' => ['required', 'string', 'max:255'],
    'cpf' => ['required', 'digits:11'],
    'email' => ['required', 'email'],
    'telefone' => ['required', 'string', 'max:20'],
    'data_nascimento' => ['required', 'date'],
  ],
  message: [
    'nome.required' => 'O campo nome é obrigatório.',
    'nome.string' => 'O campo nome deve ser um texto.',
    'nome.max' => 'O campo nome não pode ter mais que :max caracteres.',

    'cpf.required' => 'O CPF é obrigatório.',
    'cpf.digits' => 'O CPF deve ter exatamente 11 dígitos.',

    'email.required' => 'O email é obrigatório.',
    'email.email' => 'Informe um email válido.',

    'telefone.required' => 'O telefone é obrigatório.',
    'telefone.max' => 'O telefone não pode ter mais que :max caracteres.',

    'data_nascimento.required' => 'A data de nascimento é obrigatória.',
    'data_nascimento.date' => 'Informe uma data válida.',
  ]
)]
class CadastroForm extends Form
{
  public ?int $endereco_id = null;
  public ?string $nome = null;
  public ?string $email = null;
  public ?string $cpf = null;
  public ?string $telefone = null;
  public ?string $data_nascimento = null;

  public function limpaCaracteresEspeciais(): void {
    $this->cpf = $this->somenteNumeros($this->cpf);
    $this->telefone = $this->somenteNumeros($this->telefone);
  }

  private function somenteNumeros($valor): string
  {
    return preg_replace('/[^0-9]/', '', $valor);
  }

}

