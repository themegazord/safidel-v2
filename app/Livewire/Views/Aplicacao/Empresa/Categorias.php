<?php

namespace App\Livewire\Views\Aplicacao\Empresa;

use AllowDynamicProperties;
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
use App\Livewire\Forms\Cardapio\Categoria\EdicaoForm as EdicaoCategoria;
use Mary\Traits\Toast;

#[AllowDynamicProperties]
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

  // DTO
  public ?Categoria $categoriaAtual = null;

  // Modal

  public bool $modalConfirmacaoRemocaoCategoria = false;

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
  public EdicaoCategoria $categoriaEdicao;

  public function resetForms(): void
  {
    $this->categoriaCadastrar->reset();
    $this->categoriaEdicao->reset();
    $this->resetErrorBag();

    //tabs

    $this->tabSelecionada = 'detalhes';
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

  public function cadastrarCategoria(): void
  {
    $this->categoriaCadastrar->validate();

    // Reuso da validação de conflitos PDV antes de qualquer gravação
    if ($mensagem = $this->validarConflitosPDVParaCadastro()) {
      $this->warning($mensagem);
      return;
    }

    \Illuminate\Support\Facades\DB::transaction(function () {
      $categoria = \App\Models\Categoria::query()->create([
        'cardapio_id' => $this->cardapio_id,
        'tipo'        => $this->categoriaCadastrar->tipo,
        'nome'        => $this->categoriaCadastrar->nome,
      ]);

      if ($this->categoriaCadastrar->tipo !== "P") {
        return;
      }

      // Usa relações para criar em lote e já vincular à categoria
      $categoria->tamanhos()->createMany($this->categoriaCadastrar->tamanho);
      $categoria->massas()->createMany($this->categoriaCadastrar->massa);
      $categoria->bordas()->createMany($this->categoriaCadastrar->borda);
    });

    $this->success('Categoria cadastrada com sucesso!');
    $this->resetForms();
    $this->drawerCadastroCategoria = false;
    $this->reloadItens();
  }

  /**
   * @throws \Throwable
   */
  public function editarCategoria(): void
  {
    $this->categoriaEdicao->validate();

    // Verifica conflitos de códigos PDV antes de qualquer atualização
    if ($mensagem = $this->encontrarConflitoCodigoPDV($this->categoriaEdicao->massas, 'massa')) {
      $this->warning($mensagem);
      return;
    }
    if ($mensagem = $this->encontrarConflitoCodigoPDV($this->categoriaEdicao->bordas, 'borda')) {
      $this->warning($mensagem);
      return;
    }

    \Illuminate\Support\Facades\DB::transaction(function () {
      // Atualiza categoria atual sem reconsultar
      $this->categoriaAtual->update([
        'nome' => $this->categoriaEdicao->nome,
      ]);

      if ($this->categoriaEdicao->tipo !== 'P') {
        return;
      }

      // Atualizações atômicas e com whitelisting de campos
      $this->atualizarColecao(
        $this->categoriaEdicao->tamanhos,
        \App\Models\CategoriaTamanho::class,
        ['external_id', 'nome', 'qtde_pedacos', 'qtde_sabores']
      );

      $this->atualizarColecao(
        $this->categoriaEdicao->massas,
        \App\Models\CategoriaMassa::class,
        ['external_id', 'nome', 'preco']
      );

      $this->atualizarColecao(
        $this->categoriaEdicao->bordas,
        \App\Models\CategoriaBorda::class,
        ['external_id', 'nome', 'preco']
      );
    });

    $this->success('Categoria editada com sucesso!');
    $this->resetForms();
    $this->drawerEdicaoCategoria = false;
    $this->reloadItens();
  }

  public function removerCategoria(): void {
    $this->categoriaAtual->forceDelete();
    $this->success('Categoria removida com sucesso!');
    $this->resetForms();
    $this->reloadItens();
    $this->modalConfirmacaoRemocaoCategoria = false;
  }

  public function setCategoriaAtual(int $categoria_id, string $metodo): void
  {
    $this->categoriaAtual = Categoria::withTrashed()
      ->findOrFail($categoria_id)
      ->load(['tamanhos', 'massas', 'bordas']);


    if ($metodo === 'edicao') {
      $this->categoriaEdicao->fill($this->categoriaAtual->toArray());
      $this->drawerEdicaoCategoria = true;
      return;
    }

    if ($metodo === 'remocao') {
      $this->modalConfirmacaoRemocaoCategoria = true;
    }
  }

  public function adicionarCategoriaPropriedade(string $tipo, bool $edicao = false): void
  {
    $defaults = match ($tipo) {
      'tamanhos' => ['nome' => $edicao ? 'Tamanho novo' : '', 'qtde_pedacos' => 1, 'qtde_sabores' => [1]],
      'massas'   => ['nome' => $edicao ? 'Massa novo' : '', 'external_id' => null, 'preco' => 0],
      'bordas'   => ['nome' => $edicao ? 'Borda novo' : '', 'external_id' => null, 'preco' => 0],
      default   => throw new \InvalidArgumentException("Tipo inválido: {$tipo}"),
    };

    if ($edicao) {
      $modelClass = match ($tipo) {
        'tamanhos' => \App\Models\CategoriaTamanho::class,
        'massas'   => \App\Models\CategoriaMassa::class,
        'bordas'   => \App\Models\CategoriaBorda::class,
      };

      $novo = $modelClass::create([
        'categoria_id' => $this->categoriaAtual->id,
        ...$defaults,
      ]);

      $this->categoriaEdicao->{$tipo}[] = [
        'id' => $novo->id,
        ...$defaults,
      ];
    } else {
      $this->categoriaCadastrar->{$tipo}[] = $defaults;
    }
  }

  public function removerCategoriaPropriedade(string $tipo, int $chave, ?int $id = null): void
  {
    if ($id) {
      $relacao = match ($tipo) {
        'tamanhos' => $this->categoriaAtual->tamanhos,
        'massas'   => $this->categoriaAtual->massas,
        'bordas'   => $this->categoriaAtual->bordas,
      };

      $relacao->find($id)?->delete();
      unset($this->categoriaEdicao->{$tipo}[$chave]);
    } else {
      unset($this->categoriaCadastrar->{$tipo}[$chave]);
    }
  }


  /**
   * Atualiza uma coleção de registros garantindo que pertencem à categoria atual
   * e que apenas campos permitidos são atualizados.
   *
   * @param array<int, array<string, mixed>> $itens
   * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
   * @param array<int, string> $camposPermitidos
   */
  protected function atualizarColecao(array $itens, string $modelClass, array $camposPermitidos): void
  {
    foreach ($itens as $item) {
      if (empty($item['id'])) {
        continue;
      }

      // Whitelist de campos
      $payload = array_intersect_key($item, array_flip($camposPermitidos));

      // Garante que o registro pertence à categoria atual
      $modelClass::query()
        ->whereKey($item['id'])
        ->where('categoria_id', $this->categoriaAtual->id)
        ->update($payload);
    }
  }

  /**
   * Verifica se já existe um Item usando o mesmo external_id para algum elemento da coleção.
   * Retorna a primeira mensagem de conflito encontrada ou null se não houver conflito.
   *
   * @param array<int, array<string, mixed>> $colecao
   */
  protected function encontrarConflitoCodigoPDV(array $colecao, string $tipoElemento): ?string
  {
    foreach ($colecao as $elemento) {
      $externalId = $elemento['external_id'] ?? null;
      $nomeElemento = $elemento['nome'] ?? '';

      if (empty($externalId)) {
        continue;
      }

      $possivelItem = \App\Models\Item::query()->where('external_id', $externalId)->first();
      if (is_null($possivelItem)) {
        continue;
      }

      $mensagens = [
        'PIZ' => "Código PDV informado na {$tipoElemento} {$nomeElemento} está sendo usado no sabor de pizza [{$possivelItem->nome}]",
        'PRE' => "Código PDV informado na {$tipoElemento} {$nomeElemento} está sendo usado no item preparado [{$possivelItem->nome}]",
        'BEB' => "Código PDV informado na {$tipoElemento} {$nomeElemento} está sendo usado na bebida [{$possivelItem->nome}]",
        'IND' => "Código PDV informado na {$tipoElemento} {$nomeElemento} está sendo usado no item industrializado [{$possivelItem->nome}]",
      ];

      return $mensagens[$possivelItem->tipo] ?? 'Código PDV duplicado';
    }

    return null;
  }

  private function reloadItens(): void
  {
    $this->carregaCategorias($this->cardapio_id);
    $this->geraStats();
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
        'value' => $this->categorias->sum(fn($c) => $c->itens->count()),
        'icon' => 'o-cube-transparent'
      ],
      [
        'label' => 'Preço médio dos itens',
        'value' => "R$ " . number_format($this->precoMedioDosItens()['normais'], 2, ',', '.'),
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
    $itens = $this->categorias->flatMap(fn($categoria) => $categoria->itens);

    $avgOrNull = function (\Illuminate\Support\Collection $values): ?float {
      $values = $values->filter(static fn($v) => $v !== null);
      return $values->isNotEmpty() ? round($values->avg(), 2) : null;
    };

    $precosNormais = $itens
      ->whereIn('tipo', ['PRE', 'BEB', 'IND'])
      ->map(fn($item) => is_null($item->preco) ? null : (float)$item->preco);

    $precosPizzas = $itens
      ->where('tipo', 'PIZ')
      ->map(function ($item) {
        $avg = $item->precosItemPizza?->avg('preco');
        return is_null($avg) ? null : (float)$avg;
      });

    return [
      'normais' => $avgOrNull($precosNormais),
      'pizzas' => $avgOrNull($precosPizzas),
    ];
  }

  /**
   * Reaproveita encontrarConflitoCodigoPDV para verificar massa e borda.
   */
  private function validarConflitosPDVParaCadastro(): ?string
  {
    if ($msg = $this->encontrarConflitoCodigoPDV($this->categoriaCadastrar->massa, 'massa')) {
      return $msg;
    }
    if ($msg = $this->encontrarConflitoCodigoPDV($this->categoriaCadastrar->borda, 'borda')) {
      return $msg;
    }
    return null;
  }

}
