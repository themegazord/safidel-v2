<?php

namespace App\Services\IFOOD;

use App\Models\Configuracao;
use App\Models\Empresa;
use App\Models\Endereco;
use App\Models\FinanceiroPedido;
use App\Models\Integracao;
use App\Models\Pedido;
use App\Models\PedidoComplemento;
use App\Models\PedidoIntegracaoIfood;
use App\Models\PedidoItem;
use Carbon\Carbon;
use Mary\Traits\Toast;

class ApiExternaIfood extends Endpoints
{
  use Toast;

  public function autenticacao(int $empresa_id): void
  {
    $integracao = $this->obterIntegracaoIfood($empresa_id);

    if (!$integracao) {
      return;
    }

    if ($this->tokenAindaValido($empresa_id)) {
      return;
    }

    try {
      $resposta = \Http::asForm()->withHeaders([
        'accept' => 'application/json',
      ])->post($this->urlBaseAutenticacao . $this->oauthToken, [
        'grantType' => 'client_credentials',
        'clientId' => $integracao->clientId,
        'clientSecret' => $integracao->clientSecret,
        'authorizationCode' => '',
        'authorizationCodeVerifier' => '',
        'refreshToken' => '',
      ]);

      $dados = $resposta->json();

      if (!isset($dados['accessToken'], $dados['expiresIn'])) {
        throw new \Exception('Resposta inválida do IFOOD');
      }

      Empresa::findOrFail($empresa_id)->update([
        'tokenIfood' => $dados['accessToken'],
        'lifetimeTokenIfood' => now()->addSeconds($dados['expiresIn']),
      ]);
    } catch (\Exception $e) {
      $this->warning("Integração IFOOD: Falha na tentativa de autenticação com IFOOD.");
    }
  }

  public function consultaListagemPedidos(int $empresa_id): void
  {
    $this->autenticacao($empresa_id);

    $resposta = \Http::withToken(Empresa::query()->findOrFail($empresa_id)->tokenIfood)->withHeaders([
      'accept' => 'application/json'
    ])->get($this->urlBaseEventos . $this->pollingEvents);

    if ($resposta->ok()) {
      $pedidos = $resposta->json();
      foreach ($pedidos as $pedido) {
        try {
          PedidoIntegracaoIfood::query()->updateOrCreate(['uuid' => $pedido['id']], [
            'uuid' => $pedido['id'],
            'empresa_id' => $empresa_id,
            'orderId' => $pedido['orderId'],
            'fullCode' => $pedido['fullCode'],
            'code' => $pedido['code'],
            'metadata' => $pedido['metadata'] ?? [],
            'created_ifood_at' => $pedido['createdAt'],
          ]);

          if ($pedido['fullCode'] === 'CONCLUDED') {
            Pedido::query()->where('pedido_ifood_id', $pedido['orderId'])->update([
              'status' => 'entregue'
            ]);
          }
          if ($pedido['fullCode'] === 'CANCELLED') {
            $pedidoCancelado = Pedido::query()->where('pedido_ifood_id', $pedido['orderId'])->first();
            $pedidoCancelado->update([
              'status' => 'cancelado'
            ]);
            $pedidoCancelado->delete();
          }

        } catch (\Throwable $e) {
          \Log::error('Erro ao salvar pedido iFood', [
            'pedido_id' => $pedido['id'] ?? null,
            'erro' => $e->getMessage(),
          ]);
        }
      }
    }
    $this->consultaDadosPedidoIFOOD($empresa_id);
    $this->enviaRespostaDeRecebimento($empresa_id);
  }

  private function enviaRespostaDeRecebimento(int $empresa_id): void
  {
    $this->autenticacao($empresa_id);

    $resposta = \Http::withToken(Empresa::query()->findOrFail($empresa_id)->tokenIfood)->withHeaders([
      'accept' => 'application/json'
    ])->post($this->urlBaseEventos . $this->ackEvents, PedidoIntegracaoIfood::query()->where('empresa_id', $empresa_id)->where('viewed_at', null)->get(['uuid'])->map(fn($p) => ['id' => $p->uuid]));

    if ($resposta->accepted()) {
      PedidoIntegracaoIfood::query()->where('empresa_id', $empresa_id)->where('viewed_at', null)->update([
        'viewed_at' => Carbon::now(),
      ]);
    }
  }

