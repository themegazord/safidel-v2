<div class="p-4">
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
                <x-toggle label="Ativo" :checked="!$categoria->trashed()"
                          wire:key="categoria-toggle-{{ $categoria->id }}"
                          wire:click="setCategoriaAtual({{ $categoria->id }}, 'ativacao')"/>
              </div>
              <x-button label="Editar" icon="o-pencil-square" class="btn btn-secondary"
                        wire:click="setCategoriaAtual({{ $categoria->id }}, 'edicao')"/>
              <x-button label="Remover" icon="o-trash" class="btn btn-error"
                        wire:click="setCategoriaAtual({{ $categoria->id }}, 'remocao')"/>
            </div>
          </div>
        </div>

        {{--   card body     --}}
        <div class="space-y-4">
          <div class="text-lg font-semibold">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
              @forelse($categoria->itens()->withTrashed()->get() as $item)
                <div class="bg-white border-border hover:shadow-md rounded-t-lg text-black">
                  <div class="aspect-video relative overflow-hidden rounded-t-lg">
                    <img src="{{ $item->imagem ?? 'https://placehold.co/400' }}"
                         alt="{{ $item->nome }}"
                         class="w-full h-full object-cover">
                  </div>
                  <div class="p-4">
                    <div class="space-y-2">
                      <div class="flex items-start justify-between">
                        <div class="font-semibold">{{ $item->nome }}</div>
                        <div class="flex flex-row items-center gap-4">
                          <x-badge @class([
                          'badge text-base',
                          'badge-success' => !$item->ehAtivo(),
                          'badge-error' => $item->ehAtivo()
                        ]) value="{{ !$item->ehAtivo() ? 'Ativo' : 'Inativo' }}"/>
                          <x-dropdown>
                            <x-slot:trigger>
                              <x-button icon="o-ellipsis-vertical" class="btn-ghost"/>
                            </x-slot:trigger>

                            @if($item->trashed())
                              <x-menu-item title="Ativar item" icon="o-eye"
                                           class="text-base-content hover:bg-base-200"
                                           wire:click.stop="alterarStatusItem({{$item->id}})"
                                           spinner="alterarStatusItem"/>
                            @else
                              <x-menu-item title="Inativar item" icon="o-eye-slash"
                                           class="text-base-content hover:bg-base-200"
                                           wire:click.stop="alterarStatusItem({{$item->id}})"
                                           spinner="alterarStatusItem"/>
                            @endif
                            <x-menu-item title="Clonar item" icon="o-document-duplicate"
                                         class="text-base-content hover:bg-base-200"
                                         wire:click="setItemAtual({{ $item->id }}, {{ $categoria->id }}, 'clonar')"/>
                            <x-menu-item title="Editar item" icon="o-pencil-square"
                                         class="text-base-content hover:bg-base-200"
                                         wire:click="setItemAtual({{ $item->id }}, {{ $categoria->id }}, 'editar')"/>
                            <x-menu-item title="Remover item" icon="o-trash"
                                         class="text-base-content hover:bg-base-200"
                                         wire:click="setItemAtual({{ $item->id }}, {{ $categoria->id }}, 'remover')"/>
                          </x-dropdown>

                        </div>
                      </div>
                      <p class="text-sm line-clamp-3">
                        {{ $item->descricao }}
                      </p>
                      <div class="flex items-center pt-2">
                        @if($item->tipo === 'PIZ')
                          <span
                            class="text-lg font-bold text-primary">A partir de R$ {{ number_format($item->precosItemPizza->min('preco'), 2, ',', '.') }}</span>
                        @else
                          @if ($item->desconto)
                            <div class="flex flex-col md:flex-row gap-2">
                              <span
                                class="text-lg font-bold text-error line-through">R$ {{ number_format($item->preco, 2, ',', '.') }}</span>
                              <span
                                class="text-lg font-bold text-primary">R$ {{ number_format($item->valor_desconto, 2, ',', '.') }}</span>
                            </div>
                          @else
                            <span
                              class="text-lg font-bold text-primary">R$ {{ number_format($item->preco, 2, ',', '.') }}</span>
                          @endif
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

          <x-button class="btn btn-outline w-full" icon="o-plus" label="Adicionar item"
                    wire:click="setCategoriaAtual({{ $categoria->id }}, 'cadastro_item')"/>
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
              <p class="link text-sm sm:text-base" @click="$wire.$set('categoriaCadastrar.tipo', null)">
                Alterar</p>
            </div>

            <x-input label="Nome da categoria" placeholder="Ex: Marmitas, lanches, sorvetes..."
                     wire:model="categoriaCadastrar.nome" required>
            </x-input>
          </x-tab>

          <x-tab name="tamanhos" label="Tamanhos" class="flex flex-col gap-4 h-auto sm:h-[74vh]">
            <h1 class="text-xl font-bold ">Tamanhos</h1>
            <p class="text-base-content/50 text-sm mt-1 ">Indique aqui os tamanhos que suas pizzas são
              produzidas...</p>
            <div class="flex flex-col gap-4">
              @foreach ($categoriaCadastrar->tamanho as $chave => $tamanho)
                <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end"
                     wire:key="{{ $chave }}">
                  <x-input label="Cód. PDV"
                           wire:model="categoriaCadastrar.tamanho.{{ $chave }}.external_id"/>
                  <x-input label="Nome" wire:model="categoriaCadastrar.tamanho.{{ $chave }}.nome"/>
                  <x-input label="Qtde. Pedaços"
                           wire:model="categoriaCadastrar.tamanho.{{ $chave }}.qtde_pedacos"/>
                  <x-choices label="Qtde. Sabores"
                             wire:model.fill="categoriaCadastrar.tamanho.{{ $chave }}.qtde_sabores"
                             :options="$qtd_sabores" option-value="id" option-label="nome"
                             height="max-h-96"/>
                  @if (count($categoriaCadastrar->tamanho) > 1)
                    <x-dropdown>
                      <x-menu-item title="Remover" icon="o-trash"
                                   wire:click="removerCategoriaPropriedade('tamanho', {{ $chave }})"/>
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
                <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end"
                     wire:key="massa-{{ $chave }}">
                  <x-input label="Nome" wire:model="categoriaCadastrar.massa.{{ $chave }}.nome"/>
                  <x-input label="Preço" wire:model="categoriaCadastrar.massa.{{ $chave }}.preco"
                           suffix="R$" money
                           locale="pt-BR"/>
                  <x-input label="Código PDV"
                           wire:model="categoriaCadastrar.massa.{{ $chave }}.external_id"/>
                  <x-dropdown>
                    <x-menu-item title="Remover" icon="o-trash"
                                 wire:click="removerCategoriaPropriedade('massa', {{ $chave }})"/>
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
                <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end"
                     wire:key="borda-{{ $chave }}">
                  <x-input label="Nome" wire:model="categoriaCadastrar.borda.{{ $chave }}.nome"/>
                  <x-input label="Preço" wire:model="categoriaCadastrar.borda.{{ $chave }}.preco"
                           suffix="R$" money
                           locale="pt-BR"/>
                  <x-input label="Código PDV"
                           wire:model="categoriaCadastrar.borda.{{ $chave }}.external_id"/>
                  <x-dropdown>
                    <x-menu-item title="Remover" icon="o-trash"
                                 wire:click="removerCategoriaPropriedade('borda', {{ $chave }})"/>
                  </x-dropdown>
                </div>
              @endforeach
              <x-button icon="o-plus" class="btn-outline w-full sm:w-1/6" label="Nova borda"
                        wire:click="adicionarCategoriaPropriedade('borda')"/>
            </div>

            <div class="mt-4">
              <x-errors title="Oops!" description="Verifique os campos abaixo e tente novamente"
                        icon="o-face-frown"/>
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
              <x-button class="btn btn-success" type="button" label="Cadastrar"
                        wire:click="cadastrarCategoria"/>
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
              <x-input placeholder="Ex: Marmitas, lanches, sorvetes..." wire:model="categoriaEdicao.nome"
                       label="Nome da categoria"/>
            </x-tab>
            <x-tab name="tamanhos" label="Tamanhos" class="flex flex-col gap-4 h-[74vh] overflow-y-scroll">
              <h1 class="text-xl font-bold ">Tamanhos</h1>
              <p class="text-base-content/50 text-sm mt-1 ">Indique aqui os tamanhos que suas pizzas são
                produzidas...</p>
              <div class="flex flex-col gap-4">
                @foreach ($categoriaEdicao->tamanhos as $chave => $tamanho)
                  <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end"
                       wire:key="{{ $chave }}">
                    <x-input class="hidden" wire:model="categoriaEdicao.tamanhos.{{ $chave }}.id"/>
                    <x-input label="Cód. PDV"
                             wire:model="categoriaEdicao.tamanhos.{{ $chave }}.external_id"/>
                    <x-input label="Nome" wire:model="categoriaEdicao.tamanhos.{{ $chave }}.nome"/>
                    <x-input label="Qtde. Pedaços"
                             wire:model="categoriaEdicao.tamanhos.{{ $chave }}.qtde_pedacos"/>
                    <x-choices label="Qtde. Sabores"
                               wire:model.fill="categoriaEdicao.tamanhos.{{ $chave }}.qtde_sabores"
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
                  <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end"
                       wire:key="{{ $chave }}">
                    <x-input class="hidden" wire:model="categoriaEdicao.massas.{{ $chave }}.id"/>
                    <x-input label="Massa" wire:model="categoriaEdicao.massas.{{ $chave }}.nome"/>
                    <x-input label="Código PDV"
                             wire:model="categoriaEdicao.massas.{{ $chave }}.external_id"/>
                    <x-input label="Preço" wire:model="categoriaEdicao.massas.{{ $chave }}.preco"
                             suffix="R$" money
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
                  <div class="grid grid-cols-1 sm:flex sm:grid-cols-none gap-4 items-end"
                       wire:key="{{ $chave }}">
                    <x-input class="hidden" wire:model="categoriaEdicao.bordas.{{ $chave }}.id"/>
                    <x-input label="Borda" wire:model="categoriaEdicao.bordas.{{ $chave }}.nome"/>
                    <x-input label="Código PDV"
                             wire:model="categoriaEdicao.bordas.{{ $chave }}.external_id"/>
                    <x-input label="Preço" wire:model="categoriaEdicao.bordas.{{ $chave }}.preco"
                             suffix="R$" money
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
              <div class="mt-4">
                <x-errors title="Oops!" description="Verifique os campos abaixo e tente novamente"
                          icon="o-face-frown"/>
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
          <x-button class="btn btn-success" wire:click="editarCategoria" type="submit" label="Salvar"
                    spinner="editarCategoria" wire:loading.attr="disabled"/>
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
              <x-button class="btn btn-success" type="button" label="Salvar" wire:click="editarCategoria"
                        spinner="editarCategoria" wire:loading.attr="disabled"/>
              @break
          @endswitch
        @endif

      </x-slot:actions>
    @endif

  </x-drawer>
  {{-- drawer edicao categoria --}}

  {{-- Cadastro de itens --}}
  <x-drawer wire:model="drawerCadastroItem"
            class="w-full lg:w-[55vw]"
            title="Cadastro de itens"
            subtitle="Insira as informações necessárias para cadastrar um item"
            @close="$wire.resetForms()"
            separator
            with-close-button
            close-on-escape
            right>
    @if (!is_null($categoriaAtual))
      @if ($categoriaAtual->tipo === 'I')
        <x-form wire:submit.prevent="cadastraItem">
          {{-- Seletor do tipo de item --}}
          <div class="flex flex-col gap-2 mb-4">
            <p class="text-sm text-base-content/70">Selecione o tipo do item</p>
            <div class="join">
              <button type="button"
                      class="btn join-item"
                      :class="{ 'btn-primary': @js($tipoItemNormal) === 'PRE' }"
                      @click="$wire.set('tipoItemNormal', 'PRE')">
                Preparado
              </button>
              <button type="button"
                      class="btn join-item"
                      :class="{ 'btn-primary': @js($tipoItemNormal) === 'BEB' }"
                      @click="$wire.set('tipoItemNormal', 'BEB')">
                Bebida
              </button>
              <button type="button"
                      class="btn join-item"
                      :class="{ 'btn-primary': @js($tipoItemNormal) === 'IND' }"
                      @click="$wire.set('tipoItemNormal', 'IND')">
                Industrializado
              </button>
            </div>
          </div>

          <x-tabs wire:model.live="tabCadastroItemSelecionada">
            <x-tab name="detalhes" label="Detalhes" class="h-[82vh]">
              <div class="h-[70vh] overflow-y-auto">
                <div class="flex flex-col gap-4 sm:flex-row mb-4">
                  <x-file wire:model.live="fotoTemporaria" accept="image/jpeg">
                    <img src="{{ 'https://placehold.co/300' }}"
                         class="rounded max-w-[300px] max-h-[300px]"/>
                  </x-file>
                  <div class="w-full flex flex-col gap-4">
                    <x-input label="Categoria" value="{{ $categoriaAtual->nome }}" readonly
                             required/>
                    <x-input label="Nome do prato" wire:model="itemCadastrar.nome" required/>
                    <x-input label="Código PDV" wire:model="itemCadastrar.external_id"
                             :required="true"/>
                    {{-- Descrição apenas para item preparado (PRE) --}}
                    @if ($tipoItemNormal === 'PRE')
                      <x-textarea label="Descrição" wire:model="itemCadastrar.descricao"
                                  placeholder="Legumes, salada e um carboidrato a sua escolha."
                                  rows="5"/>
                      <x-alert icon="o-exclamation-circle" class="alert alert-info">
                        Ajude seus clientes a entender o tamanho dos itens do seu cardápio.
                      </x-alert>
                      <x-select
                        label="Pra qual tamanho de fome é esse item?"
                        :options="$qtd_pessoas"
                        wire:model="itemCadastrar.qtde_pessoas"
                      />
                      <x-input label="Peso" wire:model="itemCadastrar.peso">
                        <x-slot:append>
                          <x-select :options="$gramagem"
                                    wire:model="itemCadastrar.gramagem"
                                    option-value="valor"
                                    option-label="label"
                                    class="rounded-s-none bg-base-200"/>
                        </x-slot:append>
                      </x-input>
                    @endif
                  </div>
                </div>
              </div>
            </x-tab>

            <x-tab name="preco_estoque" label="Preço e Estoque" class="h-[82vh]">
              @if (!$itemCadastrar->desconto)
                <div class="flex flex-col md:flex-row gap-8 items-end">
                  <div class="w-full md:w-48">
                    <x-input label="Preço" wire:model.blur="itemCadastrar.preco" suffix="R$" money
                             locale="pt-BR"
                             required/>
                  </div>
                  <x-button label="Aplicar desconto" class="btn btn-outline"
                            @click="$wire.$set('itemCadastrar.desconto', true)"/>
                </div>
              @else
                <p class="text-xl font-bold">Desconto direto no item</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <x-input wire:model.live.debounce.250ms="itemCadastrar.preco" label="Preço atual"
                           required readonly/>
                  <x-input label="Novo preço"
                           wire:model.live.debounce.250ms="itemCadastrar.valor_desconto" suffix="R$"
                           money locale="pt-BR"/>
                  <x-input label="Desconto em %" type="number" min="0"
                           wire:model.live.debounce.250ms="itemCadastrar.porcentagem_desconto"
                           suffix="%"/>
                </div>
                <div class="divider"></div>
                <p class="link link-primary" @click="$wire.$set('itemCadastrar.desconto', false)">
                  Remover desconto</p>
              @endif
            </x-tab>

            <x-tab name="classificacao" label="Classificação" class="h-[82vh]">
              {{-- Toggle de bebida (usa a mesma flag para PRE/BEB/IND, conforme já usado no back) --}}
              <div
                class="border-solid border-4 border-purple-600 rounded-lg flex flex-col md:flex-row justify-between items-center h-auto md:h-24 px-4 mb-4 gap-4">
                <p class="text-base md:text-lg">Este item é uma bebida?</p>
                <x-toggle wire:model.change="itemCadastrar.eh_bebida"/>
              </div>

              {{-- Se não é bebida: restrições alimentares gerais --}}
              @if (!$itemCadastrar->eh_bebida)
                <p class="text-xl font-bold">Restrições alimentares:</p>
                <p class="text-base-content/50 text-sm mt-1">
                  Indique se seu item é adequado a restrições alimentares diversas para atrair a
                  atenção de clientes.
                </p>
                <x-alert icon="o-exclamation-triangle"
                         class="alert-warning my-4 text-base text-sm mt-1">
                  <strong>Lembre-se que você é responsável por todas as informações sobre os
                    itens.</strong>
                </x-alert>

                <div class="flex flex-col gap-6">
                  @foreach ([['vegetariano', 'Vegetariano', 'Sem carne de nenhum tipo'], ['vegano', 'Vegano', 'Sem produtos de origem animal, como carne, ovo ou leite'], ['organico', 'Orgânico', 'Cultivado sem agrotóxicos, segundo a lei 10.831'], ['sem-acucar', 'Sem açúcar', 'Não contém nenhum tipo de açúcar (cristal, orgânico, mascavo etc.)'], ['zero-lactose', 'Zero lactose', 'Não contém lactose, ou seja, leite e seus derivados'],] as [$id, $label, $description])
                    <div class="flex items-start gap-4">
                      <input type="checkbox" name="{{ $id }}" id="{{ $id }}"
                             wire:model="itemCadastrar.classificacao.{{ $loop->index }}.status">
                      <div class="flex gap-4 items-center">
                        <livewire:icons.classificacao.{{ $id }} width
                        ="30px" height="30px" estilo="fill-purple-600" />
                        <div class="flex flex-col gap-1">
                          <p class="text-lg">{{ $label }}</p>
                          <p class="text-sm">{{ $description }}</p>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                {{-- Se é bebida: classificações de bebida e restrições específicas --}}
                <p class="text-xl">Classificação da bebida:</p>
                <p class="text-sm md:text-base">
                  Indique se sua bebida é adequada a restrições alimentares diversas para atrair a
                  atenção de clientes.
                </p>

                <div class="flex flex-col gap-6 my-4">
                  @foreach ([['bebida_gelada', 'Bebida Gelada', 'Da geladeira direto para o consumidor'], ['bebida_alcolica', 'Bebida alcoólica', 'De 0,5% a 54% em volume, destilados, fermentados etc'], ['bebida_natural', 'Bebida natural', 'Preparados na hora com frutas frescas'],] as [$id, $label, $description])
                    <div class="flex items-start gap-4">
                      <input type="checkbox" name="{{ $id }}" id="{{ $id }}"
                             wire:model="itemCadastrar.classificacao.{{ $loop->index + 5 }}.status">
                      <div class="flex gap-4 items-center">
                        <livewire:icons.classificacao.{{ $id }} width
                        ="30px" height="30px" estilo="fill-purple-600" />
                        <div class="flex flex-col gap-1">
                          <p class="text-lg">{{ $label }}</p>
                          <p class="text-sm">{{ $description }}</p>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>

                <p class="text-xl mt-2">Restrições:</p>
                <p class="text-sm md:text-base mb-4">Indique se sua bebida possui algum ingrediente que
                  seja uma
                  restrição
                  alimentar.</p>
                @foreach ([['zero_lactose', 'Zero Lactose', 'Não contém lactose, ou seja, leite e seus derivados'], ['bebida_diet', 'Bebida diet', 'Sem adição de açúcares'],] as [$id, $label, $description])
                  <div class="flex items-start gap-4">
                    <input type="checkbox" name="{{ $id }}" id="{{ $id }}"
                           wire:model="itemCadastrar.classificacao.{{ $loop->index + 8 }}.status">
                    <div class="flex gap-4 items-center">
                      <livewire:icons.classificacao.{{ $id }} width
                      ="30px" height="30px" estilo="fill-purple-600" />
                      <div class="flex flex-col gap-1">
                        <p class="text-lg">{{ $label }}</p>
                        <p class="text-sm">{{ $description }}</p>
                      </div>
                    </div>
                  </div>
                @endforeach
              @endif

              <div class="mt-4">
                <x-errors title="Oops!" description="Verifique os campos abaixo e tente novamente"
                          icon="o-face-frown"/>
              </div>
            </x-tab>
          </x-tabs>
        </x-form>
      @endif


      @if ($categoriaAtual->tipo === 'P')
        <x-form wire:submit="cadastraItem" class="flex flex-col gap-8">
          <div class="flex flex-col gap-4 mt-4">
            <x-tabs wire:model.live="tabCadastroItemSelecionada">
              <x-tab name="detalhes" label="Detalhes" class="h-[74vh]">
                <div class="flex flex-col gap-4 sm:flex-row mb-4">
                  <x-file wire:model.live="fotoTemporaria" accept="image/jpeg">
                    <img src="{{ 'https://placehold.co/300' }}"
                         class="rounded max-w-[300px] max-h-[300px]"/>
                  </x-file>

                  <div class="w-full">
                    <div class="mb-4">
                      <x-input label="Categoria" value="{{ $categoriaAtual->nome }}" readonly
                               required/>
                    </div>
                    <div class="mb-4">
                      <x-input label="Nome do prato" wire:model="itemCadastrar.nome" required/>
                    </div>
                    <div class="mb-4">
                      <x-input label="Código PDV" wire:model="itemCadastrar.external_id"
                               required/>
                    </div>
                    <x-textarea label="Descrição" wire:model="itemCadastrar.descricao"
                                placeholder="Pizza artesanal de frango com catupiry e borda de cheddar"
                                rows="5"/>
                  </div>
                </div>
              </x-tab>
              <x-tab name="precos" label="Preço" class="h-[74vh]">
                <div class="flex flex-row gap-8 flex-wrap justify-center">
                  @foreach ($itemCadastrar->precos as $chave => $precoTamanho)
                    <x-card
                      class="w-[200px] flex flex-col items-center border-primary-500 border-solid border-2 rounded-md p-4 mb-4"
                      wire:key="{{ $chave }}">
                      <livewire:icons.itens.mini-pizza wire:key="{{ $chave }}"/>
                      <div class="flex flex-row gap-4 m-auto mb-4">
                        <input type="checkbox"
                               wire:model.change="itemCadastrar.precos.{{ $chave }}.status">
                        <label for="statusItemPizza">{{ $precoTamanho['tamanho'] }}</label>
                      </div>
                      <x-input wire:model="itemCadastrar.precos.{{ $chave }}.preco"
                               prefix="R$"
                               locale="pt-BR"
                               money
                               inline
                               :disabled="!$itemCadastrar->precos[$chave]['status']"/>
                    </x-card>
                  @endforeach
                </div>
              </x-tab>
              <x-tab name="classificacao" label="Classificação"
                     class="h-[74vh]">
                <h1 class="font-bold text-xl mb-4">Restrições alimentares</h1>
                <p class="text-lg mb-4">Indique se seu item é adequado a restrições alimentares diversas
                  para atrair a
                  atenção de clientes</p>
                <x-alert icon="o-exclamation-triangle" class="alert-warning mb-4">
                  <strong>Lembre-se que você é responsável por todas as informações sobre os
                    itens.</strong>
                </x-alert>

                <div class="flex flex-col gap-4">
                  <div class="flex gap-4">
                    <input type="checkbox" name="vegetariano" id="vegetariano"
                           wire:model="itemCadastrar.classificacao.0.status">
                    <div class="flex gap-4 items-center">
                      <livewire:icons.classificacao.vegetariano width="30px" height="30px"
                                                                estilo="fill-purple-600"/>
                      <div class="flex flex-col gap-2">
                        <p class="text-lg">Vegetariano</p>
                        <p>Sem carne de nenhum tipo</p>
                      </div>
                    </div>
                  </div>

                  <div class="flex gap-4">
                    <input type="checkbox" name="vegano" id="vegano"
                           wire:model="itemCadastrar.classificacao.1.status">
                    <div class="flex gap-4 items-center">
                      <livewire:icons.classificacao.vegano width="30px" height="30px"
                                                           estilo="fill-purple-600"/>
                      <div class="flex flex-col gap-2">
                        <p class="text-lg">Vegano</p>
                        <p>Sem produtos de origem animal, como carne, ovo ou leite</p>
                      </div>
                    </div>
                  </div>

                  <div class="flex gap-4">
                    <input type="checkbox" name="organico" id="organico"
                           wire:model="itemCadastrar.classificacao.2.status">
                    <div class="flex gap-4 items-center">
                      <livewire:icons.classificacao.organico width="30px" height="30px"
                                                             estilo="fill-purple-600"/>
                      <div class="flex flex-col gap-2">
                        <p class="text-lg">Orgânico</p>
                        <p>Cultivado sem agrotóxicos, segundo a lei 10.831</p>
                      </div>
                    </div>
                  </div>

                  <div class="flex gap-4">
                    <input type="checkbox" name="sem-acucar" id="sem-acucar"
                           wire:model="itemCadastrar.classificacao.3.status">
                    <div class="flex gap-4 items-center">
                      <livewire:icons.classificacao.sem-acucar width="30px" height="30px"
                                                               estilo="fill-purple-600"/>
                      <div class="flex flex-col gap-2">
                        <p class="text-lg">Sem açúcar</p>
                        <p>Não contém nenhum tipo de açúcar (cristal, orgânico, mascavo
                          etc.)</p>
                      </div>
                    </div>
                  </div>

                  <div class="flex gap-4">
                    <input type="checkbox" name="zero-lactose" id="zero-lactose"
                           wire:model="itemCadastrar.classificacao.4.status">
                    <div class="flex gap-4 items-center">
                      <livewire:icons.classificacao.zero-lactose width="30px" height="30px"
                                                                 estilo="fill-purple-600"/>
                      <div class="flex flex-col gap-2">
                        <p class="text-lg">Zero lactose</p>
                        <p>Não contém lactose, ou seja, leite e seus derivados</p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <x-errors title="Oops!" description="Verifique os campos abaixo e tente novamente"
                            icon="o-face-frown"/>
                </div>
              </x-tab>
            </x-tabs>
          </div>
        </x-form>
      @endif
      <x-slot:actions>
        <x-button label="Cancelar" @click="$wire.set('drawerCadastroItem', false)" class="btn btn-error"/>
        @if($categoriaAtual->tipo === 'P')
          @switch($tabCadastroItemSelecionada)
            @case('detalhes')
              <x-button label="Próximo" @click="$wire.$set('tabCadastroItemSelecionada', 'precos')"
                        class="btn btn-success"/>
              @break
            @case('precos')
              <x-button label="Próximo" @click="$wire.$set('tabCadastroItemSelecionada', 'classificacao')"
                        class="btn btn-success"/>
              @break
            @case('classificacao')
              <x-button label="Cadastrar" wire:click="cadastraItem" spinner="cadastraItem"
                        wire:loading.attr="disabled"
                        class="btn btn-success"/>
              @break
          @endswitch
        @else
          @switch($tabCadastroItemSelecionada)
            @case('detalhes')
              <x-button label="Próximo" @click="$wire.$set('tabCadastroItemSelecionada', 'preco_estoque')"
                        class="btn btn-success"/>
              @break
            @case('preco_estoque')
              <x-button label="Próximo" @click="$wire.$set('tabCadastroItemSelecionada', 'classificacao')"
                        class="btn btn-success"/>
              @break
            @case('classificacao')
              <x-button label="Cadastrar" wire:click="cadastraItem" spinner="cadastraItem"
                        wire:loading.attr="disabled"
                        class="btn btn-success"/>
              @break
          @endswitch
        @endif
      </x-slot:actions>
    @endif
  </x-drawer>
  {{-- fim Cadastro de itens --}}

  {{-- Drawer de edição de item --}}
  <x-drawer wire:model="drawerEdicaoItem"
            class="w-full lg:w-[55vw]"
            title="Edição de item"
            subtitle="Atualize as informações do item selecionado"
            @close="$wire.resetForms()"
            separator
            with-close-button
            close-on-escape
            right>
    @if (!is_null($categoriaAtual) && !is_null($itemAtual))
      <x-form wire:submit.prevent="editarItem">
        <x-tabs wire:model.live="tabCadastroItemSelecionada">
          <x-tab name="detalhes" label="Detalhes" class="h-[82vh]">
            <div class="h-[70vh] overflow-y-auto">
              <div class="flex flex-col gap-4 sm:flex-row mb-4">
                <x-file wire:model.live="fotoTemporariaEdicao" accept="image/jpeg">
                  <img src="{{ $itemEditar->imagem ?? 'https://placehold.co/300' }}"
                       class="rounded max-w-[300px] max-h-[300px]"/>
                </x-file>
                <div class="w-full flex flex-col gap-4">
                  <x-input label="Categoria" value="{{ $categoriaAtual->nome }}" readonly required/>
                  <x-input label="Nome do item" wire:model="itemEditar.nome" required/>
                  <x-input label="Código PDV" wire:model="itemEditar.external_id" required/>

                  @if ($itemEditar->tipo === 'PRE' || $itemEditar->tipo === 'PIZ')
                    <x-textarea label="Descrição" wire:model="itemEditar.descricao"
                                placeholder="Descreva brevemente o item." rows="5"/>
                  @endif

                  @if ($itemEditar->tipo === 'PRE')
                    <x-alert icon="o-exclamation-circle" class="alert alert-info">
                      Ajude seus clientes a entender o tamanho dos itens do seu cardápio.
                    </x-alert>
                    <x-select
                      label="Pra qual tamanho de fome é esse item?"
                      :options="$qtd_pessoas"
                      wire:model="itemEditar.qtde_pessoas"
                    />
                    <x-input label="Peso" wire:model="itemEditar.peso">
                      <x-slot:append>
                        <x-select :options="$gramagem"
                                  wire:model="itemEditar.gramagem"
                                  option-value="valor"
                                  option-label="label"
                                  class="rounded-s-none bg-base-200"/>
                      </x-slot:append>
                    </x-input>
                  @endif
                </div>
              </div>
            </div>
          </x-tab>

          @if ($itemEditar->tipo !== 'PIZ')
            <x-tab name="preco_estoque" label="Preço e Estoque" class="h-[82vh]">
              @if (!$itemEditar->desconto)
                <div class="flex flex-col md:flex-row gap-8 items-end">
                  <div class="w-full md:w-48">
                    <x-input label="Preço" wire:model.blur="itemEditar.preco" suffix="R$" money
                             locale="pt-BR"
                             required/>
                  </div>
                  <x-button label="Aplicar desconto" class="btn btn-outline"
                            @click="$wire.$set('itemEditar.desconto', true)"/>
                </div>
              @else
                <p class="text-xl font-bold">Desconto direto no item</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <x-input wire:model.live.debounce.250ms="itemEditar.preco" label="Preço atual"
                           required readonly/>
                  <x-input label="Novo preço"
                           wire:model.live.debounce.250ms="itemEditar.valor_desconto" suffix="R$"
                           money locale="pt-BR"/>
                  <x-input label="Desconto em %" type="number" min="0"
                           wire:model.live.debounce.250ms="itemEditar.porcentagem_desconto"
                           suffix="%"/>
                </div>
                <div class="divider"></div>
                <p class="link link-primary" @click="$wire.$set('itemEditar.desconto', false)">Remover
                  desconto</p>
              @endif
            </x-tab>
            <x-tab name="complementos" label="Complementos" class="h-[82vh]">
              <div class="h-[70vh] overflow-y-scroll"
                x-data="{
                  copiaComplemento: $wire.entangle('copia_complemento').live
                }"
              >
                <h1 class="text-xl font-bold">Complementos</h1>
                <p class="text-base-content/50 text-sm mt-1">Seu item tem complementos pra ficar ainda mais gostoso?
                  Indique
                  aqui.</p>

                @if (!$itemAtual->grupo_complemento->isEmpty())
                  <x-card title="Grupos de complementos que compõem esse item"
                          class="border-2 border-primary border-solid my-4" shadow separator>
                    @foreach ($itemAtual->grupo_complemento as $grupo)
                      <x-card class="border-2 border-primary border-solid mb-4" shadow separator>
                        <x-slot:title>
                          <h1 class="text-xl font-bold">{{ $grupo->nome }}</h1>
                        </x-slot:title>
                        <x-slot:subtitle>
                          @php
                            $contagemComplementos = count($grupo->complementos);
                          @endphp
                          <p>
                            @if ($contagemComplementos <= 1)
                              1 opção
                            @else
                              {{ $contagemComplementos }} opções
                            @endif
                          </p>
                        </x-slot:subtitle>
                        <x-slot:menu>
                          <div class="flex items-center gap-4 mr-8">
                            @if ($grupo->obrigatoriedade)
                              <x-badge value="Obrigatório" class="badge-primary"/>
                            @else
                              <x-badge value="Opcional"/>
                            @endif
                            <x-button icon="o-pencil-square" spinner class="btn-sm"
                                      tooltip="Editar item"
                                      wire:click="setGrupoComplemento({{ $grupo->id }}, 'editar')"/>
                            <x-button icon="o-trash"
                                      wire:click="setGrupoComplemento({{ $grupo->id }}, 'remover')"
                                      spinner
                                      class="btn-sm" tooltip="Remover item"/>
                          </div>
                        </x-slot:menu>

                        @foreach ($grupo->complementos as $complemento)
                          <div class="flex justify-between px-4">
                            <p>{{ $complemento->nome }}</p>
                            <p><b>R$ {{ $complemento->preco }}</b></p>
                          </div>
                          <div class="divider divider-primary"></div>
                        @endforeach
                      </x-card>
                    @endforeach
                  </x-card>
                @endif

                <div class="flex flex-col sm:flex-row gap-4 my-4">
                  <x-button label="Adicionar um complemento" icon="o-plus" class="btn btn-outline"
                            wire:click="preparaCadastroGrupoComplementos"
                            x-bind:disabled="copiaComplemento"/>
                  <x-button label="Copiar complemento de outro item" icon="o-document-duplicate"
                            class="btn btn-outline"
                            @click="copiaComplemento = true"/>
                </div>

                <x-card class="border-2 border-primary border-solid my-4"
                        title="Copiar grupo existente" shadow
                        separator
                        x-show="copiaComplemento">
                  <x-slot:menu>
                    <x-button class="btn btn-outline" label="Cancelar"
                              @click="copiaComplemento = false"/>
                  </x-slot:menu>
                  <x-slot:actions>
                    <x-button class="btn" label="Copiar" icon="o-document-duplicate"
                              wire:click="copiarComplementoOutroItem"/>
                  </x-slot:actions>

                  <div class="mb-4">
                    <x-select
                      :options="$categoriaAtual->cardapio->categorias()->where('tipo', 'I')->get()"
                      option-value="id" option-label="nome"
                      placeholder="Selecione a categoria..."
                      wire:model.live="categoriaSelecionadaCopiaComplemento"
                      label="Primeiro selecione a categoria"
                      required />
                  </div>

                  @if (!is_null($categoriaSelecionadaCopiaComplemento))
                    <div class="mb-4">
                      <x-select
                        :options="$categoriaAtual->cardapio->categorias()->find($categoriaSelecionadaCopiaComplemento)->itens"
                        option-value="id" option-label="nome"
                        label="Depois selecione o item"
                        placeholder="Selecione o item..."
                        wire:model.live="itemSelecionadoCopiaComplemento"
                        required />
                    </div>
                  @endif
                </x-card>
              </div>
            </x-tab>
          @else
            <x-tab name="preco_estoque" label="Preço" class="h-[74vh]">
              <div class="flex flex-row gap-8 flex-wrap justify-center">
                @foreach ($itemEditar->precos as $chave => $precoTamanho)
                  <x-card
                    class="w-[200px] flex flex-col items-center border-primary-500 border-solid border-2 rounded-md p-4 mb-4"
                    wire:key="{{ $chave }}">
                    <livewire:icons.itens.mini-pizza wire:key="{{ $chave }}"/>
                    <div class="flex flex-row gap-4 m-auto mb-4">
                      <input type="checkbox"
                             wire:model.change="itemEditar.precos.{{ $chave }}.status">
                      <label for="statusItemPizza">{{ $precoTamanho['tamanho'] }}</label>
                    </div>
                    <x-input wire:model="itemEditar.precos.{{ $chave }}.preco"
                             prefix="R$"
                             locale="pt-BR"
                             money
                             inline
                             :disabled="!$itemEditar->precos[$chave]['status']"/>
                  </x-card>
                @endforeach
              </div>
            </x-tab>
          @endif

          <x-tab name="classificacao" label="Classificação" class="h-[82vh]">
            <div
              class="border-solid border-4 border-purple-600 rounded-lg flex flex-col md:flex-row justify-between items-center h-auto md:h-24 px-4 mb-4 gap-4">
              <p class="text-base md:text-lg">Este item é uma bebida?</p>
              <x-toggle wire:model.change="itemEditar.eh_bebida"/>
            </div>

            @if (!$itemEditar->eh_bebida)
              <p class="text-xl font-bold">Restrições alimentares:</p>
              <p class="text-base-content/50 text-sm mt-1">
                Indique se seu item é adequado a restrições alimentares diversas para atrair a atenção
                de clientes.
              </p>
              <x-alert icon="o-exclamation-triangle" class="alert-warning my-4 text-base text-sm mt-1">
                <strong>Lembre-se que você é responsável por todas as informações sobre os
                  itens.</strong>
              </x-alert>

              <div class="flex flex-col gap-6">
                @foreach ([['vegetariano', 'Vegetariano', 'Sem carne de nenhum tipo'], ['vegano', 'Vegano', 'Sem produtos de origem animal, como carne, ovo ou leite'], ['organico', 'Orgânico', 'Cultivado sem agrotóxicos, segundo a lei 10.831'], ['sem-acucar', 'Sem açúcar', 'Não contém nenhum tipo de açúcar (cristal, orgânico, mascavo etc.)'], ['zero-lactose', 'Zero lactose', 'Não contém lactose, ou seja, leite e seus derivados'],] as [$id, $label, $description])
                  <div class="flex items-start gap-4">
                    <input type="checkbox" name="{{ $id }}" id="{{ $id }}"
                           wire:model="itemEditar.classificacao.{{ $loop->index }}.status">
                    <div class="flex gap-4 items-center">
                      <livewire:icons.classificacao.{{ $id }} width
                      ="30px" height="30px" estilo="fill-purple-600" />
                      <div class="flex flex-col gap-1">
                        <p class="text-lg">{{ $label }}</p>
                        <p class="text-sm">{{ $description }}</p>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <p class="text-xl">Classificação da bebida:</p>
              <p class="text-sm md:text-base">
                Indique se sua bebida é adequada a restrições alimentares diversas para atrair a atenção
                de clientes.
              </p>

              <div class="flex flex-col gap-6 my-4">
                @foreach ([['bebida_gelada', 'Bebida Gelada', 'Da geladeira direto para o consumidor'], ['bebida_alcolica', 'Bebida alcoólica', 'De 0,5% a 54% em volume, destilados, fermentados etc'], ['bebida_natural', 'Bebida natural', 'Preparados na hora com frutas frescas'],] as [$id, $label, $description])
                  <div class="flex items-start gap-4">
                    <input type="checkbox" name="{{ $id }}" id="{{ $id }}"
                           wire:model="itemEditar.classificacao.{{ $loop->index + 5 }}.status">
                    <div class="flex gap-4 items-center">
                      <livewire:icons.classificacao.{{ $id }} width
                      ="30px" height="30px" estilo="fill-purple-600" />
                      <div class="flex flex-col gap-1">
                        <p class="text-lg">{{ $label }}</p>
                        <p class="text-sm">{{ $description }}</p>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>

              <p class="text-xl mt-2">Restrições:</p>
              <p class="text-sm md:text-base mb-4">Indique se sua bebida possui algum ingrediente que seja
                uma restrição
                alimentar.</p>
              @foreach ([['zero_lactose', 'Zero Lactose', 'Não contém lactose, ou seja, leite e seus derivados'], ['bebida_diet', 'Bebida diet', 'Sem adição de açúcares'],] as [$id, $label, $description])
                <div class="flex items-start gap-4">
                  <input type="checkbox" name="{{ $id }}" id="{{ $id }}"
                         wire:model="itemEditar.classificacao.{{ $loop->index + 8 }}.status">
                  <div class="flex gap-4 items-center">
                    <livewire:icons.classificacao.{{ $id }} width
                    ="30px" height="30px" estilo="fill-purple-600" />
                    <div class="flex flex-col gap-1">
                      <p class="text-lg">{{ $label }}</p>
                      <p class="text-sm">{{ $description }}</p>
                    </div>
                  </div>
                </div>
              @endforeach
            @endif

            <div class="mt-4">
              <x-errors title="Oops!" description="Verifique os campos abaixo e tente novamente"
                        icon="o-face-frown"/>
            </div>
          </x-tab>
        </x-tabs>
      </x-form>
      <x-slot:actions>
        <x-button label="Cancelar" @click="$wire.set('drawerEdicaoItem', false)" class="btn btn-error"/>
        @switch($tabCadastroItemSelecionada)
          @case('detalhes')
            <x-button label="Próximo" @click="$wire.$set('tabCadastroItemSelecionada', 'preco_estoque')"
                      class="btn btn-success"/>
            @break
          @case('preco_estoque')
            @if ($itemEditar->tipo !== 'PIZ')
              <x-button label="Próximo" @click="$wire.$set('tabCadastroItemSelecionada', 'complementos')"
                        class="btn btn-success"/>
            @else
              <x-button label="Próximo" @click="$wire.$set('tabCadastroItemSelecionada', 'classificacao')"
                        class="btn btn-success"/>
            @endif
            @break
          @case('complementos')
            <x-button label="Próximo" @click="$wire.$set('tabCadastroItemSelecionada', 'classificacao')"
                      class="btn btn-success"/>
            @break
          @case('classificacao')
            <x-button label="Salvar alterações" wire:click="editarItem" type="submit" spinner="editarItem"
                      wire:loading.attr="disabled"
                      class="btn btn-success"/>
            @break
        @endswitch
      </x-slot:actions>
    @endif
  </x-drawer>
  {{-- fim Drawer de edição de item --}}

  {{-- Cadastro de complemento itens --}}
  <x-drawer wire:model="drawerCadastroComplementoItem"
            class="w-full lg:w-[55vw]"
            right
            title="Novo grupo de complementos"
            @close="$wire.resetFormGrupoComplemento()"
            separator
            with-close-button>
    <x-tabs wire:model.live="tabCadastroItemComplemento">
      <x-tab name="detalhes" label="Detalhes" class="h-[82vh]">
        <div class="mb-4">
          <x-input label="Nome do grupo de complementos" placeholder="Nome" wire:model.blur="grupoComplementoCadastro.nome"
                   required/>
        </div>

        <p class="mb-2"><b>Obrigatoriedade</b></p>
        <p>Indique se essa categoria é necessária para pedir o prato</p>

        <div class="flex flex-col gap-6 my-4">
          <x-group
            label="Obrigatoriedade"
            :options="[
              ['id' => '0', 'name' => 'Opcional'],
              ['id' => '1', 'name' => 'Obrigatório'],
            ]"
            wire:model.live="grupoComplementoCadastro.obrigatoriedade"
          />
          <p class="text-xs text-base-content/60 -mt-4">
            Opcional: o cliente pode ou não selecionar. Obrigatório: o cliente deve selecionar 1 ou mais itens.
          </p>
        </div>

        <p class="text-lg mb-2">Quantidade</p>
        <p>Indique quantos itens podem ser selecionados</p>

        <div class="flex gap-4 my-4">
          <x-input label="Máximo" class="appearance-none" type="number" min="0"
                   wire:model="grupoComplementoCadastro.qtd_maxima"/>
        </div>
      </x-tab>
      <x-tab name="complementos"
             label="Complementos"
             class="h-[82vh]">
        <div class="h-[70vh] overflow-y-scroll">
          <x-errors title="Oops!" description="Verifique os campos abaixo e tente novamente"
                    icon="o-face-frown"/>
          <div class="flex flex-col gap-4">
            @foreach ($grupoComplementoCadastro->complementos as $chaveComplemento => $complemento)
              <x-card title="Complemento" separator shadow wire:key="grupo-comp-{{ $chaveComplemento }}">
                <div class="mb-4">
                  <x-input label="Nome do complemento" wire:model="grupoComplementoCadastro.complementos.{{ $chaveComplemento }}.nome"
                           required/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 sm:gap-4 mb-4">
                  <x-input label="Descrição"
                  wire:model="grupoComplementoCadastro.complementos.{{ $chaveComplemento }}.descricao"/>
                  <x-input label="Código PDV"
                           wire:model="grupoComplementoCadastro.complementos.{{ $chaveComplemento }}.external_id"/>
                </div>

                <div class="flex flex-wrap gap-8 items-center">
                  <x-input label="Preço"
                           wire:model="grupoComplementoCadastro.complementos.{{ $chaveComplemento }}.preco"
                           money
                           locale="pt-BR"/>

                  <x-group label="Status do complemento"
                           wire:model="grupoComplementoCadastro.complementos.{{ $chaveComplemento }}.status" :options="[
                    ['id' => '0', 'name' => 'Pausar'],
                    ['id' => '1', 'name' => 'Ativado'],
                  ]"/>

                </div>

                <x-slot:menu>
                  <x-button icon="o-trash" wire:click="removeComplementoNoGrupoComplemento({{ $chaveComplemento }}, 'cadastro')"
                            spinner="removeComplementoNoGrupoComplemento" class="btn btn-outline btn-error" tooltip="Remover item"
                            :disabled="count($grupoComplementoCadastro->complementos) <= 1"/>
                </x-slot:menu>
              </x-card>
            @endforeach
          </div>

          <x-button label="Adicionar complemento"
                    icon="o-plus"
                    class="btn btn-outline btn-success mb-4"
                    wire:click="adicionarComplementoNoGrupoComplemento('cadastro')"/>
        </div>
      </x-tab>
    </x-tabs>
    <x-slot:actions>
      <x-button label="Cancelar" @click="$wire.$set('drawerCadastroComplementoItem', false)" class="btn btn-error"/>
      @switch($tabCadastroItemComplemento)
        @case('detalhes')
          <x-button label="Próximo"
                    @click="$wire.$set('tabCadastroItemComplemento', 'complementos')" class="btn btn-success"/>
          @break
        @case('complementos')
          <x-button label="Finalizar" wire:click="finalizarCadastroGrupoComponentes" wire:loading.attr="disabled" spinner="finalizarCadastroGrupoComponentes" class="btn btn-success"/>
          @break
      @endswitch
    </x-slot:actions>
  </x-drawer>
  {{-- fim Cadastro de complemento itens --}}

  {{-- Drawer de edição de grupo de complementos --}}
  <x-drawer wire:model="drawerEdicaoComplementoItem"
            class="w-full lg:w-[55vw]"
            right
            @close="$wire.resetFormGrupoComplemento()"
            title="Editando grupo de complementos"
            separator
            with-close-button>
    @if (!is_null($grupoComplementoAtual))
      <x-tabs wire:model.live="tabEdicaoItemComplemento">
        {{-- Aba: Detalhes --}}
        <x-tab name="detalhes" label="Detalhes" class="h-[82vh]">
          <div class="mb-4">
            <x-input placeholder="Nome"
                     wire:model="grupoComplementoEdicao.nome" label="Nome do grupo de complementos" required/>
          </div>

          <p class="mb-2"><b>Obrigatoriedade</b></p>
          <p>Indique se essa categoria é necessária para pedir o prato</p>

          <div class="flex flex-col gap-6 my-4">
            <x-group
              label="Obrigatoriedade"
              :options="[
              ['id' => '0', 'name' => 'Opcional'],
              ['id' => '1', 'name' => 'Obrigatório'],
            ]"
              wire:model.live="grupoComplementoEdicao.obrigatoriedade"
            />
            <p class="text-xs text-base-content/60 -mt-4">
              Opcional: o cliente pode ou não selecionar. Obrigatório: o cliente deve selecionar 1 ou mais itens.
            </p>
          </div>

          <p class="text-lg mb-2"><b>Quantidade</b></p>
          <p>Indique quantos itens podem ser selecionados</p>

          <div class="flex gap-4 my-4">
            <x-input class="appearance-none"
                     type="number"
                     min="0"
                     wire:model="grupoComplementoEdicao.qtd_maxima">
              <x-slot:label><b>Máximo</b></x-slot:label>
            </x-input>
          </div>
        </x-tab>

        {{-- Aba: Complementos --}}
        <x-tab name="complementos"
               label="Complementos"
               :disabled="empty($grupoComplementoEdicao->nome)"
               class="h-[82vh]">
          <div class="h-[70vh] overflow-y-scroll">
            <div class="flex flex-col gap-4">
              @foreach ($grupoComplementoEdicao->complementos as $chaveComplemento => $complemento)
                <x-card title="Complemento" separator shadow wire:key="edit-comp-{{ $chaveComplemento }}">
                  <div class="mb-4">
                    <x-input wire:model="grupoComplementoEdicao.complementos.{{ $chaveComplemento }}.nome" label="Nome do complemento" required />
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                      <x-input label="Descrição" wire:model="grupoComplementoEdicao.complementos.{{ $chaveComplemento }}.descricao" />
                    </div>
                    <div>
                      <x-input label="Código PDV" wire:model="grupoComplementoEdicao.complementos.{{ $chaveComplemento }}.external_id" />
                    </div>
                  </div>

                  <div class="flex flex-wrap gap-8 items-center">
                    <x-input label="Preço" wire:model="grupoComplementoEdicao.complementos.{{ $chaveComplemento }}.preco"
                             money
                             locale="pt-BR"
                              required/>

                    <x-group
                      label="Status do complemento"
                      :options="[
                      ['id' => '0', 'name' => 'Pausar'],
                      ['id' => '1', 'name' => 'Ativado'],
                    ]"
                      wire:model="grupoComplementoEdicao.complementos.{{ $chaveComplemento }}.status"
                    />
                  </div>

                  <x-slot:menu>
                    <x-button icon="o-trash"
                              class="btn btn-outline btn-error"
                              tooltip="Remover complemento"
                              wire:click="removeComplementoNoGrupoComplemento({{ $chaveComplemento }}, 'edicao')"
                              spinner="removerComplementoEdicao"
                              :disabled="count($grupoComplementoEdicao->complementos) <= 1"/>
                  </x-slot:menu>
                </x-card>
              @endforeach
            </div>
          </div>

          <x-button label="Adicionar complemento" icon="o-plus"
                    class="btn btn-outline"
                    wire:click="adicionarComplementoNoGrupoComplemento('edicao')"/>

        </x-tab>
      </x-tabs>
      <x-slot:actions>
        <x-button label="Cancelar" @click="$wire.$set('drawerEdicaoComplementoItem', false)" class="btn btn-error"/>
        @switch($tabEdicaoItemComplemento)
          @case('detalhes')
            <x-button label="Próximo"
                      @click="$wire.$set('tabEdicaoItemComplemento', 'complementos')" class="btn btn-success"/>
            @break
          @case('complementos')
            <x-button label="Finalizar" wire:click="finalizarEdicaoGrupoComponentes" wire:loading.attr="disabled" spinner="finalizarEdicaoGrupoComponentes" class="btn btn-success"/>
            @break
        @endswitch
      </x-slot:actions>
    @endif
  </x-drawer>
  {{-- Drawer de edição de grupo de complementos --}}

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
        @click="$wire.$set('modalConfirmacaoRemocaoCategoria', false)"
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

  {{-- modal confirmacao clonagem item --}}
  <x-modal wire:model="modalConfirmacaoClonarItem" title="Clonar item" icon="o-document-duplicate">
    <div class="space-y-2">
      <p>Tem certeza que deseja clonar este item?</p>
      @isset($itemAtual)
        <p class="text-sm text-base-content/70">Item: <span
            class="font-semibold">{{ $itemAtual->nome ?? '' }}</span></p>
      @endisset
      <x-alert icon="o-information-circle" class="alert-warning mt-2">
        A cópia será criada inativa por padrão. Você poderá editar antes de ativar.
      </x-alert>
    </div>

    <x-slot:actions>
      <x-button class="btn btn-ghost" @click="$wire.set('modalConfirmacaoClonarItem', false)" label="Cancelar"/>
      <x-button class="btn btn-primary" wire:click="clonarItem" spinner="clonarItem" label="Confirmar clonagem"/>
    </x-slot:actions>
  </x-modal>
  {{-- modal confirmacao clonagem item --}}

  {{-- modal confirmacao remocao item --}}
  <x-modal wire:model="modalConfirmacaoRemocaoItem" title="Remover item" icon="o-trash">
    <div class="space-y-2">
      <p>Tem certeza que deseja remover este item? Essa ação é irreversível.</p>
      @isset($itemAtual)
        <p class="text-sm text-base-content/70">
          Item: <span class="font-semibold">{{ $itemAtual->nome ?? '' }}</span>
        </p>
      @endisset
      <x-alert icon="o-exclamation-triangle" class="alert-error mt-2">
        Esta operação removerá o item e seus dados relacionados.
      </x-alert>
    </div>

    <x-slot:actions>
      <x-button class="btn btn-ghost" @click="$wire.set('modalConfirmacaoRemocaoItem', false)" label="Cancelar"/>
      <x-button class="btn btn-error" wire:click="removerItem" spinner="removerItem" label="Remover definitivamente"/>
    </x-slot:actions>
  </x-modal>
  {{-- modal confirmacao remocao item --}}

  {{-- modal confirmacao remocao grupo complemento --}}
  <x-modal wire:model="modalConfirmacaoRemocaoGrupoComplemento">
    <x-slot:title>
      Confirmar remoção
    </x-slot:title>

    <div class="space-y-2">
      <p>Tem certeza que deseja remover o grupo de complementos abaixo?</p>
      <div class="p-3 rounded bg-base-200">
        <div class="font-semibold">
          {{ $grupoComplementoAtual?->nome ?? 'Grupo selecionado' }}
        </div>
        <div class="text-sm opacity-70">
          ID: {{ $grupoComplementoAtual?->id ?? '-' }}
        </div>
      </div>
      <p class="text-sm text-warning">
        Esta ação é permanente e não poderá ser desfeita.
      </p>
    </div>

    <x-slot:actions>
      <x-button
        label="Cancelar"
        class="btn-ghost"
        @click="$wire.set('modalConfirmacaoRemocaoGrupoComplemento', false)"
      />
      <x-button
        label="Remover"
        icon="o-trash"
        class="btn-error"
        wire:click="removerGrupoComplemento"
        wire:loading.attr="disabled"
        spinner="removerGrupoComplemento"
      />
    </x-slot:actions>
  </x-modal>
  {{-- modal confirmacao remocao grupo complemento --}}

</div>
