<div class="container mx-auto px-4 py-8">
  <x-header title="Meu desempenho" subtitle="Aqui você irá ver um resumo breve das operações da sua loja.">
    <x-slot:actions>
      @if ($empresa->tokenIfood !== null)
        <x-toggle :checked="$recebimentoPedidosIfood"
                  wire:click="$toggle('recebimentoPedidosIfood')" label="Está recebendo pedidos do ifood?" right/>
      @endif
      <x-button class="btn btn-circle btn-outline" wire:click="copiaLinkLoja('delivery')">
        <livewire:icons.pedidos.delivery fill="#646EE4"/>
      </x-button>
      <x-button class="btn btn-circle btn-outline" wire:click="copiaLinkLoja('mesa')">
        <livewire:icons.pedidos.mesa fill="#646EE4"/>
      </x-button>
      <x-button class="btn btn-circle btn-outline" wire:click="copiaLinkLoja('retirada')">
        <livewire:icons.pedidos.retirada fill="#646EE4"/>
      </x-button>
      @script
      <script>
        Livewire.on('copia-link', (e) => {
          const linkElement = e[0].link;

          const tempInput = document.createElement('input');
          tempInput.style.position = 'absolute';
          tempInput.style.left = '-9999px';
          document.body.appendChild(tempInput);

          tempInput.value = linkElement;

          tempInput.select();
          tempInput.setSelectionRange(0, 99999);

          try {
            document.execCommand('copy');
          } catch (err) {
          }

          document.body.removeChild(tempInput);
        });
      </script>
      @endscript
    </x-slot:actions>
  </x-header>
  {{-- Alert Section --}}
  @if (count($necessidades) > 0)
    <x-alert icon="o-exclamation-triangle" class="alert-warning">
      <ul class="flex flex-col gap-2">
        @foreach ($necessidades as $necessidade)
          <li>{{ $necessidade['mensagem'] }}<a href="{{ $necessidade['link'] }}" class="link">Clique aqui para
              arrumar</a></li>
        @endforeach
      </ul>
    </x-alert>
  @endif

  {{-- Today's Metrics --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <x-card separator>
      <x-slot:title box-class="text-lg font-semibold flex justify-between items-center">
        Faturamento de hoje
      </x-slot:title>
      <x-slot:menu>
        <x-icon name="o-information-circle" class="cursor-pointer w-6 h-6"/>
      </x-slot:menu>
      <p class="text-lg font-bold">R$ {{ number_format($financeiroPedidosHoje->sum('total'), 2, ',', '.') }}</p>
    </x-card>
    <x-card separator>
      <x-slot:title box-class="text-lg font-semibold flex justify-between items-center">
        Em análise agora
      </x-slot:title>
      <p class="text-lg font-bold text-center">{{ $pedidosHoje->where('status', 'pendente')->count() }}</p>
    </x-card>
    <x-card separator>
      <x-slot:title box-class="text-lg font-semibold flex justify-between items-center">
        Em produção agora
      </x-slot:title>
      <p class="text-lg font-bold text-center">{{ $pedidosHoje->where('status', 'sendo preparado')->count() }}</p>
    </x-card>
    <x-card separator>
      <x-slot:title box-class="text-lg font-semibold flex justify-between items-center">
        Pronto para entrega
      </x-slot:title>
      <p class="text-lg font-bold text-center">{{ $pedidosHoje->where('status', 'pronto para entrega')->count() }}</p>
    </x-card>
  </div>

  {{-- Date Filter --}}
  <div class="flex flex-col-reverse gap-4 sm:gap-0 sm:flex-row justify-between items-center mt-12 mb-4">
    <p class="text-lg">{{ date('d/m/Y', strtotime("-$diasFiltro days")) }} a {{ date('d/m/Y', strtotime('yesterday')) }}
    </p>
    @php
      $dias = [
      ['id' => 7, 'name' => 'Últimos 7 dias'],
      ['id' => 15, 'name' => 'Últimos 15 dias'],
      ['id' => 30, 'name' => 'Últimos 30 dias'],
      ];
    @endphp
    <x-group :options="$dias" wire:model.live="diasFiltro" />
  </div>

  {{-- Period Summary --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <x-card title="Faturamento">
      <p class="text-lg font-bold">R$ {{ number_format($financeiroPedidos->sum('total'), 2, ',', '.') }}</p>
    </x-card>
    <x-card title="Pedidos">
      <p class="text-lg font-bold">{{ $pedidos->where('status', 'entregue')->count() }}</p>
    </x-card>
    <x-card title="Ticket médio">
      @php
        $totalPedidos = $financeiroPedidos->sum('total');
        $pedidosEntregues = $pedidos->where('status', 'entregue')->count();
        $media = $pedidosEntregues === 0 ? 0 : $totalPedidos / $pedidosEntregues;
      @endphp

      <p class="text-lg font-bold">R$ {{ number_format($media, 2, ',', '.') }}</p>

    </x-card>
  </div>

  {{-- Charts Section --}}
  <x-card class="border-border">
    <div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Chart Placeholder --}}
        <div class="h-80 flex items-center justify-center text-muted-foreground border border-dashed rounded-lg">
          <x-chart wire:model="chartPedidosModalidade" />
        </div>

        {{-- Stats --}}
        <div class="flex flex-col justify-center gap-6">
          @foreach($chartPedidosModalidade['data']['datasets'][0]['data'] as  $type)
            <div class="flex items-center justify-between p-4 border border-border rounded-lg">
              <div class="flex items-center gap-3">
                <div class="w-4 h-4 rounded-full" style="background-color: {{ $chartPedidosModalidade['data']['datasets'][0]['backgroundColor'][$loop->index] }}"></div>
                <span class="font-medium">{{ $type }}</span>
              </div>
              <div class="text-2xl font-bold" style="color: {{ $chartPedidosModalidade['data']['datasets'][0]['backgroundColor'][$loop->index] }}">
                {{ $chartPedidosModalidade['data']['labels'][$loop->index] }}
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </x-card>

</div>