  private function consultaDadosPedidoIFOOD(int $empresa_id): void
  {
    $this->autenticacao($empresa_id);

    foreach (PedidoIntegracaoIfood::query()->doesntHave('pedido')->where('fullCode', "PLACED")->whereNot('viewed_at', null)->get() as $pedido) {
      $resposta = \Http::withToken(Empresa::query()->findOrFail($empresa_id)->tokenIfood)->withHeaders([
        'accept' => 'application/json'
      ])->get($this->urlBaseOrders . $this->getDetailsOrderWithOrderID($pedido->orderId));

      if ($resposta->ok()) {
        $detalhesPedidoIfood = $resposta->json();
        try {
          $consultaCliente = \DB::table('clientes');
          if (isset($detalhesPedidoIfood['customer']['documentNumber'])) {
            $consultaCliente = $consultaCliente->orWhereRaw("REGEXP_REPLACE(cpf, '[^0-9]', '') = ?", [$detalhesPedidoIfood['customer']['documentNumber']])->first();
          } else if (isset($detalhesPedidoIfood['customer']['phone']['number'])) {
            $consultaCliente = $consultaCliente->orWhereRaw("REGEXP_REPLACE(telefone, '[^0-9]', '') = ?", [preg_replace('/\D/', '', $detalhesPedidoIfood['customer']['phone']['number'])])->first();
          } else {
            $consultaCliente = null;
          }

          $enderecoEntregaIfood = Endereco::query()->create([
            'logradouro' => $detalhesPedidoIfood['delivery']['deliveryAddress']['streetName'],
            'bairro' => $detalhesPedidoIfood['delivery']['deliveryAddress']['neighborhood'],
            'cidade' => $detalhesPedidoIfood['delivery']['deliveryAddress']['city'],
            'uf' => $detalhesPedidoIfood['delivery']['deliveryAddress']['state'],
            'cep' => $detalhesPedidoIfood['delivery']['deliveryAddress']['postalCode'],
            'numero' => intval($detalhesPedidoIfood['delivery']['deliveryAddress']['streetNumber']),
          ]);
          $dadosPedido = [
            'pedido_ifood_id' => $detalhesPedidoIfood['id'],
            'endereco_entrega_ifood' => $enderecoEntregaIfood->id,
            'comanda' => uuid_create(),
            'tipo' => 'D',
            'empresa_id' => $empresa_id,
            'valor_frete' => $detalhesPedidoIfood['total']['deliveryFee']
          ];

          if (Configuracao::whereEmpresaId($empresa_id)->whereConfiguracao('aceite_automatico_ifood')->first()->valor) {
            $this->aceitarPedidoIfood($empresa_id, $detalhesPedidoIfood['id']);
            $dadosPedido['status'] = 'sendo preparado';
          } else {
            $dadosPedido['status'] = 'pendente';
          }

          if ($consultaCliente !== null) {
            $dadosPedido['cliente_id'] = $consultaCliente->id;
          } else {
            $dadosPedido['nome'] = $detalhesPedidoIfood['customer']['name'];
            $dadosPedido['telefone'] = preg_replace('/\D/', '', $detalhesPedidoIfood['customer']['phone']['number']);
          }
          $pedidoCriado = Pedido::query()->create($dadosPedido);
          foreach ($detalhesPedidoIfood['items'] as $item) {
            $pedidoItemCriado = PedidoItem::query()->create([
              'pedido_id' => $pedidoCriado->id,
              'uuid' => uuid_create(),
              'nome' => $item['name'],
              'external_id' => !empty($item['externalCode']) ? $item['externalCode'] : null,
              'tipo' => $item['type'] === 'LEGACY_PIZZA' ? 'P' : 'I',
              'quantidade' => $item['quantity'],
              'preco_unitario' => $item['unitPrice'],
              'subtotal' => $item['totalPrice']
            ]);

            if (isset($item['options'])) {
              foreach ($item['options'] as $complemento) {
                PedidoComplemento::query()->create([
                  'pedido_item_id' => $pedidoItemCriado->id,
                  'uuid' => uuid_create(),
                  'external_id' => !empty($complemento['externalCode']) ? $complemento['externalCode'] : null,
                  'nome' => $complemento['name'],
                  'qtde' => $complemento['quantity'],
                  'preco_unitario' => $complemento['unitPrice'],
                ]);
              }
            }
          }

          $financeiroDados = [
            'uuid' => uuid_create(),
            'pedido_id' => $pedidoCriado->id,
            'total' => $detalhesPedidoIfood['payments']['prepaid'] !== 0 ? $detalhesPedidoIfood['payments']['prepaid'] : $detalhesPedidoIfood['payments']['pending'],
          ];

          $financeiroDados['forma_pagamento'] = match ($detalhesPedidoIfood['payments']['methods'][0]['method']) {
            'CASH' => 'Dinheiro',
            'BANK_DRAFT' => 'Cheque bancário',
            'CREDIT' => "Cartão de Crédito - " . $detalhesPedidoIfood['payments']['methods'][0]['card']['brand'],
            'DEBIT' => "Cartão de Débito - " . $detalhesPedidoIfood['payments']['methods'][0]['card']['brand'],
            'MEAL_VOUCHER' => "Vale Refeição - " . $detalhesPedidoIfood['payments']['methods'][0]['card']['brand'],
            default => $detalhesPedidoIfood['payments']['methods'][0]['method']
          };

          FinanceiroPedido::query()->create($financeiroDados);
        } catch (\Exception $e) {
          \Log::warning("Erro no cadastro dos dados do pedido: $pedido->orderId; " . $e->getMessage());
          $this->warning("Erro no cadastro dos dados do pedido: $pedido->orderId");
        }
      }
    }
  }

