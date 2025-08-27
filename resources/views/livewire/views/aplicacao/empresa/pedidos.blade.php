<div
  x-data="{
    updateStatus(id, to) {
      $wire.atualizarStatus({ id, status: to });
    }
  }"
>
  <x-header title="Gerenciamento de Pedidos" subtitle="Acompanhe todos os pedidos do restaurante" separator>
    <x-slot:actions>
      <x-input icon="o-bolt" placeholder="Consulte..."/>
    </x-slot:actions>
  </x-header>

  <div wire:poll.10s="recarregarPedidos" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">
    @php
      // Status do banco => label da UI e cor
      $statusMap = [
        'pendente' => ['label' => 'Em análise',         'color' => 'bg-yellow-500'],
        'sendo preparado' => ['label' => 'Em produção',  'color' => 'bg-blue-500'],
        'pronto para entrega' => ['label' => 'Pronto para entrega', 'color' => 'bg-green-500'],
        'sendo entregue' => ['label' => 'Sendo entregue','color' => 'bg-orange-500'],
      ];
    @endphp

    @foreach($statusMap as $dbStatus => $meta)
      @php
        $grupoPedidos = $this->filteredOrders->get($dbStatus, collect());
      @endphp

      <div class="space-y-4" wire:key="coluna-status-{{ $dbStatus }}">
        <div class="flex items-center gap-2">
          <div class="w-3 h-3 rounded-full {{ $meta['color'] }}"></div>
          <h2 class="font-semibold text-lg">{{ $meta['label'] }}</h2>
          <x-badge class="ml-auto {{ $meta['color'] }}" value="{{ $grupoPedidos->count() }}"/>
        </div>

        <div
          class="space-y-3 min-h-[200px] p-2 rounded-lg border-2 border-dashed border-border"
          x-sort="(item) => updateStatus(item, '{{ $dbStatus }}')"
          x-sort:group="pedidos"
          aria-label="Lista de pedidos: {{ $meta['label'] }}"
        >
          @forelse($grupoPedidos as $order)
            <div
              class="bg-card rounded-lg p-4 shadow cursor-move"
              x-sort:item="{{ $order['id'] }}"
              wire:key="pedido-{{ $order['id'] }}"
              wire:click="setPedidoSelecionado({{ $order['id'] }})"
            >
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold">Pedido #{{ $order['number'] }}</h3>
                <x-badge value="{{ $meta['label'] }}"
                         class="{{ $meta['color'] }} whitespace-nowrap px-2 py-1 inline-flex items-center"/>
              </div>
              <div class="text-sm text-muted-foreground">
                <strong>{{ $order['customerName'] }}</strong><br>
                {{ $order['type'] }} <br>
                {{ $order['createdAt'] }}
              </div>
              <div class="pt-2 border-t mt-2">
                <div class="text-sm text-muted-foreground mb-1">
                  {{ implode(', ', $order['items']) }}
                </div>
                <div class="font-semibold text-primary">
                  R$ {{ number_format($order['total'], 2, ',', '.') }}
                </div>
              </div>
            </div>
          @empty
            <div class="bg-card p-6 text-center">
              <p class="text-muted-foreground">Nenhum pedido encontrado</p>
            </div>
          @endforelse
        </div>
      </div>
    @endforeach
  </div>

  {{--  modal de visualizacao do pedido--}}
  <x-modal wire:model="modalDetalhePedido" class="backdrop-blur" box-class="w-full max-w-5xl p-4 min-h-[95vh]"
           persistent>
    @if ($pedidoSelecionado)
      <x-header separator>
        <x-slot:title>
          <div class="flex items-center gap-4">
            <span class="flex items-center gap-4">
            <x-icon name="{{ $this->defineIconPedido($pedidoSelecionado->status) }}"/>
          Pedido #{{ $pedidoSelecionado->id }}
          </span>
            <x-badge value="{{ $pedidoSelecionado->defineStatusPedidoCliente() }}"
                     class="{{ $pedidoSelecionado->defineCorDependendoStatus() }} whitespace-nowrap px-2 py-1 inline-flex items-center"/>

            <x-badge value="{{ $this->getTempoPedidoAberto($pedidoSelecionado->created_at ) }}"
                     class="ml-2"/>
          </div>
        </x-slot:title>
        <x-slot:actions>
          <x-button icon="o-trash" class="btn-outline btn-error"/>
          <x-button icon="o-printer" class="btn-outline"/>
          <x-button icon="o-arrow-right" class="btn-outline btn-primary"/>
          <x-button icon="o-x-mark" class="btn btn-ghost btn-error"
                    @click="$wire.set('modalDetalhePedido', false)"/>
        </x-slot:actions>
      </x-header>

      <div class="w-full max-h-[90vh] overflow-y-auto p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Coluna Esquerda: Cliente e Entrega -->
          <div class="space-y-6 col-span-1">
            <!-- Cliente -->
            <div class="bg-base-100 rounded-lg p-4 border">
              <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                <x-icon name="o-user" class="w-5 h-5 text-primary"/>
                Cliente
              </h3>
              <div class="space-y-3">
                <div>
                  <p class="font-medium">{{ $pedidoSelecionado->cliente->nome ?? '—' }}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <x-icon name="o-phone" class="w-4 h-4 text-base-content/60"/>
                    <span class="text-sm text-base-content/60">{{ $pedidoSelecionado->cliente->telefone ?? '—' }}</span>
                  </div>
                </div>
                <p class="text-xs text-base-content/60">Já
                  pediu {{ $pedidoSelecionado->cliente->pedidos()->where('status', 'entregue')->get()->count() ?? 0 }}
                  vezes.</p>
              </div>
            </div>

            <!-- Entrega -->
            @php
              $tipoPedido = $pedidoSelecionado->defineTipoPedido() ?? ($pedidoSelecionado->tipo ?? '—');
              $endereco = $pedidoSelecionado->cliente->endereco->enderecoFormatado();
            @endphp
            @if(($tipoPedido === 'Delivery' || $tipoPedido === 'delivery') && $endereco)
              <div class="bg-base-100 rounded-lg p-4 border">
                <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                  <x-icon name="o-map-pin" class="w-5 h-5 text-primary"/>
                  Entrega
                </h3>
                <p class="text-sm leading-relaxed">{{ $endereco }}</p>
              </div>
            @endif

            <!-- Pagamento -->
            <div class="bg-base-100 rounded-lg p-4 border">
              <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                <x-icon name="o-credit-card" class="w-5 h-5 text-primary"/>
                Forma de pagamento
              </h3>
              <p class="text-sm">{{ $pedidoSelecionado->financeiro->defineFormaPagamento() ?? '—' }}</p>
            </div>
          </div>

          <!-- Coluna Direita: Itens e Totais -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Itens -->
            <div class="bg-base-100 rounded-lg p-4 border">
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg">Itens do pedido</h3>
{{--                <x-button icon="o-pencil-square" variant="ghost" size="sm" class="text-base-content/60"--}}
{{--                          tooltip="Editar itens"/>--}}
              </div>

              <div class="space-y-4">
                @forelse(($pedidoSelecionado->itens ?? []) as $item)
                  <div class="border-b pb-4 last:border-b-0 last:pb-0">
                    <div class="flex flex-col gap-2 items-start mb-2">
                      @if($item->tipo === 'I')
                        <div class="flex justify-between w-full">
                          <p class="font-medium">
                            {{ $item->quantidade ?? 1 }}
                            x {{ $item->nome ?? $item->descricao ?? 'Item' }}
                          </p>
                          @php
                            $preco = (float) ($item->preco_unitario * $item->quantidade ?? 0);
                            $totalItem = $preco; // ajuste se houver descontos
                          @endphp
                          <p class="font-medium">
                            R$ {{ number_format($preco, 2, ',', '.') }}</p>
                        </div>
                        @forelse($item->complementos as $chaveComplemento => $complemento)
                          @php
                            $totalComplemento = floatval($complemento->preco_unitario * $complemento->qtde);
                            $totalItem += $totalComplemento
                          @endphp
                          <div class="pl-8 flex justify-between w-full">
                            <p class="text-xs text-base-content/60">{{ $complemento->qtde }}
                              x {{ $complemento->nome }}</p>
                            <p class="text-xs text-base-content/60">
                              R${{ number_format($totalComplemento, 2, ',', '.') }}</p>
                          </div>
                        @empty
                          <p class="text-sm text-base-content/60 text-center w-full">Nenhum
                            complemento no item.</p>
                        @endforelse
                        @if(!empty($item->observacao))
                          <p class="text-sm text-base-content/60">Observação do
                            item: {{ $item->observacao }}</p>
                        @endif
                        <p class="text-sm font-medium text-primary text-right w-full">
                          R$ {{ number_format($totalItem, 2, ',', '.') }}</p>
                      @else
                        <div class="flex justify-between w-full">
                          <p class="font-medium">
                            {{ $item->quantidade ?? 1 }}
                            x {{ $item->nome ?? $item->descricao ?? 'Item' }}
                          </p>
                        </div>
                        @if (is_null($pedidoSelecionado->pedido_ifood_id))
                          @if (!is_null($item->borda_id))
                            <div class="flex justify-between w-full">
                              <p class="pl-8 text-sm text-base-content/60">Borda: {{ $item->borda->nome }}</p>
                              <p class="text-sm text-base-content/60">
                                R$ {{ number_format($item->borda->preco, 2, ',', '.') }}</p>
                            </div>
                          @endif
                          @if (!is_null($item->massa_id))
                            <div class="flex justify-between w-full">
                              <p class="pl-8 text-sm text-base-content/60">Massa: {{ $item->massa->nome }}</p>
                              <p class="text-sm text-base-content/60">
                                R$ {{ number_format($item->massa->preco, 2, ',', '.') }}</p>
                            </div>
                          @endif
                          @forelse($item->sabores as $chaveSabor => $sabor)
                            <div class="flex justify-between w-full">
                              <p class="pl-8 text-sm text-base-content/60">{{ $sabor->qtde }}x {{ $sabor->nome }}</p>
                              <p class="text-sm text-base-content/60">
                                R$ {{ number_format(floatval($sabor->preco_unitario * $sabor->qtde), 2, ',', '.') }}
                              </p>
                            </div>
                          @empty
                            <p class="text-sm text-base-content/60 text-center w-full">Nenhum sabor no item.</p>
                          @endforelse
                        @else
                          @if(!$item->complementos->isEmpty())
                            @forelse($item->complementos as $chaveComplemento => $complemento)
                              <div class="flex justify-between w-full">
                                <p class="pl-8 text-sm text-base-content/60">{{ $complemento->qtde }}
                                  x {{ $complemento->nome }}</p>
                                <p class="text-sm text-base-content/60">
                                  R${{ number_format(floatval($complemento->preco_unitario * $complemento->qtde), 2, ',', '.') }}
                                </p>
                              </div>
                            @empty
                              <p class="text-sm text-base-content/60 text-center w-full">Nenhum sabor no item.</p>
                            @endforelse
                          @endif
                        @endif
                        @if(!empty($item->observacao))
                          <p class="text-sm text-base-content/60">Observação do
                            item: {{ $item->observacao }}</p>
                        @endif
                        <p class="text-sm font-medium text-primary text-right w-full">
                          @if (is_null($pedidoSelecionado->pedido_ifood_id))
                            R$ {{ number_format(array_sum($item->sabores->map(fn($s) => $s->qtde * $s->preco_unitario)->toArray()) + $item->massa->preco + $item->borda->preco, 2, ',', '.') }}
                          @else
                            R${{ number_format($item->subtotal, 2, ',', '.') }}
                          @endif
                        </p>
                      @endif
                    </div>
                  </div>
                @empty
                  <p class="text-sm text-base-content/60">Nenhum item no pedido.</p>
                @endforelse
              </div>
            </div>

            <!-- Totais -->
            @php
              $subtotalReal = $pedidoSelecionado->financeiro->total - $pedidoSelecionado->valor_frete;

              if (!is_null($pedidoSelecionado->cupomUsadoNoPedido()->first()) && $pedidoSelecionado->cupomUsadoNoPedido()->first()->onde_afetara === 'produto') {
                $subtotalReal = ($subtotalReal * 100) / (100 - $pedidoSelecionado->cupomUsadoNoPedido()->first()->valor_desconto);
              }

              if (!is_null($pedidoSelecionado->cupomUsadoNoPedido()->first()) && $pedidoSelecionado->cupomUsadoNoPedido()->first()->onde_afetara === 'frete') {
                $subtotalReal += $this->calculaDescontoCupomPedido($subtotalReal, $pedidoSelecionado->valor_frete);
              }
            @endphp
            <div class="bg-base-100 rounded-lg p-4 border">
              <div class="space-y-3">
                <div class="flex justify-between text-sm">
                  <span class="text-base-content/60">Subtotal dos itens</span>
                  <span class="font-medium">R$ {{ number_format($subtotalReal, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-base-content/60">Frete</span>
                  <span class="font-medium">R$ {{ number_format($pedidoSelecionado->valor_frete, 2, ',', '.') }}</span>
                </div>
                @if (!is_null($pedidoSelecionado->cupomUsadoNoPedido()->first()))
                  <div class="flex justify between text-sm">
                    @if ($pedidoSelecionado->cupomUsadoNoPedido()->first()->onde_afetara === 'produto')
                      <span class="text-base-content/60">Desconto nos produtos</span>
                    @else
                      <span class="text-base-content/60">Desconto no frete</span>
                    @endif
                    <span class="font-medium">
                      R$ {{ number_format($this->calculaDescontoCupomPedido($subtotalReal, $pedidoSelecionado->valor_frete), 2, ',', '.') }}
                    </span>
                  </div>
                @endif
                <div class="flex justify-between text-lg font-bold">
                  <span>Total</span>
                  <span class="text-primary">R$ {{ number_format($pedidoSelecionado->financeiro->total, 2, ',', '.') }}</span>
                </div>
              </div>
            </div>

            <!-- Ações -->

          </div>
        </div>
      </div>
    @endif
  </x-modal>
  {{--  modal de visualizacao do pedido--}}
</div>
