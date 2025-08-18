<?php

namespace App\Livewire\Forms\Cardapio\Categoria;

use Livewire\Attributes\Validate;
use Livewire\Form;

#[Validate(rule: [
  'tipo' => ['required'],
  'nome' => ['required', 'max:50'],
  'tamanho' => ['required_if:tipo,P'],
  'massa' => ['required_if:tipo,P'],
  'borda' => ['required_if:tipo,P'],
  'tamanho.*.external_id' => ['required_if:tipo,P'],
  'tamanho.*.nome' => ['required_if:tipo,P', 'max:50'],
  'tamanho.*.qtde_pedacos' => ['required_if:tipo,P'],
  'tamanho.*.qtde_sabores' => ['required_if:tipo,P'],
  'massa.*.nome' => ['required_if:tipo,P', 'max:50'],
  'massa.*.preco' => ['required_if:tipo,P'],
  'borda.*.nome' => ['required_if:tipo,P', 'max:50'],
  'borda.*.preco' => ['required_if:tipo,P'],
], message: [
  'required' => 'Campo obrigatório',
  'required_if' => 'Campo obrigatório',
  'nome.max' => 'O nome da categoria deve conter no máximo :max caracteres',
  'tamanho.*.nome' => 'O nome do tamanho deve conter no máximo :max caracteres',
  'massa.*.nome' => 'O nome da massa deve conter no máximo :max caracteres',
  'borda.*.nome' => 'O nome da borda deve conter no máximo :max caracteres',
])]
class CadastroForm extends Form
{
  public ?int $cardapio_id = null;
  public ?string $tipo = null;
  public ?string $nome = null;
  public array $tamanho = [
    [
      'external_id' => null,
      'nome' => '',
      'qtde_pedacos' => 0,
      'qtde_sabores' => [1],
    ]
  ];
  public array $massa = [
    [
      'nome' => '',
      'preco' => 0
    ]
  ];
  public array $borda = [
    [
      'nome' => '',
      'preco' => 0
    ]
  ];
}
