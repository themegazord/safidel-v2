<div class="p-4">
  <x-theme-toggle/>
  <x-header title="Categorias"
            subtitle="Aqui você vai poder cadastrar os itens que seus clientes acessarão quando esse cardápio estiver operante! Personalize e organize suas categorias de produtos no Safi Delivery para criar experiências que conquistam.">
    <x-slot:actions>
      <x-button label="Cadastrar categoria" icon="o-plus" class="btn btn-primary"/>
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
              <x-button label="Editar" icon="o-pencil-square" class="btn btn-secondary"/>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div class="text-lg font-semibold">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
              @forelse($categoria->itens as $item)
                <div class="bg-white border-border hover:shadow-md text-black">
                  <div class="aspect-video relative overflow-hidden rounded-t-lg">
                    <img src="{{ $item->imagem }}" alt="{{ $item->nome }}" class="w-full h-full object-cover">
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
                      <p class="text-sm">
                        {{ $item->descricao }}
                      </p>
                      <div class="flex items-center justify-between pt-2">
                        <span class="text-lg font-bold text-primary">R$ {{ number_format($item->preco, 2, ',', '.') }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              @empty
                <x-alert title="Nenhuma categoria cadastrada" icon="o-exclamation-triangle"/>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    @empty
      <x-alert title="Nenhuma categoria cadastrada" icon="o-exclamation-triangle"/>
    @endforelse
  </div>

</div>
