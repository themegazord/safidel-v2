<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PedidoIntegracaoIfood extends Model
{
  protected $table = 'pedidos_integracao_ifood';

  protected $primaryKey = 'uuid'; // se quiser, senão Laravel usa "id"

  public $incrementing = false;
  protected $keyType = 'string';

  protected $fillable = [
    'uuid',
    'empresa_id',
    'orderId',
    'fullCode',
    'code',
    'metadata',
    'created_ifood_at',
    'viewed_at',
  ];

  protected $casts = [
    'metadata' => 'array',
    'created_ifood_at' => 'datetime',
    'viewed_at' => 'datetime',
  ];

  public function pedido(): HasOne {
    return $this->hasOne(Pedido::class, 'pedido_ifood_id', 'orderId')->withTrashed();
  }
}
