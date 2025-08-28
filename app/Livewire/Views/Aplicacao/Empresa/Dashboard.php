<?php

namespace App\Livewire\Views\Aplicacao\Empresa;

use App\Models\Empresa;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class Dashboard extends Component
{
  use Toast;
  public int $diasFiltro = 7;
  public array $necessidades = [];
  public array $chartPedidosModalidade = [
    'type' => 'doughnut',
    'data' => [
      'labels' => ['Entrega', 'Mesa', 'Retirada'],
      'datasets' => [
        [
          'label' => 'Pedidos por modalidade',
          'data' => [], // aqui você injeta dinamicamente
          'backgroundColor' => ['#e74c3c', '#f39c12', '#2ecc71'], // vermelho, laranja, verde
          'hoverOffset' => 10,
        ],
      ],
    ],
    'options' => [
      'responsive' => true,
      'plugins' => [
        'legend' => [
          'display' => false, // esconder legenda default, já que você monta a custom do lado
        ],
      ],
      'cutout' => '60%', // tamanho do buraco do meio
    ],
  ];
  public bool $recebimentoPedidosIfood = false;
  public Collection $pedidos;
  public Collection $pedidosHoje;
  public Collection $financeiroPedidos;
  public Collection $financeiroPedidosHoje;
  public Empresa $empresa;

  public function mount(): void
  {
    $this->empresa = \Auth::user()->empresa()->with('pedidos')->firstOrFail();
    $this->recebimentoPedidosIfood = $this->empresa->esta_recebendo_pedidos_ifood;
    $this->atualizaPedidos($this->diasFiltro);
    $this->atualizaPedidosHoje();
    $this->alimentaCharts();

//    if (Configuracao::whereEmpresaId($this->empresa->id)->where('configuracao', 'aceite_automatico')->first() === null) {
//      $this->necessidades['aceite_automatico'] = [
//        "mensagem" => "Você deve configurar se a sua empresa vai ou não aceitar automáticamente os pedidos, caso queira que não seja, entre na rotina e clique em Salvar => ",
//        "link" => route('aplicacao.empresa.config_empresa.configuracoes', ['cnpj' => $this->empresa->cnpj])
//      ];
//    }
//
//    if (Configuracao::whereEmpresaId($this->empresa->id)->where('configuracao', 'fuso_horario')->first() === null) {
//      $this->necessidades['fuso_horario'] = [
//        "mensagem" => "Você deve configurar qual fuso horário sua empresa irá funcionar => ",
//        "link" => route('aplicacao.empresa.horarios', ['cnpj' => $this->empresa->cnpj])
//      ];
//    }
//
//    if (Configuracao::whereEmpresaId($this->empresa->id)->where('configuracao', 'funcionamentoEstabelecimento')->first() === null) {
//      $this->necessidades['funcionamentoEstabelecimento'] = [
//        "mensagem" => "Você deve configurar como vai ser o funcionamento do seu estabelecimento => ",
//        "link" => route('aplicacao.empresa.horarios', ['cnpj' => $this->empresa->cnpj])
//      ];
//    }
//
//    if (FormaPagamento::whereEmpresaId($this->empresa->id)->get()->isEmpty()) {
//      $this->necessidades['forma_pagamento'] = [
//        "mensagem" => "Você deve cadastrar alguma forma de pagamento => ",
//        "link" => route('aplicacao.empresa.forma_pagamento', ['cnpj' => $this->empresa->cnpj])
//      ];
//    }
//
//    if (Cardapio::whereEmpresaId($this->empresa->id)->get()->isEmpty()) {
//      $this->necessidades['cardapios'] = [
//        "mensagem" => "Você deve cadastrar um cardápio com seus itens => ",
//        "link" => route('aplicacao.empresa.cardapios', ['cnpj' => $this->empresa->cnpj])
//      ];
//    }
  }

  #[Layout('components.layouts.empresa')]
  #[Title('Dashboard')]
  public function render()
  {
    return view('livewire.views.aplicacao.empresa.dashboard');
  }

  public function copiaLinkLoja(string $tipo_funcionamento): void {
    $this->dispatch('copia-link', ['link' => env('APP_URL') . "/loja/{$this->empresa->interacao_id}/$tipo_funcionamento"]);
    $this->success('Link com cupom copiado');
  }

  private function atualizaPedidos(int $diaReferencia): void
  {
    $this->pedidos = $this->empresa->pedidos()
      ->whereBetween('created_at', [
        now()->subDays($diaReferencia)->startOfDay(),
        now()->endOfDay(),
      ])
      ->with('financeiro') // evita N+1 ao mapear depois
      ->get();


    $this->financeiroPedidos = $this->pedidos
      ->where('status', 'entregue')
      ->map(fn($p) => $p->financeiro)
      ->values();
  }

  private function atualizaPedidosHoje(): void
  {
    $this->pedidosHoje = $this->empresa->pedidos()
      ->whereDate('created_at', now())
      ->get();


    $this->financeiroPedidosHoje = $this->pedidosHoje->map(function ($pedido) {
      if ($pedido->status === 'entregue')
        return $pedido->financeiro;
    });
  }

  private function alimentaCharts(): void
  {
    $this->chartPedidosModalidade['data']['datasets'][0]['data'] = [
      $this->contaPedidos('D'), // Entrega
      $this->contaPedidos('M'), // Mesa
      $this->contaPedidos('R'), // Retirada
    ];
  }

  private function contaPedidos(string $tipo): int
  {
    return $this->pedidos->where('tipo', $tipo)->where('status', 'entregue')->count();
  }
}
