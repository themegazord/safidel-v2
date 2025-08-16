<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinanceiroPedido extends Model
{
    protected $table = 'financeiro_pedido';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
  protected $fillable = [
    "uuid",
    "pedido_id",
    "forma_pagamento",
    "total"
  ];

  public function pedido(): BelongsTo
  {
    return $this->belongsTo(Pedido::class, 'id', 'pedido_id');
  }

  public function status_financeiro_api(): HasOne {
    return $this->hasOne(StatusFinanceiroPedidoApi::class, 'financeiro_pedido_id', 'uuid');
  }

  public function defineFormaPagamento(): string
  {
    return match ($this->forma_pagamento) {
      'pix' => 'Pix',
      'master_debito' => 'Master Débito',
      'visa_debito' => 'Visa Débito',
      'elo_debito' => 'Elo Débito',
      'visa_credito' => 'Visa Crédito',
      'elo_credito' => 'Elo Crédito',
      'amex_credito' => 'Amex Crédito',
      'master_credito' => 'Master Crédito',
      'dinheiro' => 'Dinheiro',
      'vr_refeicao' => 'VR Refeição',
      'alelo_refeicao' => 'Alelo Refeição',
      'outro_refeicao' => 'Outro Vale Refeição',
      default => 'Forma de pagamento não definida',
    };
  }

}
