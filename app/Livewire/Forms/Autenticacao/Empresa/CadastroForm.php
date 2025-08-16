<?php

namespace App\Livewire\Forms\Autenticacao\Empresa;

use Livewire\Attributes\Validate;
use Livewire\Form;

#[Validate(
  rule: [
    'razao_social' => ['required', 'string', 'max:255'],
    'nome_fantasia' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email'],
    'cnpj' => ['required', 'digits:14'],
    'telefone_comercial' => ['required', 'string', 'max:20'],
    'telefone_whatsapp' => ['required', 'string', 'max:20'],
    'telefone_contato' => ['required', 'string', 'max:20'],
  ],
  message: [
    'razao_social.required' => 'A razão social é obrigatória.',
    'razao_social.string' => 'A razão social deve ser um texto.',
    'razao_social.max' => 'A razão social não pode ter mais que :max caracteres.',

    'nome_fantasia.required' => 'O nome fantasia é obrigatório.',
    'nome_fantasia.string' => 'O nome fantasia deve ser um texto.',
    'nome_fantasia.max' => 'O nome fantasia não pode ter mais que :max caracteres.',

    'email.required' => 'O email é obrigatório.',
    'email.email' => 'Informe um email válido.',

    'cnpj.required' => 'O CNPJ é obrigatório.',
    'cnpj.digits' => 'O CNPJ deve ter exatamente 14 dígitos.',

    'telefone_comercial.required' => 'O telefone comercial é obrigatório.',
    'telefone_comercial.max' => 'O telefone comercial não pode ter mais que :max caracteres.',

    'telefone_whatsapp.required' => 'O telefone do WhatsApp é obrigatório.',
    'telefone_whatsapp.max' => 'O telefone do WhatsApp não pode ter mais que :max caracteres.',

    'telefone_contato.required' => 'O telefone de contato é obrigatório.',
    'telefone_contato.max' => 'O telefone de contato não pode ter mais que :max caracteres.',
  ]
)]
class CadastroForm extends Form
{
  public ?int $endereco_id = null;
  public ?string $razao_social = null;
  public ?string $nome_fantasia = null;

  public ?string $cnpj = null;
  public ?string $email = null;
  public ?string $telefone_comercial = null;
  public ?string $telefone_contato = null;
  public ?string $telefone_whatsapp = null;

  public function limpaCaracteresEspeciais(): void {
    $this->cnpj = $this->somenteNumeros($this->cnpj);
    $this->telefone_comercial = $this->somenteNumeros($this->telefone_comercial);
    $this->telefone_contato = $this->somenteNumeros($this->telefone_contato);
    $this->telefone_whatsapp = $this->somenteNumeros($this->telefone_whatsapp);
  }

  private function somenteNumeros($valor): string
  {
    return preg_replace('/[^0-9]/', '', $valor);
  }

}
