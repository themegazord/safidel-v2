<?php

namespace App\Livewire\Views\Aplicacao\Empresa;

use App\Models\JustificativaCancelamentoPedido;
use App\Models\Pedido;
use App\Services\IFOOD\ApiExternaIfood;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Pedidos extends Component
{
  use Toast, WithPagination;

  public $searchTerm = '';
  public Collection $pedidos;
  public Pedido $pedidoSelecionado, $pedidoSelecionadoAntigo;
  public bool $modalDetalhePedido = false;
  public bool $modalDetalhePedidoAntigo = false;
  public bool $modalCancelamentoPedido = false;
  public bool $modalDadosClientePedido = false;
  public ?string $motivoCancelamento = null;
  public ?string $motivoCancelamentoSelecionado = null;
  public ?array $motivosCancelamentoIFOOD = null;
  public array $sortBy = ['column' => 'created_at', 'direction' => 'asc'];
  public int $porPaginaPedidosCliente = 10;
  public Collection $produtosMaisPedidosPeloCliente;
  protected $listeners = ['atualizarStatus' => 'atualizarStatus'];

  public function mount()
  {
    $this->carregarPedidos();
  }

  public function recarregarPedidos(): void
  {
    $this->carregarPedidos();
  }

  private function carregarPedidos(): void
  {
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

  public function defineIconPedido(string $status): ?string
  {
    return match ($status) {
      'pendente', 'pronto para entrega' => 'o-clock',
      'sendo preparado' => 'o-bookmark-square',
      'sendo entregue' => 'o-map-pin',
      'cancelado' => 'o-trash',
      'entregue' => 'o-check-circle',
    };
  }

  public function setPedidoSelecionado(int $pedido_id): void
  {
    $this->pedidoSelecionado = $this->pedidos->firstWhere('id', $pedido_id);
    $this->modalDetalhePedido = true;
  }

  public function setPedidoSelecionadoAntigo(int $pedido_id): void
  {
    $this->pedidoSelecionadoAntigo = $this->pedidoSelecionado->cliente->pedidos->firstWhere('id', $pedido_id);
    $this->pedidoSelecionadoAntigo->load(['itens', 'cliente', 'financeiro']);
    $this->modalDetalhePedidoAntigo = true;
  }

  public function getTempoPedidoAberto(string $created_at): string
  {
    Carbon::setLocale('pt_BR');
    return Carbon::parse($created_at)->diffForHumans();
  }

  public function alteraStatusPedido(ApiExternaIfood $api): void
  {
    if ($this->pedidoSelecionado->status === 'pendente') {
      $this->pedidoSelecionado->update(['status' => 'sendo preparado']);
      $this->dispatch('pausar-som-alerta');
    } elseif ($this->pedidoSelecionado->status === 'sendo preparado') {
      if ($this->pedidoSelecionado->pedido_ifood_id !== null) {
        $api->prontoParaRetiradaPedidoIfood($this->empresa->id, $this->pedidoSelecionado->pedido_ifood_id);
      }
      $this->pedidoSelecionado->update(['status' => 'pronto para entrega']);
    } elseif ($this->pedidoSelecionado->status === 'pronto para entrega') {
      if ($this->pedidoSelecionado->pedido_ifood_id !== null) {
        $api->dispacharPedidoIfood($this->empresa->id, $this->pedidoSelecionado->pedido_ifood_id);
      }
      $this->pedidoSelecionado->update(['status' => 'sendo entregue']);
    }

    $this->recarregarPedidos();

    $this->success('O pedido foi atualizado.');
  }

  public function imprimePedido(): StreamedResponse
  {
    $pdf = Pdf::loadView('livewire.pdfs.pedido', ['pedido' => $this->pedidoSelecionado])
      ->setPaper([0, 0, 807.874, 221.102], 'landscape');

    return response()->streamDownload(function () use ($pdf) {
      echo $pdf->stream();
    }, "pedido_{$this->pedidoSelecionado->id}.pdf");
  }

  public function setCancelamentoPedido(ApiExternaIfood $api): void {
    $this->modalCancelamentoPedido = true;

    if ($this->pedidoSelecionado->pedido_ifood_id) {
      $resposta = $api->solicitarMotivosCancelamentoIfood($this->pedidoSelecionado->empresa->id, $this->pedidoSelecionado->pedido_ifood_id);

      if ($resposta->ok()) {
        $this->motivosCancelamentoIFOOD = $resposta->json();
      }
    }
  }

  public function confirmarCancelamento(ApiExternaIfood $api): void
  {
    if ($this->pedidoSelecionado->pedido_ifood_id) {
      $this->validate(rules: [
        'motivoCancelamentoSelecionado' => ['required']
      ], messages: [
        'required' => 'Você deve selecionar um motivo para continuar com o cancelamento.'
      ]);

      $resposta = $api->solicitarCancelamentoIfood($this->empresa->id, $this->pedidoSelecionado->pedido_ifood_id, $this->buscarDescricaoManual($this->motivoCancelamentoSelecionado), $this->motivoCancelamentoSelecionado);

      if ($resposta->status() !== 202) {
        $this->error('Erro ao tentar o cancelamento do pedido no ifood. Entrar em contato com o suporte.');
        $this->modalCancelamentoPedido = !$this->modalCancelamentoPedido;
        return;
      }

      if ($resposta->accepted()) {
        $this->pedidoSelecionado->justificativaCancelamento()->create([
          [
            'pedido_id' => $this->pedidoSelecionado->id,
            'origem_cancelamento' => 'empresa',
            'motivo' => $this->buscarDescricaoManual($this->motivoCancelamentoSelecionado)
          ]
        ]);
      }
    } else {
      $this->validate(rules: [
        'motivoCancelamento' => ['required']
      ], messages: [
        'required' => 'Você deve informar um motivo para continuar com o cancelamento.'
      ]);

      $this->pedidoSelecionado->justificativaCancelamento()->create([
        'pedido_id' => $this->pedidoSelecionado->id,
        'origem_cancelamento' => 'empresa',
        'motivo' => $this->motivoCancelamento
      ]);
    }

    $this->pedidoSelecionado->update(['status' => 'cancelado']);
    $this->pedidoSelecionado->delete();
    $this->modalCancelamentoPedido = false;
    $this->modalDetalhePedido = false;

    $this->success('Pedido cancelado com sucesso');
  }

  public function setModalDetalhesClientePedidos(): void {
    // Produtos mais vendidos
    $pedidoItens = collect();
    foreach ($this->pedidoSelecionado->cliente->pedidos->where('status', 'entregue')->where('empresa_id', $this->pedidoSelecionado->empresa->id) as $pedido) {
      $pedido->itens->each(function ($i) use ($pedidoItens) {
        $pedidoItens->add($i);
      });
    }
    $grupos = $pedidoItens->groupBy('nome');
    $gruposComContagem = $grupos->map(function ($grupo) {
      return [
        'nome' => $grupo->first()['nome'],
        'qtde' => $grupo->sum('quantidade')
      ];
    });

    $this->produtosMaisPedidosPeloCliente = $gruposComContagem->sortByDesc('qtde');

    $this->modalDadosClientePedido = true;
  }


  #[Layout('components.layouts.empresa')]
  #[Title('Pedidos')]
  public function render()
  {
    return view('livewire.views.aplicacao.empresa.pedidos');
  }

  private function buscarDescricaoManual(string $codigo)
  {
    foreach ($this->motivosCancelamentoIFOOD as $item) {
      if ($item['cancelCodeId'] === $codigo) {
        return $item['description'];
      }
    }
    return null;
  }
}
