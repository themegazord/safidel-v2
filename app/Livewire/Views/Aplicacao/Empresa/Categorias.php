<?php

namespace App\Livewire\Views\Aplicacao\Empresa;

use App\Models\Cardapio;
use App\Models\Categoria;
use App\Models\CategoriaBorda;
use App\Models\CategoriaMassa;
use App\Models\CategoriaTamanho;
use App\Models\Empresa;
use App\Models\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Livewire\Forms\Cardapio\Categoria\CadastroForm as CadastroCategoria;
use Mary\Traits\Toast;

class Categorias extends Component
{
  use Toast;

  public int $cardapio_id;
  public Collection $categorias;
  public Empresa $empresa;
  public ?array $stats = null;
  public array $qtd_sabores = [
    ['id' => 1, 'nome' => 1],
    ['id' => 2, 'nome' => 2],
    ['id' => 3, 'nome' => 3],
    ['id' => 4, 'nome' => 4],
  ];

  // Drawers
  public bool $drawerEdicaoCategoria = false;
  public bool $drawerCadastroCategoria = false;
  public bool $drawerCadastroItem = false;
  public bool $drawerCadastroItemPreparado = false;
  public bool $drawerCadastroBebida = false;
  public bool $drawerCadastroItemIndustrializado = false;
  public bool $drawerCadastroComplementoItem = false;
  public bool $drawerEdicaoComplementoItem = false;
  public bool $drawerEdicaoItemPreparado = false;
  public bool $drawerEdicaoItemBebida = false;
  public bool $drawerEdicaoItemIndustrializado = false;
  public bool $drawerEdicaoItemPizza = false;

  // Tabs
  public string $tabSelecionada = 'detalhes';
  public string $tabCadastroItemSelecionada = 'detalhes';
  public string $tabCadastroItemPreparado = 'detalhes';
  public string $tabCadastroItemBebida = 'detalhes';
  public string $tabCadastroItemIndustrializado = 'detalhes';
  public string $tabCadastroItemComplemento = 'detalhes';
  public string $tabEdicaoItemComplemento = 'detalhes';

  // Forms
  public CadastroCategoria $categoriaCadastrar;

  public function resetForms(): void
  {
    $this->categoriaCadastrar->reset();
  }
  public function mount(int $cardapio_id): void
  {
    $this->cardapio_id = $cardapio_id;
    $this->empresa = Auth::user()->empresa;
    $this->carregaCategorias($cardapio_id);

    $this->geraStats();
  }


  #[Layout('components.layouts.empresa')]
  #[Title('Categorias')]
  public function render(): \Illuminate\Contracts\View\View
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

  /**
   * @param int $cardapio_id
   * @return void
   */
  public function carregaCategorias(int $cardapio_id): void
  {
    $cardapio = $this->empresa->cardapios()->findOrFail($cardapio_id)
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
  }

  public function cadastraCategoria(): void
  {
    $this->categoriaCadastrar->validate();

    foreach ($this->categoriaCadastrar->massa as $massa) {
      if (!empty($massa['external_id'])) {
        $possivelItem = Item::query()->where('external_id', $massa['external_id'])->first();
        if (!is_null($possivelItem)) {
          match ($possivelItem->tipo) {
            'PIZ' => $this->warning("Código PDV informado na massa {$massa['nome']} está sendo usado no sabor de pizza [$possivelItem->nome]"),
            'PRE' => $this->warning("Código PDV informado na massa {$massa['nome']} está sendo usado no item preparado [$possivelItem->nome]"),
            'BEB' => $this->warning("Código PDV informado na massa {$massa['nome']} está sendo usado na bebida [$possivelItem->nome]"),
            'IND' => $this->warning("Código PDV informado na massa {$massa['nome']} está sendo usado no item industrializado [$possivelItem->nome]"),
          };
          return;
        }
      }
    }

    foreach ($this->categoriaCadastrar->borda as $borda) {
      if (!empty($borda['external_id'])) {
        $possivelItem = Item::query()->where('external_id', $borda['external_id'])->first();
        if (!is_null($possivelItem)) {
          match ($possivelItem->tipo) {
            'PIZ' => $this->warning("Código PDV informado na borda {$borda['nome']} está sendo usado no sabor de pizza [$possivelItem->nome]"),
            'PRE' => $this->warning("Código PDV informado na borda {$borda['nome']} está sendo usado no item preparado [$possivelItem->nome]"),
            'BEB' => $this->warning("Código PDV informado na borda {$borda['nome']} está sendo usado na bebida [$possivelItem->nome]"),
            'IND' => $this->warning("Código PDV informado na borda {$borda['nome']} está sendo usado no item industrializado [$possivelItem->nome]"),
          };
          return;
        }
      }
    }

    $categoria = Categoria::query()->create([
      'cardapio_id' => $this->cardapio_id,
      'tipo' => $this->categoriaCadastrar->tipo,
      'nome' => $this->categoriaCadastrar->nome
    ]);


    if ($this->categoriaCadastrar->tipo === 'P') {
      foreach ($this->categoriaCadastrar->tamanho as $tamanho) {
        CategoriaTamanho::query()->create([
          'categoria_id' => $categoria->id,
          ...$tamanho
        ]);
      }
      foreach ($this->categoriaCadastrar->massa as $massa) {
        CategoriaMassa::query()->create([
          'categoria_id' => $categoria->id,
          ...$massa
        ]);
      }
      foreach ($this->categoriaCadastrar->borda as $borda) {
        CategoriaBorda::query()->create([
          'categoria_id' => $categoria->id,
          ...$borda
        ]);
      }
    }

    $this->success('Categoria cadastrada com sucesso!');
    $this->resetForms();
    $this->drawerCadastroCategoria = false;

    $this->reloadItens();
  }

  private function reloadItens(): void {
    $this->geraStats();
    $this->carregaCategorias($this->cardapio_id);
  }


}
