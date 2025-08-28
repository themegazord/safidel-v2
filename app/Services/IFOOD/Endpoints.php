<?php

namespace App\Services\IFOOD;

class Endpoints
{
  protected string $urlBaseAutenticacao = "https://merchant-api.ifood.com.br/authentication/v1.0";
  protected string $urlBaseEventos = "https://merchant-api.ifood.com.br/events/v1.0/";
  protected string $urlBaseOrders = "https://merchant-api.ifood.com.br/order/v1.0/";
  protected string $oauthToken = "/oauth/token";
  protected string $pollingEvents = "/events:polling?types=PLC%2CCAN%2CCON&groups=DELIVERY%2CORDER_STATUS";
  protected string $ackEvents = "/events/acknowledgment";
  protected string $detailsOrder = "orders/";

  protected function getDetailsOrderWithOrderID(string $order_id): string {
    return $this->detailsOrder . $order_id;
  }

  protected function getAcceptOrderWithOrderID(string $order_id): string {
    return $this->detailsOrder . $order_id . "/confirm";
  }

  protected function getReadyToPickupWithOrderID(string $order_id): string {
    return $this->detailsOrder . $order_id . "/readyToPickup";
  }

  protected function getDispatchOrderWithOrderID(string $order_id): string {
    return $this->detailsOrder . $order_id . "/dispatch";
  }

  protected function getCancellationReasons(string $order_id): string {
    return $this->detailsOrder . $order_id . "/cancellationReasons";
  }

  protected function getRequestCancellation(string $order_id): string {
    return $this->detailsOrder . $order_id . "/requestCancellation";
  }
}
