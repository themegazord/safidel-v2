<?php

namespace App\Livewire\Forms\Cardapio\Item;

use Livewire\Attributes\Validate;
use Livewire\Form;

class CadastroForm extends Form
{
  // Domínio
  private const TIPO_PIZZA = 'PIZ';
  private const TIPOS_COM_PRECO_STR = 'PRE,BEB,IND';

  // Regras condicionais
  private const REQ_IF_PRECO = 'required_if:tipo,' . self::TIPOS_COM_PRECO_STR; // PRE, BEB, IND
  private const REQ_IF_PIZZA = 'required_if:tipo,' . self::TIPO_PIZZA;


  // Defaults
  private const DEFAULT_CLASSIFICACAO = [
    ['nome' => 'vegetariano', 'status' => false],
    ['nome' => 'vegano', 'status' => false],
    ['nome' => 'organico', 'status' => false],
    ['nome' => 'sem_acucar', 'status' => false],
    ['nome' => 'zero_lactose', 'status' => false],
    ['nome' => 'bebida_gelada', 'status' => false],
    ['nome' => 'bebida_alcoolica', 'status' => false],
    ['nome' => 'bebida_natural', 'status' => false],
    ['nome' => 'bebida_zero_lactose', 'status' => false],
    ['nome' => 'bebida_diet_zero', 'status' => false],
  ];

  // Validações
  private const RULES = [
    'nome' => ['required'],
    'tipo' => ['required'],
    'preco' => [self::REQ_IF_PRECO],
    'precos' => [self::REQ_IF_PIZZA],
  ];


  private const MESSAGES = [
    'nome.required' => 'O campo nome é obrigatório',
    'tipo.required' => 'O campo tipo é obrigatório',
    'preco.required_if' => 'O campo preço é obrigatório para itens preparados, bebidas e industrializados',
    'precos.required_if' => 'O campo de preços por tamanho é obrigatório para pizzas',
  ];

  #[Validate(rule: self::RULES, message: self::MESSAGES)]
  public ?int $categoria_id = null;

  public ?string $nome = null;
  public ?string $imagem = null;
  public ?string $tipo = null;
  public ?int $external_id = null;
  public ?string $descricao = null;
  public ?float $preco = null;

  public bool $desconto = false;
  public ?float $valor_desconto = null;
  public ?float $porcentagem_desconto = null;

  public bool $contem_complemento = false;
  public array $grupo_complemento = [];

  public ?string $tipo_preco = null;
  public ?int $qtde_pessoas = null;
  public ?string $peso = null;
  public ?string $gramagem = 'g';
  public bool $eh_bebida = false;

  /**
   * @var array<int, array{nome:string,status:bool}>
   */
  public array $classificacao = self::DEFAULT_CLASSIFICACAO;

  /**
   * Para pizzas (tipo = PIZ): lista de preços por tamanho.
   * Ajuste a estrutura conforme seu domínio (ex.: tamanho_id + preco).
   * @var array<int, array{
   *   tamanho_id?: int,
   *   preco?: float|int
   * }>
   */
  public array $precos = [];

  public function classificacaoLimpa(): array
  {
    return array_map(
      fn(array $classificacao) => $classificacao['nome'],
      array_filter($this->classificacao, fn(array $classificacao) => $classificacao['status'])
    );
  }
}
