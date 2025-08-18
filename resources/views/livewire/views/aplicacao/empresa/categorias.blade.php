<div class="p-4">
  <x-theme-toggle/>
  <x-header title="Categorias"
            subtitle="Aqui você vai poder cadastrar os itens que seus clientes acessarão quando esse cardápio estiver operante! Personalize e organize suas categorias de produtos no Safi Delivery para criar experiências que conquistam.">
    <x-slot:actions>
      <x-button label="Cadastrar categoria" icon="o-plus" class="btn btn-primary"
                wire:click="$set('drawerCadastroCategoria', true)"/>
    </x-slot:actions>
  </x-header>

  <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
    @foreach($stats as $key => $stat)
      <div class="bg-primary rounded-lg shadow-md hover:scale-105 transition-all text-white" wire:key="{{ $key }}">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm">{{ $stat['label'] }}</p>
              <p class="text-2xl font-bold">{{ $stat['value'] }}</p>
            </div>
            <x-icon name="{{ $stat['icon'] }}" class="w-6 h-6 text-slate-100"/>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="space-y-8">
    @forelse($categorias as $categoria)
      <div class="bg-primary rounded-lg shadow-md w-full p-4 text-white">
        {{--    card header    --}}
        <div class="pb-4">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="space-y-2">
              <div class="flex items-center gap-3">
                <div class="text-2xl">{{ $categoria->nome }}</div>
                <x-badge @class([
                  'badge text-base',
                  'badge-success' => !$categoria->ehAtivo(),
                  'badge-error' => $categoria->ehAtivo()
                ]) value="{{ !$categoria->ehAtivo() ? 'Ativo' : 'Inativo' }}"/>
              </div>
              <div class="flex items-center gap-4 text-sm">
                <span>{{ $categoria->itens->count() }}  itens</span>
                @if($categoria->tipo !== 'P')
                  <span>.</span>
                  <span>Preço médio: R$ {{number_format($categoria->itens->avg('preco'), 2, ',', '.')}}</span>
                @endif
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-2">
                <x-toggle label="Ativo"/>
              </div>
              <x-button label="Editar" icon="o-pencil-square" class="btn btn-secondary"
                        wire:click="setCategoriaAtual({{ $categoria->id }}, 'edicao')"/>
              <x-button label="Remover" icon="o-trash" class="btn btn-error" wire:click="setCategoriaAtual({{ $categoria->id }}, 'remocao')"/>
            </div>
          </div>
        </div>

        {{--   card body     --}}
        <div class="space-y-4">
          <div class="text-lg font-semibold">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
              @forelse($categoria->itens as $item)
                <div class="bg-white border-border hover:shadow-md text-black">
                  <div class="aspect-video relative overflow-hidden rounded-t-lg">
                    <img src="{{ $item->imagem ?? 'https://placehold.co/400' }}" alt="{{ $item->nome }}"
                         class="w-full h-full object-cover">
                  </div>
                  <div class="p-4">
                    <div class="space-y-2">
                      <div class="flex items-start justify-between">
                        <div class="font-semibold">{{ $item->nome }}</div>
                        <x-badge @class([
                          'badge text-base',
                          'badge-success' => !$item->ehAtivo(),
                          'badge-error' => $item->ehAtivo()
                        ]) value="{{ !$item->ehAtivo() ? 'Ativo' : 'Inativo' }}"/>
                      </div>
                      <p class="text-sm line-clamp-3">
                        {{ $item->descricao }}
                      </p>
                      <div class="flex items-center justify-between pt-2">
                        @if($item->tipo === 'PIZ')
                          <span
                            class="text-lg font-bold text-primary">A partir de R$ {{ number_format($item->precosItemPizza->min('preco'), 2, ',', '.') }}</span>
                        @else
                          <span
                            class="text-lg font-bold text-primary">R$ {{ number_format($item->preco, 2, ',', '.') }}</span>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              @empty
                <div class="col-span-full flex justify-center">
                  <x-alert title="Nenhuma categoria cadastrada" icon="o-exclamation-triangle"/>
                </div>
              @endforelse
            </div>
          </div>

          <x-button class="btn btn-outline w-full" icon="o-plus" label="Adicionar item"/>
        </div>
      </div>
    @empty
      <x-alert title="Nenhuma categoria cadastrada" icon="o-exclamation-triangle"/>
    @endforelse
  </div>

  {{--  drawer cadastro categoria--}}

  <x-drawer wire:model="drawerCadastroCategoria"
            class="w-full lg:w-[55vw]"
            title="Cadastro de categoria"
            subtitle="Insira as informações necessárias para cadastrar uma nova categoria"
            @close="$wire.resetForms()"
            separator
            with-close-button
            close-on-escape
            right>

    @if (is_null($categoriaCadastrar->tipo))
      <div class="flex flex-col gap-4 mt-4">
        <h3 class="text-lg sm:text-xl">Selecione o modelo de categoria para dividir o seu cardápio</h3>

        <x-button class="flex gap-4 h-24 w-full justify-start p-4 flex-nowrap"
                  @click="$wire.$set('categoriaCadastrar.tipo', 'I')">
          <livewire:icons.categorias.itens-principais/>
          <div class="flex flex-col gap-1 items-start">
            <h3 class="text-base sm:text-lg font-bold">Itens principais</h3>
            <p class="text-sm sm:text-base w-full">Comidas, lanches, sobremesas e etc.</p>
          </div>
        </x-button>

        <x-button class="flex gap-4 h-24 w-full justify-start p-4 flex-nowrap"
                  @click="$wire.$set('categoriaCadastrar.tipo', 'P')">
          <livewire:icons.categorias.pizzas/>
          <div class="flex flex-col gap-1 items-start">
            <h3 class="text-base sm:text-lg font-bold">Pizza</h3>
            <p class="text-sm sm:text-base w-full">Defina o tamanho, tipos de massa, bordas e sabores</p>
          </div>
        </x-button>
      </div>
    @endif

    @if ($categoriaCadastrar->tipo === 'I')
      <div class="flex flex-col gap-4 mt-4">
        <div class="border border-purple-400 border-solid rounded-md h-20 flex justify-between items-center px-4 mb-8">
          <div class="flex items-center gap-8">
            <livewire:icons.categorias.itens-principais/>
            <h3 class="text-lg font-bold">Itens principais</h3>
          </div>
          <p class="link" @click="$wire.$set('categoriaCadastrar.tipo', null)">Alterar</p>
        </div>

        <x-input label="Nome da categoria" placeholder="Ex: Marmitas, lanches, sorvetes..."
                 wire:model="categoriaCadastrar.nome" required/>

        <x-slot:actions>
          <x-button class="btn btn-error" type="button"
                    wire:click="$set('drawerCadastroCategoria', false)"
                    label="Cancelar"/>
          <x-button class="btn btn-success" type="submit" label="Cadastrar" spinner="cadastrarCategoria"
                    wire:click="cadastrarCategoria" wire:loading.attr="disabled"/>
        </x-slot:actions>
      </div>
    @endif

    @if ($categoriaCadastrar->tipo === 'P')
      <div class="flex flex-col gap-4 mt-4">
        <x-tabs wire:model.live="tabSelecionada">
          <x-tab name="detalhes" label="Detalhes" class="flex flex-col gap-4 h-auto sm:h-[74vh]">
            <div class="border border-purple-400 rounded-md h-20 flex flex-wrap justify-between items-center px-4 mb-8">
              <div class="flex items-center gap-4 sm:gap-8">
                <livewire:icons.categorias.pizzas/>
                <h3 class="text-base sm:text-lg font-bold">Pizza</h3>
              </div>
              <p class="link text-sm sm:text-base" @click="$wire.$set('categoriaCadastrar.tipo', null)">Alterar</p>
            </div>

            <x-input label="Nome da categoria" placeholder="Ex: Marmitas, lanches, sorvetes..."
                     wire:model="categoriaCadastrar.nome" required>
            </x-input>
          </x-tab>

          <x-tab name="tamanhos" label="Tamanhos" class="flex flex-col gap-4 h-auto sm:h-[74vh]">
            <h1 class="text-xl font-bold ">Tamanhos</h1>
            <p class="text-base-content/50 text-sm mt-1 ">Indique aqui os tamanhos que suas pizzas são produzidas...</p>
            <div class="flex flex-col gap-4">
              @foreach ($categoriaCadastrar->tamanho as $chave => $tamanho)
                <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end" wire:key="{{ $chave }}">
                  <x-input label="Cód. PDV" wire:model="categoriaCadastrar.tamanho.{{ $chave }}.external_id"/>
                  <x-input label="Nome" wire:model="categoriaCadastrar.tamanho.{{ $chave }}.nome"/>
                  <x-input label="Qtde. Pedaços" wire:model="categoriaCadastrar.tamanho.{{ $chave }}.qtde_pedacos"/>
                  <x-choices label="Qtde. Sabores"
                             wire:model.fill="categoriaCadastrar.tamanho.{{ $chave }}.qtde_sabores"
                             :options="$qtd_sabores" option-value="id" option-label="nome" height="max-h-96"/>
                  @if (count($categoriaCadastrar->tamanho) > 1)
                    <x-dropdown>
                      <x-menu-item title="Remover" icon="o-trash" wire:click="removerCategoriaPropriedade('tamanho', {{ $chave }})"/>
                    </x-dropdown>
                  @endif
                </div>
              @endforeach
              <x-button icon="o-plus" class="btn-outline w-full sm:w-3/6" label="Novo tamanho"
                        wire:click="adicionarCategoriaPropriedade('tamanho')"/>
            </div>
          </x-tab>

          <x-tab name="massas" label="Massas" class="flex flex-col gap-4 h-auto sm:h-[74vh]">
            <h1 class="text-xl font-bold ">Massas</h1>
            <p class="text-base-content/50 text-sm mt-1 ">Adicione os tipos de massa disponíveis...</p>
            <div class="flex flex-col gap-4">
              @foreach ($categoriaCadastrar->massa as $chave => $massa)
                <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end" wire:key="massa-{{ $chave }}">
                  <x-input label="Nome" wire:model="categoriaCadastrar.massa.{{ $chave }}.nome"/>
                  <x-input label="Preço" wire:model="categoriaCadastrar.massa.{{ $chave }}.preco" suffix="R$" money
                           locale="pt-BR"/>
                  <x-input label="Código PDV" wire:model="categoriaCadastrar.massa.{{ $chave }}.external_id"/>
                  <x-dropdown>
                    <x-menu-item title="Remover" icon="o-trash" wire:click="removerCategoriaPropriedade('massa', {{ $chave }})"/>
                  </x-dropdown>
                </div>
              @endforeach
              <x-button icon="o-plus" class="btn-outline w-full sm:w-1/6" label="Nova massa"
                        wire:click="adicionarCategoriaPropriedade('massa')"/>
            </div>
          </x-tab>

          <x-tab name="bordas" label="Bordas" class="flex flex-col gap-4 h-auto sm:h-[74vh]">
            <h1 class="text-xl font-bold ">Bordas</h1>
            <p class="text-base-content/50 text-sm mt-1 ">Adicione os tipos de borda disponíveis...</p>
            <div class="flex flex-col gap-4">
              @foreach ($categoriaCadastrar->borda as $chave => $borda)
                <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end" wire:key="borda-{{ $chave }}">
                  <x-input label="Nome" wire:model="categoriaCadastrar.borda.{{ $chave }}.nome"/>
                  <x-input label="Preço" wire:model="categoriaCadastrar.borda.{{ $chave }}.preco" suffix="R$" money
                           locale="pt-BR"/>
                  <x-input label="Código PDV" wire:model="categoriaCadastrar.borda.{{ $chave }}.external_id"/>
                  <x-dropdown>
                    <x-menu-item title="Remover" icon="o-trash" wire:click="removerCategoriaPropriedade('borda', {{ $chave }})"/>
                  </x-dropdown>
                </div>
              @endforeach
              <x-button icon="o-plus" class="btn-outline w-full sm:w-1/6" label="Nova borda"
                        wire:click="adicionarCategoriaPropriedade('borda')"/>
            </div>

            <div class="mt-4">
              <x-errors title="Oops!" description="Verifique os campos abaixo e tente novamente" icon="o-face-frown"/>
            </div>
          </x-tab>
        </x-tabs>

        <x-slot:actions>
          <x-button type="button" class="btn btn-error" wire:click="$set('drawerCadastroCategoria', false)"
                    label="Cancelar"/>
          @switch($tabSelecionada)
            @case('detalhes')
              <x-button class="btn btn-success" type="button" label="Próximo"
                        @click="$wire.$set('tabSelecionada', 'tamanhos')"/>
              @break
            @case('tamanhos')
              <x-button class="btn btn-success" type="button" label="Próximo"
                        @click="$wire.$set('tabSelecionada', 'massas')"/>
              @break
            @case('massas')
              <x-button class="btn btn-success" type="button" label="Próximo"
                        @click="$wire.$set('tabSelecionada', 'bordas')"/>
              @break
            @case('bordas')
              <x-button class="btn btn-success" type="button" label="Cadastrar" wire:click="cadastrarCategoria"/>
              @break
          @endswitch
        </x-slot:actions>
      </div>
    @endif

  </x-drawer>

  {{--  drawer cadastro categoria--}}

  {{-- drawer edicao categoria --}}

  <x-drawer wire:model="drawerEdicaoCategoria"
            class="w-full lg:w-[55vw]"
            title="Edição da categoria"
            subtitle="Insira as informações necessárias para editar uma categoria"
            @close="$wire.resetForms()"
            separator
            with-close-button
            close-on-escape
            right>

    <x-form wire:submit="editarCategoria">
      @if (!is_null($categoriaAtual))
        @if ($categoriaAtual->tipo === 'I')
          <div class="flex flex-col gap-4 mt-4">
            <div
              class="border border-purple-400 border-solid rounded-md h-20 flex justify-between items-center px-4 mb-8">
              <div class="flex items-center gap-8">
                <livewire:icons.categorias.itens-principais/>
                <h3 class="text-lg font-bold">Itens principais</h3>
              </div>
            </div>

            <x-input placeholder="Ex: Marmitas, lanches, sorvetes..." value="{{ $categoriaAtual->nome }}"
                     wire:model.fill="categoriaEdicao.nome" label="Nome da categoria"/>

          </div>
        @endif

        @if ($categoriaAtual->tipo === 'P')
          <x-tabs wire:model.live="tabSelecionada">
            <x-tab name="detalhes" label="Detalhes" class="flex flex-col gap-4 h-[74vh]">
              <div class="border border-purple-400 rounded-md h-20 flex justify-between items-center px-4 mb-8">
                <div class="flex items-center gap-4">
                  <livewire:icons.categorias.pizzas/>
                  <h3 class="text-lg font-bold">Pizza</h3>
                </div>
              </div>
              <x-input placeholder="Ex: Marmitas, lanches, sorvetes..." wire:model="categoriaEdicao.nome" label="Nome da categoria" />
            </x-tab>
            <x-tab name="tamanhos" label="Tamanhos" class="flex flex-col gap-4 h-[74vh] overflow-y-scroll" >
              <h1 class="text-xl font-bold ">Tamanhos</h1>
              <p class="text-base-content/50 text-sm mt-1 ">Indique aqui os tamanhos que suas pizzas são produzidas...</p>
              <div class="flex flex-col gap-4">
                @foreach ($categoriaEdicao->tamanhos as $chave => $tamanho)
                  <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end" wire:key="{{ $chave }}">
                    <x-input class="hidden" wire:model="categoriaEdicao.tamanhos.{{ $chave }}.id"/>
                    <x-input label="Cód. PDV" wire:model="categoriaEdicao.tamanhos.{{ $chave }}.external_id"/>
                    <x-input label="Nome" wire:model="categoriaEdicao.tamanhos.{{ $chave }}.nome"/>
                    <x-input label="Qtde. Pedaços" wire:model="categoriaEdicao.tamanhos.{{ $chave }}.qtde_pedacos"/>
                    <x-choices label="Qtde. Sabores" wire:model.fill="categoriaEdicao.tamanhos.{{ $chave }}.qtde_sabores"
                               :options="$qtd_sabores" option-value="id" option-label="nome"/>
                    @if (count($categoriaEdicao->tamanhos) > 1)
                      <x-dropdown>
                        <x-menu-item title="Remover" icon="o-trash"
                                     wire:click="removerCategoriaPropriedade('tamanhos', {{ $chave }}, {{ $categoriaEdicao->tamanhos[$chave]['id'] }})"/>
                      </x-dropdown>
                    @endif
                  </div>
                @endforeach
                <x-button icon="o-plus" class="btn-outline w-full sm:w-1/3" label="Novo tamanho"
                          wire:click="adicionarCategoriaPropriedade('tamanhos', true)"/>
              </div>
            </x-tab>
            <x-tab name="massas" label="Massas" class="flex flex-col gap-4 h-[74vh] overflow-y-scroll"
                   :disabled="empty($categoriaEdicao->nome)">
              <h1 class="text-xl font-bold ">Massas</h1>
              <p class="text-base-content/50 text-sm mt-1 ">Adicione os tipos de massa disponíveis...</p>
              <div class="flex flex-col gap-4">
                @foreach ($categoriaEdicao->massas as $chave => $massa)
                  <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end" wire:key="{{ $chave }}">
                    <x-input class="hidden" wire:model="categoriaEdicao.massas.{{ $chave }}.id"/>
                    <x-input label="Massa" wire:model="categoriaEdicao.massas.{{ $chave }}.nome"/>
                    <x-input label="Código PDV" wire:model="categoriaEdicao.massas.{{ $chave }}.external_id"/>
                    <x-input label="Preço" wire:model="categoriaEdicao.massas.{{ $chave }}.preco" suffix="R$" money
                             locale="pt-BR"/>
                    @if (count($categoriaEdicao->massas) > 1)
                      <x-dropdown>
                        <x-menu-item title="Remover" icon="o-trash"
                                     wire:click="removerCategoriaPropriedade('massas', {{ $chave }}, {{ $categoriaEdicao->massas[$chave]['id'] }})"/>
                      </x-dropdown>
                    @endif
                  </div>
                @endforeach
                <x-button icon="o-plus" class="btn-outline w-full sm:w-1/3" label="Nova massa"
                          wire:click="adicionarCategoriaPropriedade('massas', true)"/>
              </div>
            </x-tab>
            <x-tab name="bordas" label="Bordas" class="flex flex-col gap-4 h-[74vh] overflow-y-scroll"
                   :disabled="empty($categoriaEdicao->nome)">
              <h1 class="text-xl font-bold ">Bordas</h1>
              <p class="text-base-content/50 text-sm mt-1 ">Adicione os tipos de borda disponíveis...</p>
              <div class="flex flex-col gap-4">
                @foreach ($categoriaEdicao->bordas as $chave => $borda)
                  <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end" wire:key="{{ $chave }}">
                    <x-input class="hidden" wire:model="categoriaEdicao.bordas.{{ $chave }}.id"/>
                    <x-input label="Borda" wire:model="categoriaEdicao.bordas.{{ $chave }}.nome"/>
                    <x-input label="Código PDV" wire:model="categoriaEdicao.bordas.{{ $chave }}.external_id"/>
                    <x-input label="Preço" wire:model="categoriaEdicao.bordas.{{ $chave }}.preco" suffix="R$" money
                             locale="pt-BR"/>
                    @if (count($categoriaEdicao->bordas) > 1)
                      <x-dropdown>
                        <x-menu-item title="Remover" icon="o-trash"
                                     wire:click="removerCategoriaPropriedade('bordas', {{ $chave }}, {{ $categoriaEdicao->bordas[$chave]['id'] }})"/>
                      </x-dropdown>
                    @endif
                  </div>
                @endforeach
                <x-button icon="o-plus" class="btn-outline w-full sm:w-1/3" label="Nova borda"
                          wire:click="adicionarCategoriaPropriedade('bordas', true)"/>
              </div>
            </x-tab>
          </x-tabs>
        @endif
      @endif
    </x-form>

    @if(!is_null($categoriaAtual))
      <x-slot:actions>
        <x-button type="button" class="btn btn-error" @click="$wire.set('drawerEdicaoCategoria', false)"
                  label="Cancelar"/>
        @if($categoriaAtual->tipo === 'I')
          <x-button class="btn btn-success" wire:click="editarCategoria" type="submit" label="Salvar" spinner="editarCategoria" wire:loading.attr="disabled"/>
        @endif

        @if($categoriaAtual->tipo === 'P')
          @switch($tabSelecionada)
            @case('detalhes')
              <x-button class="btn btn-success" type="button" label="Próximo"
                        @click="$wire.$set('tabSelecionada', 'tamanhos')"/>
              @break
            @case('tamanhos')
              <x-button class="btn btn-success" type="button" label="Próximo"
                        @click="$wire.$set('tabSelecionada', 'massas')"/>
              @break
            @case('massas')
              <x-button class="btn btn-success" type="button" label="Próximo"
                        @click="$wire.$set('tabSelecionada', 'bordas')"/>
              @break
            @case('bordas')
              <x-button class="btn btn-success" type="button" label="Salvar" wire:click="editarCategoria" spinner="editarCategoria" wire:loading.attr="disabled"/>
              @break
          @endswitch
        @endif

      </x-slot:actions>
    @endif

  </x-drawer>

  {{-- drawer edicao categoria --}}

  {{--  modal confirmacao remocao categoria--}}

  <x-modal
    wire:model="modalConfirmacaoRemocaoCategoria"
    title="Remover categoria"
    subtitle="Esta ação não poderá ser desfeita."
    icon="o-exclamation-triangle"
    separator
    persistent
  >
    @if($categoriaAtual)
      <div class="space-y-2">
        <p class="text-sm">
          Tem certeza que deseja remover a categoria
          <span class="font-semibold">"{{ $categoriaAtual->nome }}"</span>?
        </p>
        <p class="text-xs text-base-content/60">
          Observação: itens vinculados podem deixar de aparecer para seus clientes.
        </p>
      </div>
    @endif

    <x-slot:actions>
      <x-button
        class="btn btn-ghost"
        label="Cancelar"
        @click="$wire.$set('modalConfirmRemocaoCategoria', false)"
      />
      <x-button
        class="btn btn-error"
        icon="o-trash"
        label="Remover"
        wire:click="removerCategoria"
        spinner="removerCategoria"
        wire:loading.attr="disabled"
      />
    </x-slot:actions>
  </x-modal>


  {{--  modal confirmacao remocao categoria--}}

</div>
