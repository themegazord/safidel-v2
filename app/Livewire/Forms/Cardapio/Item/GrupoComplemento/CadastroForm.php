<?php

namespace App\Livewire\Forms\Cardapio\Item\GrupoComplemento;

use Livewire\Attributes\Validate;
use Livewire\Form;

#[Validate(
  rule: [
    'nome' => ['required', 'string', 'max:80'],
    'obrigatoriedade' => ['boolean'],

    // Quantidades
    'qtd_minima' => ['nullable', 'integer', 'min:0'],
    'qtd_maxima' => ['nullable', 'integer', 'min:0'],

    // Complementos
    'complementos' => ['array'],
    'complementos.*.nome' => ['required', 'string', 'max:80'],
    'complementos.*.descricao' => ['nullable', 'string', 'max:255'],
    'complementos.*.external_id' => ['nullable', 'string', 'max:50'],
    'complementos.*.preco' => ['nullable', 'numeric', 'min:0'],
    'complementos.*.status' => ['nullable', 'in:0,1'],
  ],
  message: [
    'nome.required' => 'Nome é obrigatório.',
    'complementos.*.nome' => 'Nome do complemento é obrigatório.',
    'string' => 'Informe um texto válido.',
    'max' => 'O campo não pode ter mais que :max caracteres.',
    'boolean' => 'Informe um valor verdadeiro/falso.',
    'integer' => 'Informe um número inteiro válido.',
    'numeric' => 'Informe um número válido.',
    'min' => 'O valor mínimo é :min.',
    'in' => 'Valor selecionado é inválido.',
  ]
)]
class CadastroForm extends Form
{
  public ?string $nome = null;
  public bool $obrigatoriedade = false;

  public ?int $qtd_minima = 0;
  public ?int $qtd_maxima = 1;

  /**
   * Estrutura dos complementos:
   * [
   *   ['nome' => '', 'descricao' => null, 'external_id' => null, 'preco' => 0, 'status' => '1'],
   *   ...
   * ]
   */
  public array $complementos = [];

  /**
   * Normaliza e corrige inconsistências antes de validar/persistir.
   * - Garante limites coerentes e obrigatoriedade respeitada.
   * - Converte/limpa campos numéricos e booleanos.
   */
  public function normalizar(): void
  {
    // Casts e coerções
    $this->obrigatoriedade = (bool)$this->obrigatoriedade;
    $this->qtd_minima = $this->qtd_minima !== null ? max(0, (int)$this->qtd_minima) : 0;
    $this->qtd_maxima = $this->qtd_maxima !== null ? max(0, (int)$this->qtd_maxima) : 0;

    // Se obrigatório, garantir pelo menos 1 na mínima
    if ($this->obrigatoriedade && $this->qtd_minima === 0) {
      $this->qtd_minima = 1;
    }

    // Mínimo não pode exceder o máximo
    if ($this->qtd_maxima > 0 && $this->qtd_minima > $this->qtd_maxima) {
      $this->qtd_minima = $this->qtd_maxima;
    }

    // Normaliza a coleção de complementos
    $this->complementos = array_values(array_map(static function (array $c): array {
      return [
        'nome' => trim((string)($c['nome'] ?? '')),
        'descricao' => $c['descricao'] ?? null,
        'external_id' => $c['external_id'] ?? null,
        'preco' => isset($c['preco']) ? (float)$c['preco'] : 0.0,
        'status' => (string)($c['status'] ?? '1'), // '0' pausado | '1' ativo
      ];
    }, $this->complementos));
  }

  /**
   * Helpers para o componente Livewire.
   */
  public function adicionarComplemento(): void
  {
    $this->complementos[] = [
      'nome' => '',
      'descricao' => null,
      'external_id' => null,
      'preco' => 0.0,
      'status' => '1',
    ];
  }

  public function removerComplemento(int $indice): void
  {
    if (array_key_exists($indice, $this->complementos)) {
      unset($this->complementos[$indice]);
    }
  }
}
