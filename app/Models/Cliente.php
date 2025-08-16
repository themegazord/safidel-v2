<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Cliente extends Model
{
  /** @use HasFactory<\Database\Factories\ClienteFactory> */
  use HasFactory, Notifiable;

  protected $fillable = [
    'endereco_id',
    'nome',
    'email',
    'cpf',
    'telefone',
    'data_nascimento',
  ];
  public function pedidos(): HasMany {
    return $this->hasMany(Pedido::class, 'cliente_id');
  }

  public function endereco(): BelongsTo {
    return $this->belongsTo(Endereco::class,'endereco_id');
  }

  public function usuario(): BelongsTo {
    return $this->belongsTo(User::class, 'email', 'email');
  }

  public function cuponsUsadosPeloCliente(): BelongsToMany {
    return $this->belongsToMany(Promocao::class, 'promocao_usada', 'cliente_id', 'promocao_id');
  }
}
