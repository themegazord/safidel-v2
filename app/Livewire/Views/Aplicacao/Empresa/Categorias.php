<?php

namespace App\Livewire\Views\Aplicacao\Empresa;

use App\Models\Cardapio;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Categorias extends Component
{
  public Collection $categorias;
  public ?array $stats = null;

  public function mount(int $cardapio_id): void
  {
    $cardapio = Cardapio::query()
      ->with([
        'categorias' => function ($q) {
          $q->withTrashed()
            ->with([
              'tamanhos',
              'massas',
              'bordas',
              'itens',
              'itens.precosItemPizza',
            ]);
        },
      ])
      ->findOrFail($cardapio_id);

    // Categorias já vieram com withTrashed e com as relações carregadas
    $this->categorias = $cardapio->categorias;

    $this->geraStats();
  }


  #[Layout('components.layouts.empresa')]
  #[Title('Categorias')]
  public function render()
  {
    return view('livewire.views.aplicacao.empresa.categorias');
  }

  private function geraStats(): void
  {
    $this->stats = [
      [
        'label' => 'Total de categorias',
        'value' => $this->categorias->count(),
        'icon' => 'o-archive-box'
      ],
      [
        'label' => 'Categorias ativas',
        'value' => $this->categorias->whereNull('deleted_at')->count(),
        'icon' => 'o-arrow-trending-up'
      ],
      [
        'label' => 'Total de itens',
        'value' => $this->categorias->sum(fn ($c) => $c->itens->count()),
        'icon' => 'o-cube-transparent'
      ],
      [
        'label' => 'Preço médio dos itens',
        'value' =>  "R$ " . number_format($this->precoMedioDosItens()['normais'], 2, ',', '.'),
        'icon' => 'o-currency-dollar'
      ],
      [
        'label' => 'Preço médio dos itens (pizzas)',
        'value' => "R$ " . number_format($this->precoMedioDosItens()['pizzas'], 2, ',', '.'),
        'icon' => 'o-currency-dollar'
      ]
    ];
  }

  private function precoMedioDosItens(): array
  {
    $itens = $this->categorias->flatMap(fn ($categoria) => $categoria->itens);

    $avgOrNull = function (\Illuminate\Support\Collection $values): ?float {
      $values = $values->filter(static fn ($v) => $v !== null);
      return $values->isNotEmpty() ? round($values->avg(), 2) : null;
    };

    $precosNormais = $itens
      ->whereIn('tipo', ['PRE', 'BEB', 'IND'])
      ->map(fn ($item) => is_null($item->preco) ? null : (float) $item->preco);

    $precosPizzas = $itens
      ->where('tipo', 'PIZ')
      ->map(function ($item) {
        $avg = $item->precosItemPizza?->avg('preco');
        return is_null($avg) ? null : (float) $avg;
      });

    return [
      'normais' => $avgOrNull($precosNormais),
      'pizzas'  => $avgOrNull($precosPizzas),
    ];
  }


}
