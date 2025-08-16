<div class="p-4">
  <x-header title="Gerenciamento de cardápio"
            subtitle="Otimize seu cardápio e aumente as vendas! Gerencie seus pratos de forma inteligente e conquiste mais clientes."
            separator>
    <x-slot:actions>
      <x-button label="Cadastrar cardápio 📄" class="btn btn-primary" @click="$wire.drawerCadastroCardapio = true"/>
    </x-slot:actions>
  </x-header>

  <div class="flex flex-wrap gap-4 mt-4">
    @forelse($cardapios as $cardapio)
      <x-card title="{{ $cardapio->nome }}" subtitle="{{ $cardapio->descricao }}">
        <x-slot:menu>
          <x-dropdown>
            <x-slot:trigger>
              <x-button icon="o-ellipsis-vertical" class="btn-ghost"/>
            </x-slot:trigger>
            <x-menu-item title="Edição" icon="o-pencil-square" wire:click="setCardapioAtual({{ $cardapio->id }}, 'edicao')"/>
            <x-menu-item title="Remoção" icon="o-trash" wire:click="setCardapioAtual({{ $cardapio->id }}, 'remover')"/>
          </x-dropdown>
        </x-slot:menu>

        <x-slot:actions>
          <x-button class="btn btn-primary" label="Gerenciar cardápio" icon="o-cog" link="{{ route('aplicacao.empresa.categorias', ['cardapio_id' => $cardapio->id]) }}"/>
        </x-slot:actions>
      </x-card>
    @empty
      Não contêm cardápios cadastrados.
    @endforelse
  </div>

  {{-- Cadastro de cardápios --}}
  <x-drawer wire:model="drawerCadastroCardapio" title="Cadastro de cardápio"
            subtitle="Insira as informações necessárias para cadastrar um novo cardápio" class="w-11/12 lg:w-1/3"
            @close="$wire.resetForm()"
            separator
            with-close-button
            close-on-escape
            right>
    <x-form wire:submit="cadastrarCardapio">
      <x-input label="Nome do cardápio" placeholder="Insira o nome do cardápio" wire:model="cardapio.nome"/>
      <x-input label="Descrição do cardápio" placeholder="Insira a descrição do cardápio"
               wire:model="cardapio.descricao"/>
      <x-choices label="Dias de funcionamento" wire:model="cardapio.dias_funcionamento" :options="$diasSemana"
                 option-value="id" option-label="nome" icon="o-calendar-date-range" height="max-h-96"
                 hint="Selecione os dias que o cardápio vai funcionar"/>
      <x-choices label="Tipo de funcionamento" wire:model="cardapio.tipo_funcionamento" :options="$tipoFuncionamento"
                 single/>
    </x-form>
    <x-slot:actions>
      <x-button label="Cadastrar" spinner="cadastrarCardapio" wire:click="cadastrarCardapio"
                class=" btn btn-primary"/>
    </x-slot:actions>
  </x-drawer>
  {{-- Fim cadastro de cardápios --}}


  {{-- Edicao de cardapios --}}
  <x-drawer wire:model="drawerEdicaoCardapio"
            title="Edição de cardápio"
            subtitle="Insira as informações necessárias para edição do cardápio" class="w-11/12 lg:w-1/3"
            @close="$wire.resetForm()"
            separator
            with-close-button
            close-on-escape
            right>
    <x-form wire:submit="editarCardapio">
      @if(!is_null($cardapioAtual))
        <x-input label="Nome do cardápio" placeholder="Insira o nome do cardápio" wire:model="cardapioEdicao.nome"/>
        <x-input label="Descrição do cardápio" placeholder="Insira a descrição do cardápio"
                 wire:model="cardapioEdicao.descricao"/>
        <x-choices label="Dias de funcionamento" wire:model="cardapioEdicao.dias_funcionamento"
                   :options="$diasSemana" option-value="id" option-label="nome" icon="o-calendar-date-range"
                   height="max-h-96"
                   hint="Selecione os dias que o cardápio vai funcionar"/>
        <x-choices label="Tipo de funcionamento" wire:model="cardapioEdicao.tipo_funcionamento"
                   :options="$tipoFuncionamento" single/>
      @endif
    </x-form>
    <x-slot:actions>
      <x-button label="Salvar" spinner="editarCardapio" wire:click="editarCardapio"
                class=" btn btn-primary"/>
    </x-slot:actions>
  </x-drawer>
  {{--     Fim edicao de cardapios--}}

  {{-- inicio modal de confirmação de remocao de cardapio --}}
  <x-modal wire:model="confirmRemocao" title="Confirmar remoção" separator>
    @if($cardapioAtual)
      <x-slot:subtitle>
        Tem certeza que deseja remover o cardápio “{{ $cardapioAtual->nome }}”? Esta ação não pode ser desfeita.
      </x-slot:subtitle>
      <div class="flex items-center gap-3 text-sm">
        <x-icon name="o-exclamation-triangle" class="text-error"/>
        <span>Itens e categorias associados podem ser afetados.</span>
      </div>

      <x-slot:actions>
        <x-button class="btn-ghost" label="Cancelar" @click="confirmRemocao = false"/>
        <x-button class="btn btn-error" icon="o-trash" label="Remover" wire:click="removerCardapio({{ $cardapioAtual->id }})" wire:loading.attr="disabled" spinner="removerCardapio"/>
      </x-slot:actions>
    @endif
  </x-modal>

  {{-- fim modal de confirmação de remocao de cardapio --}}


</div>
