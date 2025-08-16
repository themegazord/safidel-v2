<?php

namespace App\Models;

use App\Observers\PedidoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([PedidoObserver::class])]
class Pedido extends Model
{
  use SoftDeletes;

  protected $table = 'pedidos';
  protected $fillable = ['pedido_ifood_id', 'endereco_entrega_ifood', 'mesa', 'comanda', 'empresa_id', 'cliente_id', 'tipo', 'status', 'observacao', 'valor_frete', 'nome', 'telefone'];

  public function itens(): HasMany
  {
    return $this->hasMany(PedidoItem::class);
  }

  public function cliente(): BelongsTo
  {
    return $this->belongsTo(Cliente::class); // Ou User::class, se usar autenticação.
  }

  public function enderecoEntregaIfood(): BelongsTo {
    return $this->belongsTo(Endereco::class, 'endereco_entrega_ifood', 'id');
  }

  public function empresa(): BelongsTo
  {
    return $this->belongsTo(Empresa::class);
  }

  public function financeiro(): HasOne
  {
    return $this->hasOne(FinanceiroPedido::class, 'pedido_id');
  }

  public function justificativaCancelamento(): BelongsTo
  {
    return $this->belongsTo(JustificativaCancelamentoPedido::class);
  }

  public function integracaoIfood(): BelongsTo
  {
    return $this->belongsTo(PedidoIntegracaoIfood::class, 'pedido_ifood_id', 'orderId');
  }

  public function cupomUsadoNoPedido(): BelongsToMany
  {
    return $this->belongsToMany(Promocao::class, 'promocao_usada', 'pedido_id', 'promocao_id');
  }

  public function defineTipoPedido(): string
  {
    return match ($this->tipo) {
      'D' => 'Delivery',
      'M' => 'Atendimento em mesa',
      'R' => 'Retirada no estabelecimento'
    };
  }

  public function defineCorDependendoStatus(): string
  {
    return match ($this->status) {
      'pendente' => 'bg-yellow-500 text-white',
      'aceito' => 'bg-green-500 text-white',
      'sendo preparado' => 'bg-sky-500 text-white',
      'sendo entregue' => 'bg-purple-500 text-white',
      'pronto para entrega' => 'bg-cyan-500 text-white',
      'entregue para mesa' => 'bg-green-500 text-white',
      'entregue' => 'bg-green-500 text-white',
      'finalizado' => 'bg-green-500 text-white',
      'pedido feito' => 'bg-green-500 text-white',
      'cancelado' => 'bg-red-500 text-white',
      'pix expirado' => 'bg-gray-500 text-white',
      'confirmar pix' => 'bg-yellow-500 text-white',
    };
  }

  public function defineStatusPedidoCliente(): string
  {
    return match ($this->status) {
      'pendente' => 'Pedido pendente',
      'aceito' => 'Pedido aceito',
      'sendo preparado' => 'Pedido está sendo preparado',
      'sendo entregue' => 'Pedido está sendo entregue',
      'pronto para entrega' => 'Esperando entregador',
      'entregue' => 'Pedido entregue',
      'finalizado' => 'Pedido da mesa finalizado',
      'entregue para mesa' => 'Pedido entregue na mesa',
      'pedido feito' => 'Pedido feito',
      'cancelado' => 'Pedido cancelado',
      'pix expirado' => 'Pix expirado',
      'confirmar pix' => 'Esperando a confirmação do Pix'
    };
  }
}
