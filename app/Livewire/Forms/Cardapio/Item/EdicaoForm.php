<?php

namespace App\Livewire\Forms\Cardapio\Item;

use Livewire\Attributes\Validate;
use Livewire\Form;

#[Validate(
  rule: [
    'external_id' => ['nullable', 'string', 'max:50'],
    'categoria_id' => ['required', 'integer'],
    'inativo' => ['boolean'],

    'nome' => ['required', 'string', 'max:80'],
    'tipo' => ['required', 'in:PRE,BEB,IND,PIZ'],
    'descricao' => ['nullable', 'string', 'max:255'],

    // PRE/BEB/IND: preço direto no item
    'preco' => ['required_if:tipo,PRE,BEB,IND', 'nullable', 'numeric', 'min:0'],

    // PIZ: preços por tamanho
    'precos' => ['required_if:tipo,PIZ', 'array'],
    'precos.*.tamanho_id' => ['required_if:tipo,PIZ', 'nullable', 'integer'],
    'precos.*.preco' => ['required_if:tipo,PIZ', 'nullable', 'numeric', 'min:0'],
    'precos.*.status' => ['nullable', 'boolean'],

    // Desconto
    'desconto' => ['boolean'],
    'valor_desconto' => ['nullable', 'numeric', 'min:0'],
    'porcentagem_desconto' => ['nullable', 'numeric', 'min:0', 'max:100'],

    // Campos específicos (apenas PRE)
    'qtde_pessoas' => ['nullable', 'integer', 'min:1', 'max:3'],
    'peso' => ['nullable', 'numeric', 'min:0'],
    'gramagem' => ['nullable', 'in:g,Kg'],

    // Bandeiras
    'eh_bebida' => ['boolean'],
    'imagem' => ['nullable', 'string'],

    // Classificação
    'classificacao' => ['array'],
    'classificacao.*.nome' => ['required', 'string'],
    'classificacao.*.status' => ['boolean'],

    // Complementos
    'contem_complemento' => ['boolean'],
    'grupo_complemento' => ['array'],
  ],
  message: [
    'required' => 'O campo é obrigatório.',
    'required_if' => 'O campo é obrigatório.',
    'string' => 'Informe um texto válido.',
    'integer' => 'Informe um número inteiro válido.',
    'numeric' => 'Informe um número válido.',
    'min' => 'O valor mínimo é :min.',
    'max' => 'O valor máximo é :max.',
    'in' => 'Valor selecionado é inválido.',
  ]
)]
class EdicaoForm extends Form
{
  public ?string $external_id = null;
  public ?int $categoria_id = null;
  public bool $inativo = false;

  public ?string $nome = null;
  public ?string $tipo = null; // PRE | BEB | IND | PIZ
  public ?string $descricao = null;

  // PRE/BEB/IND
  public ?float $preco = null;
  public bool $desconto = false;
  public ?float $valor_desconto = null;
  public ?float $porcentagem_desconto = null;

  // Complementos
  public bool $contem_complemento = false;
  public array $grupo_complemento = [];

  // Metadados de preço
  public ?string $tipo_preco = null; // fixo | preco_item

  // Específicos para PRE
  public ?int $qtde_pessoas = null;
  public ?float $peso = null;
  public ?string $gramagem = null; // g | Kg

  // Bandeiras
  public bool $eh_bebida = false;

  // Mídia
  public ?string $imagem = null;

  // Classificação (slugs canônicos, mesma ordem usada no Blade)
  public array $classificacao = [
    ['nome' => 'vegetariano',      'status' => false],
    ['nome' => 'vegano',           'status' => false],
    ['nome' => 'organico',         'status' => false],
    ['nome' => 'sem-acucar',       'status' => false],
    ['nome' => 'zero_lactose',     'status' => false],
    ['nome' => 'bebida_gelada',    'status' => false],
    ['nome' => 'bebida_alcolica',  'status' => false],
    ['nome' => 'bebida_natural',   'status' => false],
    ['nome' => 'zero_lactose',     'status' => false], // restrição para bebidas
    ['nome' => 'bebida_diet',      'status' => false],
  ];

  // PIZ
  public array $precos = [];

  public function classificacaoLimpa(): array
  {
    return array_values(array_map(
      static fn(array $cls) => $cls['nome'],
      array_filter($this->classificacao, static fn(array $cls) => ($cls['status'] ?? false) === true)
    ));
  }

  /**
   * Normaliza campos conforme o tipo do item e desconto.
   * Chamar antes de persistir.
   */
  public function normalizarCampos(): void
  {
    // Se item não for PRE, zera campos específicos
    if (!in_array($this->tipo, ['PRE'], true)) {
      $this->qtde_pessoas = null;
      $this->peso = null;
      $this->gramagem = null;
    }

    // Se item for PIZ, limpa preço direto
    if ($this->tipo === 'PIZ') {
      $this->preco = null;
      $this->desconto = false;
      $this->valor_desconto = null;
      $this->porcentagem_desconto = null;
    }

    // Se desconto desativado, zera campos
    if ($this->desconto === false) {
      $this->valor_desconto = null;
      $this->porcentagem_desconto = null;
    }

    // Consistência entre valor e porcentagem de desconto quando houver preço
    if ($this->desconto && $this->preco !== null) {
      if ($this->valor_desconto !== null && $this->valor_desconto >= 0 && $this->valor_desconto <= $this->preco) {
        $this->porcentagem_desconto = round(
          100 - (($this->valor_desconto * 100) / max($this->preco, 0.0001)),
          2
        );
      } elseif ($this->porcentagem_desconto !== null) {
        $this->porcentagem_desconto = max(0, min(100, $this->porcentagem_desconto));
        $this->valor_desconto = round(
          $this->preco - (($this->porcentagem_desconto * $this->preco) / 100),
          2
        );
      }
    }
  }
}
