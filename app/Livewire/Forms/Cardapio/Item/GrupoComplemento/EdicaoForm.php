<?php

namespace App\Livewire\Forms\Cardapio\Item\GrupoComplemento;

use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Form;

#[Validate(
  rule: [
    'nome' => ['required', 'string', 'max:80'],
    'obrigatoriedade' => ['boolean'],
    'qtd_minima' => ['nullable', 'integer', 'min:0'],
    'qtd_maxima' => ['nullable', 'integer', 'min:0'],

    'complementos' => ['array'],
    'complementos.*.nome' => ['required', 'string', 'max:80'],
    'complementos.*.descricao' => ['nullable', 'string', 'max:255'],
    'complementos.*.external_id' => ['nullable', 'string', 'max:50'],
    'complementos.*.preco' => ['nullable', 'numeric', 'min:0'],
    'complementos.*.status' => ['nullable', 'in:0,1'],
  ],
  message: [
    'nome.required' => 'O nome do grupo é obrigatório.',
    'nome.string' => 'O nome do grupo deve ser um texto.',
    'nome.max' => 'O nome do grupo não pode ter mais que :max caracteres.',

    'obrigatoriedade.boolean' => 'Obrigatoriedade deve ser verdadeiro ou falso.',

    'qtd_minima.integer' => 'A quantidade mínima deve ser um número inteiro.',
    'qtd_minima.min' => 'A quantidade mínima não pode ser negativa.',
    'qtd_maxima.integer' => 'A quantidade máxima deve ser um número inteiro.',
    'qtd_maxima.min' => 'A quantidade máxima não pode ser negativa.',

    'complementos.array' => 'A lista de complementos é inválida.',
    'complementos.*.nome.required' => 'O nome do complemento é obrigatório.',
    'complementos.*.nome.string' => 'O nome do complemento deve ser um texto.',
    'complementos.*.nome.max' => 'O nome do complemento não pode ter mais que :max caracteres.',
    'complementos.*.descricao.string' => 'A descrição do complemento deve ser um texto.',
    'complementos.*.descricao.max' => 'A descrição do complemento não pode ter mais que :max caracteres.',
    'complementos.*.external_id.string' => 'O código PDV deve ser um texto.',
    'complementos.*.external_id.max' => 'O código PDV não pode ter mais que :max caracteres.',
    'complementos.*.preco.numeric' => 'O preço do complemento deve ser numérico.',
    'complementos.*.preco.min' => 'O preço do complemento não pode ser negativo.',
    'complementos.*.status.in' => 'Status do complemento inválido.',
  ]
)]
class EdicaoForm extends Form
{
  public ?int $id = null;
  public ?int $item_id = null;
  public ?string $nome = null;
  public ?bool $obrigatoriedade = false;
  public ?int $qtd_minima = 0;
  public ?int $qtd_maxima = 1;
  public array $complementos = [];

  public function alimentaComplementos(Collection $complementos): void
  {
    foreach ($complementos as $complemento) {
      $this->complementos[] = $complemento->only([
        'id', 'external_id', 'nome', 'descricao', 'preco', 'status'
      ]);
    }
  }

  /**
   * Normaliza campos antes de validar/persistir.
   */
  public function normalizar(): void
  {
    $this->obrigatoriedade = (bool)($this->obrigatoriedade ?? false);
    $this->qtd_minima = $this->qtd_minima !== null ? max(0, (int)$this->qtd_minima) : 0;
    $this->qtd_maxima = $this->qtd_maxima !== null ? max(0, (int)$this->qtd_maxima) : 0;

    if ($this->obrigatoriedade && $this->qtd_minima === 0) {
      $this->qtd_minima = 1;
    }
    if ($this->qtd_maxima > 0 && $this->qtd_minima > $this->qtd_maxima) {
      $this->qtd_minima = $this->qtd_maxima;
    }

    $this->complementos = array_values(array_map(static function (array $c): array {
      return [
        'id' => $c['id'] ?? null,
        'external_id' => $c['external_id'] ?? null,
        'nome' => trim((string)($c['nome'] ?? '')),
        'descricao' => $c['descricao'] ?? null,
        'preco' => isset($c['preco']) ? (float)$c['preco'] : 0.0,
        'status' => (string)($c['status'] ?? '1'),
      ];
    }, $this->complementos));
  }

  public function adicionarComplemento(): void
  {
    $this->complementos[] = [
      'id' => null,
      'external_id' => null,
      'nome' => '',
      'descricao' => null,
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
