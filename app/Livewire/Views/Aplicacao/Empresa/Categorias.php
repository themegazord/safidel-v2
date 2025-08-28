<?php

namespace App\Livewire\Views\Aplicacao\Empresa;

use AllowDynamicProperties;
use App\Livewire\Forms\Cardapio\Categoria\CadastroForm as CadastroCategoria;
use App\Livewire\Forms\Cardapio\Categoria\EdicaoForm as EdicaoCategoria;
use App\Livewire\Forms\Cardapio\Item\CadastroForm as CadastroItem;
use App\Livewire\Forms\Cardapio\Item\EdicaoForm as EdicaoItem;
use App\Livewire\Forms\Cardapio\Item\GrupoComplemento\CadastroForm as CadastroGrupoComplementoItem;
use App\Livewire\Forms\Cardapio\Item\GrupoComplemento\EdicaoForm as EdicaoGrupoComplementoItem;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\GrupoComplemento;
use App\Models\Item;
use App\Traits\TrataMGCObjectStoreTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[AllowDynamicProperties]
class Categorias extends Component
{
  use Toast, WithFileUploads, TrataMGCObjectStoreTrait;

  #[Validate(rule: ['image', 'max:2048', 'mimes:jpg'], message: [
    'image' => 'Deve ser encaminhado uma imagem',
    'max' => 'A imagem deve conter no máximo 2mb.',
    'mimes:jpg' => 'Aceitamos apenas .jpg'
  ])]
  public ?TemporaryUploadedFile $fotoTemporaria = null;
  public ?TemporaryUploadedFile $fotoTemporariaEdicao = null;

  // Constantes de domínio e tipos de preço
  private const CATEGORIA_ITENS = 'I';
  private const CATEGORIA_PIZZA = 'P';

  private const ITEM_PREPARADO = 'PRE';
  private const ITEM_BEBIDA = 'BEB';
  private const ITEM_INDUSTR = 'IND';
  private const ITEM_PIZZA = 'PIZ';

  private const PRECO_TIPO_FIXO = 'fixo';
  private const PRECO_TIPO_POR_ITEM = 'preco_item';

  public ?string $tipoItemNormal = 'PRE';

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
  public array $qtd_pessoas = [
    ['id' => null, 'name' => 'Não se aplica'],
    ['id' => 1, 'name' => '1 pessoa.'],
    ['id' => 2, 'name' => '2 pessoas.'],
    ['id' => 3, 'name' => '3+ pessoas.'],
  ];

  public array $gramagem = [
    ['valor' => 'g', 'label' => 'g'],
    ['valor' => 'Kg', 'label' => 'Kg']
  ];

  // DTO
  public ?Categoria $categoriaAtual = null;
  public ?Item $itemAtual = null;
  public ?GrupoComplemento $grupoComplementoAtual = null;

  // Modal
  public bool $modalConfirmacaoRemocaoCategoria = false;
  public bool $modalConfirmacaoClonarItem = false;
  public bool $modalConfirmacaoRemocaoItem = false;
  public bool $modalConfirmacaoRemocaoGrupoComplemento = false;

  // Copia de complementos
  public bool $copia_complemento = false;
  public ?int $categoriaSelecionadaCopiaComplemento, $itemSelecionadoCopiaComplemento = null;

  // Drawers
  public bool $drawerEdicaoCategoria = false;
  public bool $drawerCadastroCategoria = false;
  public bool $drawerCadastroItem = false;
  public bool $drawerCadastroComplementoItem = false;
  public bool $drawerEdicaoComplementoItem = false;
  public bool $drawerEdicaoItem = false;
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
  public CadastroItem $itemCadastrar;
  public EdicaoCategoria $categoriaEdicao;
  public EdicaoItem $itemEditar;
  public CadastroGrupoComplementoItem $grupoComplementoCadastro;
  public EdicaoGrupoComplementoItem $grupoComplementoEdicao;

  public function resetForms(): void
  {
    $this->categoriaCadastrar->reset();
    $this->categoriaEdicao->reset();
    $this->itemCadastrar->reset();
    $this->itemEditar->reset();
    $this->resetErrorBag();

    //imagens

    $this->fotoTemporaria = null;
    $this->fotoTemporariaEdicao = null;

    // modal

    $this->itemAtual = null;
    $this->categoriaAtual = null;

    //tabs

    $this->tabSelecionada = 'detalhes';
    $this->tabCadastroItemSelecionada = 'detalhes';
  }

  public function resetFormGrupoComplemento(): void
  {
    $this->grupoComplementoCadastro->reset();
    $this->grupoComplementoEdicao->reset();
    $this->grupoComplementoCadastro->resetValidation();
    $this->grupoComplementoEdicao->resetValidation();

    $this->tabCadastroItemComplemento = 'detalhes';
    $this->tabEdicaoItemComplemento = 'detalhes';
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

  public function updating(string $props, mixed $valor): void
  {
    if ($props === 'itemCadastrar.valor_desconto') {
      $this->itemCadastrar->porcentagem_desconto = 100 - (($valor * 100) / $this->itemCadastrar->preco);
    }

    if ($props === 'itemCadastrar.porcentagem_desconto') {
      $this->itemCadastrar->valor_desconto = $this->itemCadastrar->preco - (($valor * $this->itemCadastrar->preco) / 100);
    }
  }

  /**
   * @param int $cardapio_id
   * @return void
   */
  public function carregaCategorias(int $cardapio_id): void
  {
    // Garante que o cardápio pertence à empresa, sem carregar relações desnecessárias
    $this->empresa->cardapios()->whereKey($cardapio_id)->firstOrFail();

    // Carrega as categorias do cardápio com todas as relações necessárias
    $this->categorias = \App\Models\Categoria::withTrashed()
      ->where('cardapio_id', $cardapio_id)
      ->with([
        'tamanhos',
        'massas',
        'bordas',
        // Itens com preços de pizza e grupos + complementos (sem N+1)
        'itens' => fn($q) => $q->with([
          'precosItemPizza',
          'grupo_complemento.complementos',
        ]),
      ])
      ->get();
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
        'tipo' => $this->categoriaCadastrar->tipo,
        'nome' => $this->categoriaCadastrar->nome,
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

  public function removerCategoria(): void
  {
    $this->categoriaAtual->forceDelete();
    $this->success('Categoria removida com sucesso!');
    $this->resetForms();
    $this->reloadItens();
    $this->modalConfirmacaoRemocaoCategoria = false;
  }

  public function setCategoriaAtual(int $categoria_id, ?string $metodo = null): void
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
      return;
    }

    if ($metodo === 'ativacao') {
      $categoria = $this->categorias->first(fn($c) => $c->id === $categoria_id);
      if ($categoria->trashed()) {
        $categoria->restore();
      } else {
        $categoria->delete();
      }
      $this->geraStats();
      $this->success('Alterado status da categoria com sucesso!');
    }

    if ($metodo === 'cadastro_item') {
      if ($this->categoriaAtual->tipo === 'P') {
        foreach ($this->categoriaAtual->tamanhos as $tamanho) {
          $this->itemCadastrar->precos[] = [
            'tamanho_id' => $tamanho->id,
            'tamanho' => $tamanho->nome,
            'status' => true,
            'preco' => 0
          ];
        }
      }
      $this->drawerCadastroItem = true;
      return;
    }

  }

  public function adicionarCategoriaPropriedade(string $tipo, bool $edicao = false): void
  {
    $defaults = match ($tipo) {
      'tamanhos' => ['nome' => $edicao ? 'Tamanho novo' : '', 'qtde_pedacos' => 1, 'qtde_sabores' => [1]],
      'massas' => ['nome' => $edicao ? 'Massa novo' : '', 'external_id' => null, 'preco' => 0],
      'bordas' => ['nome' => $edicao ? 'Borda novo' : '', 'external_id' => null, 'preco' => 0],
      default => throw new \InvalidArgumentException("Tipo inválido: {$tipo}"),
    };

    if ($edicao) {
      $modelClass = match ($tipo) {
        'tamanhos' => \App\Models\CategoriaTamanho::class,
        'massas' => \App\Models\CategoriaMassa::class,
        'bordas' => \App\Models\CategoriaBorda::class,
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
        'massas' => $this->categoriaAtual->massas,
        'bordas' => $this->categoriaAtual->bordas,
      };

      $relacao->find($id)?->delete();
      unset($this->categoriaEdicao->{$tipo}[$chave]);
    } else {
      unset($this->categoriaCadastrar->{$tipo}[$chave]);
    }
  }

  public function setTipoItemNormalParaCadastro(string $tipo): void
  {
    $this->tipoItemNormal = $tipo;

    $this->drawerCadastroItem = false;

    match ($tipo) {
      self::ITEM_PREPARADO => $this->drawerCadastroItemPreparado = true,
      self::ITEM_BEBIDA => $this->drawerCadastroBebida = true,
      self::ITEM_INDUSTR => $this->drawerCadastroItemIndustrializado = true,
    };
  }

  /**
   * @throws \Throwable
   */
  public function cadastraItem(): void
  {
    // Upload de imagem (uma única vez)
    if (!empty($this->fotoTemporaria)) {
      $this->itemCadastrar->imagem = $this->uploadImagem(getenv('MGC_BUCKET'), $this->fotoTemporaria);
      $this->fotoTemporaria = null;
    }

    // Categoria de "itens principais"
    if ($this->categoriaAtual->tipo === self::CATEGORIA_ITENS) {
      $this->itemCadastrar->tipo_preco = self::PRECO_TIPO_FIXO;

      // Validação e criação por tipo (PRE/BEB/IND)
      $tipo = $this->itemCadastrar->tipo = $this->tipoItemNormal;
      if (!in_array($tipo, [self::ITEM_PREPARADO, self::ITEM_BEBIDA, self::ITEM_INDUSTR], true)) {
        $this->warning('Tipo de item inválido.');
        return;
      }

      // Força o tipo esperado e valida
      $this->itemCadastrar->tipo = $tipo;
      $this->itemCadastrar->validate();

      \Illuminate\Support\Facades\DB::transaction(function () use ($tipo) {
        \App\Models\Item::query()->create($this->montarPayloadItemPorTipo($tipo));
      });

      // Fecha o drawer correto conforme o tipo
      $resetDrawers = [
        self::ITEM_PREPARADO => 'drawerCadastroItemPreparado',
        self::ITEM_BEBIDA => 'drawerCadastroBebida',
        self::ITEM_INDUSTR => 'drawerCadastroItemIndustrializado',
      ];

      // Supondo que $tipo contenha o tipo válido usado na criação
      if (isset($resetDrawers[$tipo])) {
        $drawerProp = $resetDrawers[$tipo];
        if (property_exists($this, $drawerProp)) {
          $this->{$drawerProp} = false;
        }
      }
      $this->tipoItemNormal = 'PRE';
      $this->drawerCadastroItem = false;
      $this->success('Item cadastrado com sucesso');
      $this->resetForms();

      return;
    }

    // Categoria de pizza
    if ($this->categoriaAtual->tipo === self::CATEGORIA_PIZZA) {
      $this->itemCadastrar->tipo = self::ITEM_PIZZA;
      $this->itemCadastrar->tipo_preco = self::PRECO_TIPO_POR_ITEM;

      $this->itemCadastrar->validate();

      \Illuminate\Support\Facades\DB::transaction(function () {
        $item = \App\Models\Item::query()->create($this->montarPayloadItemPizza());
        $precosValidos = array_values(array_filter(
          $this->itemCadastrar->precos,
          static fn($p) => ($p['status'] ?? false) && isset($p['tamanho_id']) && isset($p['preco'])
        ));


        if (!empty($precosValidos)) {
          $payloadPrecos = array_map(fn($p) => [
            'tamanho_id' => $p['tamanho_id'],
            'preco' => $p['preco'],
            'classificacao' => $this->itemCadastrar->classificacaoLimpa(),
          ], $precosValidos);

          $item->precosItemPizza()->createMany($payloadPrecos);
        }
      });

      $this->success('Item cadastrado com sucesso');
      $this->resetForms();
      $this->drawerCadastroItem = false;
      return;
    }

    $this->warning('Tipo de categoria inválido.');
  }

  public function alterarStatusItem(int $item_id): void
  {
    // Tenta pegar em memória dentro de $this->categorias (já carregadas)
    $item = $this->categorias
      ->flatMap(fn($categoria) => $categoria->itens)
      ->firstWhere('id', $item_id);

    // Se não encontrado, faz fallback ao banco, limitado às categorias visíveis
    if (!$item) {
      $categoriaIds = $this->categorias->pluck('id');
      $item = \App\Models\Item::withTrashed()
        ->whereIn('categoria_id', $categoriaIds)
        ->findOrFail($item_id);
    }

    // Alterna status via SoftDeletes
    if ($item->trashed()) {
      $item->restore();
      $this->success('Item ativado com sucesso!');
    } else {
      $item->delete();
      $this->success('Item inativado com sucesso!');
    }

    // Atualiza listas e métricas exibidas
    $this->reloadItens();
  }

  public function setItemAtual(int $item_id, int $categoria_id, string $metodo): void
  {

    // Carrega item com dependências usadas no fill
    $this->itemAtual = \App\Models\Item::withTrashed()
      ->with(['categoria', 'precosItemPizza', 'grupo_complemento.complementos'])
      ->findOrFail($item_id);

    // Garante categoria atual
    $this->setCategoriaAtual($categoria_id);

    // Normaliza classificação para o formato do formulário de edição
    $slugs = [
      'vegetariano', 'vegano', 'organico', 'sem-acucar', 'zero_lactose',
      'bebida_gelada', 'bebida_alcolica', 'bebida_natural', 'zero_lactose', 'bebida_diet'
    ];
    $ativos = collect($this->itemAtual->classificacao ?? [])->values()->all();
    $classificacaoForm = array_map(
      static fn($slug) => ['nome' => $slug, 'status' => in_array($slug, $ativos, true)],
      $slugs
    );

    // Prepara preços para pizza no formato esperado pelo form (quando aplicável)
    $precosForm = [];
    if ($this->itemAtual->tipo === 'PIZ') {
      $precosForm = $this->itemAtual->precosItemPizza
        ->map(fn($p) => [
          'tamanho_id' => $p->tamanho_id,
          'tamanho' => $p->tamanho->nome,
          'preco' => $p->preco,
          'status' => true,
        ])
        ->values()
        ->all();
    }

    // Monta payload para o form itemEditar
    $payload = [
      'external_id' => $this->itemAtual->external_id,
      'categoria_id' => $this->itemAtual->categoria_id,
      'inativo' => $this->itemAtual->trashed(),
      'nome' => $this->itemAtual->nome,
      'tipo' => $this->itemAtual->tipo,
      'descricao' => $this->itemAtual->descricao,
      'preco' => $this->itemAtual->preco,
      'desconto' => (bool)$this->itemAtual->desconto,
      'valor_desconto' => $this->itemAtual->valor_desconto,
      'porcentagem_desconto' => $this->itemAtual->porcentagem_desconto,
      'contem_complemento' => $this->itemAtual->grupo_complemento()->exists(),
      'grupo_complemento' => $this->itemAtual->grupo_complemento
        ->map(fn($g) => [
          'id' => $g->id,
          'nome' => $g->nome ?? null,
          'min' => $g->min ?? null,
          'max' => $g->max ?? null,
          'complementos' => $g->complementos
            ->map(fn($c) => [
              'id' => $c->id,
              'nome' => $c->nome ?? null,
              'preco' => $c->preco ?? null,
            ])->values()->all(),
        ])->values()->all(),
      'tipo_preco' => $this->itemAtual->tipo_preco,
      'qtde_pessoas' => $this->itemAtual->qtde_pessoas,
      'peso' => $this->itemAtual->peso,
      'gramagem' => $this->itemAtual->gramagem,
      'eh_bebida' => (bool)$this->itemAtual->eh_bebida,
      'imagem' => $this->itemAtual->imagem,
      'classificacao' => $classificacaoForm,
      'precos' => $precosForm,
    ];

    if ($metodo === 'clonar') {
      $this->setCategoriaAtual($categoria_id);
      $this->modalConfirmacaoClonarItem = true;
    }

    if ($metodo === 'remover') {
      $this->modalConfirmacaoRemocaoItem = true;
    }

    if ($metodo === 'editar') {
      // Preenche o form de edição (itemEditar)
      if (property_exists($this, 'itemEditar') && method_exists($this->itemEditar, 'fill')) {
        $this->itemEditar->fill($payload);
      }

      $this->drawerEdicaoItem = true;
    }
  }

  public function clonarItem(): void
  {
    \DB::transaction(function () {
      // Clona e salva o novo item
      $itemDuplicado = $this->itemAtual->replicate();
      $itemDuplicado->save();

      // Clona estruturas relacionadas conforme o tipo
      if ($this->itemAtual->tipo === 'PRE' && !$this->itemAtual->grupo_complemento->isEmpty()) {
        foreach ($this->itemAtual->grupo_complemento as $grupo) {
          $grupoDuplicado = $grupo->replicate()->fill([
            'item_id' => $itemDuplicado->id
          ]);
          $grupoDuplicado->save();

          if (!$grupo->complementos->isEmpty()) {
            foreach ($grupo->complementos as $complemento) {
              $complementoDuplicado = $complemento->replicate()->fill([
                'grupo_id' => $grupoDuplicado->id,
              ]);
              $complementoDuplicado->save();
            }
          }
        }
      }

      if ($this->categoriaAtual->tipo === 'P') {
        foreach ($this->itemAtual->precosItemPizza as $preco) {
          $precoDuplicado = $preco->replicate()->fill([
            'item_id' => $itemDuplicado->id
          ]);
          $precoDuplicado->save();
        }
      }

      // Inativa o item clonado (soft delete) após concluir a clonagem
      $itemDuplicado->delete();
    });

    $this->success('Item duplicado com sucesso');
    $this->modalConfirmacaoClonarItem = false;
  }

  public function removerItem(): void
  {
    DB::transaction(function () {
      $this->itemAtual->forceDelete();
    });

    $this->success('Item removido com sucesso');
    $this->modalConfirmacaoRemocaoItem = false;
    $this->reloadItens();
  }

  public function editarItem(): void
  {
    // Normaliza campos dependentes do tipo/discount do formulário de edição
    if (method_exists($this->itemEditar, 'normalizarCampos')) {
      $this->itemEditar->normalizarCampos();
    }

    \Illuminate\Support\Facades\DB::transaction(function () {
      // Alterna soft delete conforme flag "inativo"
      if (($this->itemEditar->inativo ?? false) && !$this->itemAtual->trashed()) {
        $this->itemAtual->delete();
      } elseif (!($this->itemEditar->inativo ?? false) && $this->itemAtual->trashed()) {
        $this->itemAtual->restore();
      }

      // Atualiza dados principais do item
      \App\Models\Item::withTrashed()
        ->whereKey($this->itemAtual->id)
        ->update([
          'imagem' => $this->itemEditar->imagem,
          'external_id' => $this->itemEditar->external_id,
          'categoria_id' => $this->categoriaAtual->id,
          'nome' => $this->itemEditar->nome,
          'preco' => $this->itemEditar->preco,
          'desconto' => $this->itemEditar->desconto,
          'valor_desconto' => $this->itemEditar->valor_desconto,
          'porcentagem_desconto' => $this->itemEditar->porcentagem_desconto,
          'descricao' => $this->itemEditar->descricao,
          'qtde_pessoas' => $this->itemEditar->qtde_pessoas,
          'peso' => $this->itemEditar->peso,
          'gramagem' => $this->itemEditar->gramagem,
          'eh_bebida' => $this->itemEditar->eh_bebida,
          'classificacao' => $this->itemEditar->classificacaoLimpa(),
        ]);

      // Atualiza preços por tamanho (PIZ)
      if ($this->itemAtual->tipo === 'PIZ') {
        foreach ($this->itemEditar->precos as $preco) {
          $query = \App\Models\ItemPreco::query()->where('item_id', $this->itemAtual->id);

          // Se houver id, usa a PK; senão, usa tamanho_id como fallback
          if (!empty($preco['id'] ?? null)) {
            $query->whereKey($preco['id']);
          } else {
            $query->where('tamanho_id', $preco['tamanho_id'] ?? null);
          }

          $query->update([
            'status' => (bool)($preco['status'] ?? false),
            'preco' => (float)($preco['preco'] ?? 0),
            'classificacao' => $this->itemEditar->classificacaoLimpa(),
          ]);
        }
      }
    });

    // Limpa estado temporário e fecha drawer
    $this->fotoTemporariaEdicao = null;
    $this->drawerEdicaoItem = false;

    $this->success('Item atualizado com sucesso');
    $this->reloadItens();
  }

  public function preparaCadastroGrupoComplementos(): void
  {

    $this->adicionarComplementoNoGrupoComplemento('cadastro');

    $this->tabCadastroItemComplemento = 'detalhes';

    $this->drawerCadastroComplementoItem = true;
  }

  public function adicionarComplementoNoGrupoComplemento(string $metodo): void
  {
    // Delegamos a inclusão ao método do Form
    match ($metodo) {
      'cadastro' => $this->grupoComplementoCadastro->adicionarComplemento(),
      'edicao' => $this->grupoComplementoEdicao->adicionarComplemento(),
    };
  }

  public function removeComplementoNoGrupoComplemento(int $indice, string $metodo): void
  {
    match ($metodo) {
      'cadastro' => $this->grupoComplementoCadastro->removerComplemento($indice),
      'edicao' => $this->grupoComplementoEdicao->removerComplemento($indice),
    };
    $this->success('Complemento removido com sucesso');
  }

  public function finalizarCadastroGrupoComponentes(): void
  {
    // Normaliza e valida o form antes de persistir
    if (method_exists($this->grupoComplementoCadastro, 'normalizar')) {
      $this->grupoComplementoCadastro->normalizar();
    }

    $this->grupoComplementoCadastro->validate();

    \Illuminate\Support\Facades\DB::transaction(function () {
      // Cria via relação do item atual (sem precisar informar item_id manualmente)
      $grupo = $this->itemAtual->grupo_complemento()->create([
        'nome' => $this->grupoComplementoCadastro->nome,
        'obrigatoriedade' => (bool)$this->grupoComplementoCadastro->obrigatoriedade,
        'qtd_minima' => $this->grupoComplementoCadastro->qtd_minima,
        'qtd_maxima' => $this->grupoComplementoCadastro->qtd_maxima,
      ]);

      // Mapeia os complementos e cria em lote pela relação
      $payloadComplementos = array_map(static function (array $c): array {
        return [
          'external_id' => $c['external_id'] ?? null,
          'nome' => $c['nome'] ?? '',
          'descricao' => $c['descricao'] ?? null,
          'preco' => isset($c['preco']) ? (float)$c['preco'] : 0.0,
          'status' => isset($c['status']) ? (int)$c['status'] : 1,
        ];
      }, $this->grupoComplementoCadastro->complementos);

      if (!empty($payloadComplementos)) {
        $grupo->complementos()->createMany($payloadComplementos);
      }
    });

    $this->drawerCadastroComplementoItem = false;
    $this->success('Grupo de complementos registrado com sucesso.');
    $this->resetFormGrupoComplemento();
    $this->reloadItens();
  }

  public function finalizarEdicaoGrupoComponentes(): void
  {
    \Illuminate\Support\Facades\DB::transaction(function () {
      // Atualiza o grupo em memória e persiste somente se houver mudanças
      $this->grupoComplementoAtual->fill([
        'nome' => $this->grupoComplementoEdicao->nome,
        'obrigatoriedade' => (bool)$this->grupoComplementoEdicao->obrigatoriedade,
        'qtd_maxima' => (int)($this->grupoComplementoEdicao->qtd_maxima ?? 0),
      ]);
      if ($this->grupoComplementoAtual->isDirty()) {
        $this->grupoComplementoAtual->save();
      }

      // Índice dos complementos atuais (em memória) por id
      $atuais = $this->grupoComplementoAtual->complementos->keyBy('id');

      // IDs que permanecerão após a edição (para remoção dos que saíram)
      $idsMantidos = [];

      foreach ($this->grupoComplementoEdicao->complementos as $comp) {
        $payload = [
          'external_id' => $comp['external_id'] ?? null,
          'nome' => $comp['nome'] ?? '',
          'descricao' => $comp['descricao'] ?? null,
          'preco' => isset($comp['preco']) ? (float)$comp['preco'] : 0.0,
          'status' => isset($comp['status']) ? (int)$comp['status'] : 1,
        ];

        // Novo complemento (sem id): cria via relação (sem consultar novamente)
        if (empty($comp['id'])) {
          $novo = $this->grupoComplementoAtual->complementos()->create($payload);
          $idsMantidos[] = $novo->id;
          continue;
        }

        // Existente: atualiza o modelo em memória e salva somente se houve mudança
        $modelo = $atuais->get($comp['id']);
        if ($modelo) {
          $modelo->fill($payload);
          if ($modelo->isDirty()) {
            $modelo->save();
          }
          $idsMantidos[] = $modelo->id;
        }
        // Se não achar em $atuais, ignora silenciosamente (ou poderia criar/lançar erro conforme regra)
      }

      // Remove complementos que não estão mais na edição (diferença em memória)
      $idsAtuais = $this->grupoComplementoAtual->complementos->pluck('id')->all();
      $idsParaRemover = array_diff($idsAtuais, $idsMantidos);
      if (!empty($idsParaRemover)) {
        $this->grupoComplementoAtual->complementos()
          ->whereIn('id', $idsParaRemover)
          ->delete();
      }
    });

    $this->drawerEdicaoComplementoItem = false;
    $this->success('Grupo de complementos salvo com sucesso.');
    $this->resetFormGrupoComplemento();
    $this->reloadItens();
  }

  public function setGrupoComplemento(int $grupoId, string $acao): void
  {
    if (!$this->itemAtual) {
      return;
    }

    $query = $this->itemAtual->grupo_complemento();

    if ($acao === 'editar') {
      $query->with('complementos');
    }

    $grupo = $query->findOrFail($grupoId);
    $this->grupoComplementoAtual = $grupo;

    if ($acao === 'editar') {
      $this->grupoComplementoEdicao->fill($grupo->toArray());
      $this->grupoComplementoEdicao->alimentaComplementos($grupo->complementos);
      $this->drawerEdicaoComplementoItem = true;
      return;
    }

    if ($acao === 'remover') {
      $this->modalConfirmacaoRemocaoGrupoComplemento = true;
      return;
    }
  }

  public function copiarComplementoOutroItem(): void
  {
    // Pré-condições mínimas
    if (!$this->itemAtual || !$this->categoriaAtual) {
      $this->warning('Nenhum item/categoria atual selecionado.');
      return;
    }
    if (empty($this->categoriaSelecionadaCopiaComplemento) || empty($this->itemSelecionadoCopiaComplemento)) {
      $this->warning('Selecione a categoria e o item de origem para copiar os complementos.');
      return;
    }

    // Busca o item fonte em memória a partir de $this->categorias (já eager-loaded)
    $itemFonte = $this->obterItemFonteParaCopia();
    if (!$itemFonte) {
      $this->warning('Item de origem não encontrado no cardápio selecionado.');
      return;
    }

    // Garante, se necessário, que grupos e complementos estão carregados (fallback sem reconsultar o que já existir)
    $itemFonte->loadMissing(['grupo_complemento.complementos']);

    $grupos = $itemFonte->grupo_complemento ?? collect();
    if ($grupos->isEmpty()) {
      $this->warning('O item de origem não possui grupos de complementos para copiar.');
      return;
    }

    \Illuminate\Support\Facades\DB::transaction(function () use ($grupos) {
      foreach ($grupos as $grupo) {
        $this->copiarGrupoEComplementos($grupo, $this->itemAtual->id);
      }
    });

    $this->finalizarFluxoCopiaComplementos();
  }
  public function removerGrupoComplemento(): void
  {
    $this->grupoComplementoAtual->delete();
    $this->success('Grupo de complementos removido com sucesso');
    $this->modalConfirmacaoRemocaoGrupoComplemento = false;
  }

  private function obterItemFonteParaCopia(): ?\App\Models\Item
  {
    // Localiza categoria na coleção já carregada (inclui withTrashed)
    $categoria = $this->categorias
      ->first(fn ($c) => (int)$c->id === (int)$this->categoriaSelecionadaCopiaComplemento);

    if (!$categoria) {
      return null;
    }

    // Itens já vieram com 'grupo_complemento.complementos' via carregaCategorias
    return $categoria->itens
      ->first(fn ($i) => (int)$i->id === (int)$this->itemSelecionadoCopiaComplemento);
  }

  private function copiarGrupoEComplementos(\App\Models\GrupoComplemento $grupo, int $itemDestinoId): void
  {
    $grupoCopiado = $grupo->replicate()->fill([
      'item_id' => $itemDestinoId,
    ]);
    $grupoCopiado->save();

    foreach ($grupo->complementos as $complemento) {
      $complementoCopiado = $complemento->replicate()->fill([
        'grupo_id' => $grupoCopiado->id,
      ]);
      $complementoCopiado->save();
    }
  }

  private function finalizarFluxoCopiaComplementos(): void
  {
    $this->success('Grupos de complementos e complementos copiados com sucesso.');
    $this->copia_complemento = false;
    $this->categoriaSelecionadaCopiaComplemento = null;
    $this->itemSelecionadoCopiaComplemento = null;
  }

  /**
   * Monta o payload do Item para categorias "itens principais" conforme o tipo.
   */
  private function montarPayloadItemPorTipo(string $tipo): array
  {
    $comum = [
      'external_id' => $this->itemCadastrar->external_id,
      'categoria_id' => $this->categoriaAtual->id,
      'tipo' => $tipo,
      'nome' => $this->itemCadastrar->nome,
      'tipo_preco' => $this->itemCadastrar->tipo_preco,
      'imagem' => $this->itemCadastrar->imagem,
      'classificacao' => $this->itemCadastrar->classificacaoLimpa(),
    ];

    return match ($tipo) {
      self::ITEM_PREPARADO => $comum + [
          'preco' => $this->itemCadastrar->preco,
          'desconto' => $this->itemCadastrar->desconto,
          'valor_desconto' => $this->itemCadastrar->valor_desconto,
          'porcentagem_desconto' => $this->itemCadastrar->porcentagem_desconto,
          'descricao' => $this->itemCadastrar->descricao,
          'qtde_pessoas' => $this->itemCadastrar->qtde_pessoas,
          'peso' => $this->itemCadastrar->peso,
          'gramagem' => $this->itemCadastrar->gramagem,
          'eh_bebida' => $this->itemCadastrar->eh_bebida,
        ],
      self::ITEM_BEBIDA, self::ITEM_INDUSTR => $comum + [
          'preco' => $this->itemCadastrar->preco,
          'eh_bebida' => $this->itemCadastrar->eh_bebida,
        ],
      default => $comum,
    };
  }

  /**
   * Monta o payload do Item para categoria pizza (sem preço direto no item).
   */
  private function montarPayloadItemPizza(): array
  {
    return [
      'external_id' => $this->itemCadastrar->external_id,
      'categoria_id' => $this->categoriaAtual->id,
      'tipo' => $this->itemCadastrar->tipo,         // PIZ
      'nome' => $this->itemCadastrar->nome,
      'descricao' => $this->itemCadastrar->descricao,
      'tipo_preco' => $this->itemCadastrar->tipo_preco,   // preco_item
      'imagem' => $this->itemCadastrar->imagem,
      'classificacao' => $this->itemCadastrar->classificacaoLimpa(),
    ];
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
