<?php

namespace App\Livewire\Forms\Cardapio;

use Livewire\Attributes\Validate;
use Livewire\Form;

#[Validate(rule: [
  'nome' => ['required', 'max:50'],
  'descricao' => ['required', 'max:255'],
  'dias_funcionamento' => ['required'],
  'tipo_funcionamento' => ['required'],
], message: [
  'required' => "Campo obrigatorio.",
  'nome.max' => "Nome deve ter 50 caracteres.",
  'descricao.max' => "Descricao deve ter 255 caracteres.",
])]
class CadastroForm extends Form
{
  public ?int $empresa_id = null;
  public ?string $nome = null;
  public ?string $descricao = null;
  public array $dias_funcionamento = [];
  public ?string $tipo_funcionamento = null;
}
