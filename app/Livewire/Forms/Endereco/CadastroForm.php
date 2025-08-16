<?php

namespace App\Livewire\Forms\Endereco;

use Livewire\Attributes\Validate;
use Livewire\Form;

#[Validate(
  rule: [
    'logradouro' => ['required', 'string', 'max:255'],
    'bairro' => ['required', 'string', 'max:255'],
    'cidade' => ['required', 'string', 'max:255'],
    'uf' => ['required', 'string', 'size:2'],
    'cep' => ['required', 'digits:8'],
    'complemento' => ['nullable', 'string', 'max:255'],
    'numero' => ['nullable', 'string'],
  ],
  message: [
    'logradouro.required' => 'Street is required.',
    'logradouro.string' => 'Street must be a valid text.',
    'logradouro.max' => 'Street cannot exceed 255 characters.',

    'bairro.required' => 'Neighborhood is required.',
    'bairro.string' => 'Neighborhood must be a valid text.',
    'bairro.max' => 'Neighborhood cannot exceed 255 characters.',

    'cidade.required' => 'City is required.',
    'cidade.string' => 'City must be a valid text.',
    'cidade.max' => 'City cannot exceed 255 characters.',

    'uf.required' => 'State is required.',
    'uf.string' => 'State must be a valid text.',
    'uf.size' => 'State must have exactly 2 characters.',

    'cep.required' => 'Postal code is required.',
    'cep.digits' => 'Postal code must have exactly 8 digits.',

    'complemento.string' => 'Complement must be a valid text.',
    'complemento.max' => 'Complement cannot exceed 255 characters.',

    'numero.string' => 'Number must be a valid text.',
  ]
)]
class CadastroForm extends Form
{
  public ?string $logradouro = null;
  public ?string $bairro = null;
  public ?string $cidade = null;
  public ?string $uf = null;
  public ?string $cep = null;
  public ?string $complemento = null;
  public ?string $numero = null;

  public function limpaCaracteresEspeciais(): void {
    $this->cep = $this->somenteNumeros($this->cep);
  }

  private function somenteNumeros($valor): string
  {
    return preg_replace('/[^0-9]/', '', $valor);
  }

}
