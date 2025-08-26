<?php

namespace App\Livewire\Views\Aplicacao\Empresa;

use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Pedidos extends Component
{
  public $searchTerm = '';
  public Collection $pedidos;
  public Pedido $pedidoSelecionado;
  public bool $modalDetalhePedido = false;
  protected $listeners = ['atualizarStatus' => 'atualizarStatus'];

  public function mount()
  {
    $this->carregarPedidos();
  }

  public function recarregarPedidos(): void {
    $this->carregarPedidos();
  }

  private function carregarPedidos(): void {
    $this->pedidos = \Auth::user()
      ->empresa
      ->pedidos()
      ->whereDate('created_at', now()->toDateString())
      ->with(['itens', 'cliente', 'financeiro'])
      ->latest()
      ->get();
  }

  public function atualizarStatus(array $payload): void
  {
    if (!isset($payload['id'], $payload['status'])) {
      return;
    }


    $id = (string)$payload['id'];
    $novoStatus = (string)$payload['status'];

    if ($pedido = $this->pedidos->first(fn($p) => (string)$p->id === $id)) {
      $pedido->status = $novoStatus;
      $pedido->save();

      // Opcional: força atualização da Collection (mesmo objeto já é referenciado)
      $this->pedidos = $this->pedidos->map(fn($p) => (string)$p->id === $id ? $pedido : $p);
    }

  }

  public function getFilteredOrdersProperty(): Collection
  {
    $normalizados = $this->pedidos->map(function ($p) {
      return [
        'id' => (string)$p->id,
        'number' => $p->numero ?? $p->id,
        'customerName' => $p->cliente->nome ?? '—',
        'type' => $p->defineTipoPedido() ?? '—',
        'status' => (string)$p->status,
        'createdAt' => $this->getTempoPedidoAberto($p->created_at),
        'items' => $p->itens?->pluck('nome')->all() ?? [],
        'total' => $p->financeiro->total ?? 0,
      ];
    });

    $busca = trim((string)$this->searchTerm);

    if ($busca === '') {
      return $normalizados->groupBy('status');
    }

    return $normalizados
      ->filter(fn($order) => str_contains(strtolower((string)$order['customerName']), strtolower($busca)) ||
        str_contains((string)$order['number'], $busca)
      )
      ->groupBy('status');
  }

  public function defineIconPedido(string $status): string {
    return match ($status) {
      'pendente', 'pronto para entrega' => 'o-clock',
      'sendo preparado' => 'o-bookmark-square',
      'sendo entregue' => 'o-map-pin',
    };
  }

  public function setPedidoSelecionado(int $pedido_id): void {
    $this->pedidoSelecionado = $this->pedidos->firstWhere('id', $pedido_id);
    $this->modalDetalhePedido = true;
  }

  public function getTempoPedidoAberto(string $created_at): string {
    Carbon::setLocale('pt_BR');
    return Carbon::parse($created_at)->diffForHumans();
  }


  #[Layout('components.layouts.empresa')]
  #[Title('Pedidos')]
  public function render()
  {
    return view('livewire.views.aplicacao.empresa.pedidos');
  }
}