  public function aceitarPedidoIfood(int $empresa_id, string $order_id): void
  {
    $this->autenticacao($empresa_id);

    \Http::withToken(Empresa::query()->findOrFail($empresa_id)->tokenIfood)->withHeaders([
      'accept' => 'application/json'
    ])->post($this->urlBaseOrders . $this->getAcceptOrderWithOrderID($order_id));
  }

  public function prontoParaRetiradaPedidoIfood(int $empresa_id, string $order_id): void
  {
    $this->autenticacao($empresa_id);

    \Http::withToken(Empresa::query()->findOrFail($empresa_id)->tokenIfood)->withHeaders([
      'accept' => 'application/json'
    ])->post($this->urlBaseOrders . $this->getReadyToPickupWithOrderID($order_id));
  }

  public function dispacharPedidoIfood(int $empresa_id, string $order_id): void
  {
    $this->autenticacao($empresa_id);

    \Http::withToken(Empresa::query()->findOrFail($empresa_id)->tokenIfood)->withHeaders([
      'accept' => 'application/json'
    ])->post($this->urlBaseOrders . $this->getDispatchOrderWithOrderID($order_id));
  }

  public function solicitarMotivosCancelamentoIfood(int $empresa_id, string $order_id): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
  {
    $this->autenticacao($empresa_id);

    return \Http::withToken(Empresa::query()->findOrFail($empresa_id)->tokenIfood)->withHeaders([
      'accept' => 'application/json'
    ])->get($this->urlBaseOrders . $this->getCancellationReasons($order_id));
  }

  public function solicitarCancelamentoIfood(int $empresa_id, string $order_id, string $motivo, string $codigo): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
  {
    $this->autenticacao($empresa_id);

    return \Http::withToken(Empresa::query()->findOrFail($empresa_id)->tokenIfood)->withHeaders([
      'accept' => '*/*',
      'Content-Type' => 'application/json'
    ])->post($this->urlBaseOrders . $this->getRequestCancellation($order_id), [
      'reason' => $motivo,
      'cancellationCode' => $codigo
    ]);
  }

  private function tokenAindaValido(int $empresa_id): bool
  {
    return Empresa::findOrFail($empresa_id)->lifetimeTokenIfood > now();
  }

  private function obterIntegracaoIfood(int $empresa_id): ?Integracao
  {
    $integracao = Integracao::where('empresa_id', $empresa_id)
      ->where('tipo', 'ifood')
      ->first();

    if (!$integracao || !$integracao->clientId || !$integracao->clientSecret) {
      return null;
    }

    return $integracao;
  }
}
