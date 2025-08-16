<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
  /** @use HasFactory<\Database\Factories\ItemFactory> */
  use HasFactory, SoftDeletes;

  protected $table = 'itens';
  protected $fillable = [
    'external_id',
    'categoria_id',
    'tipo',
    'nome',
    'tipo_preco',
    'preco',
    'desconto',
    'valor_desconto',
    'porcentagem_desconto',
    'descricao',
    'qtde_pessoas',
    'peso',
    'gramagem',
    'eh_bebida',
    'classificacao',
    'imagem'
  ];

  protected $casts = [
    'classificacao' => 'array',
  ];

  public function categoria(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
    return $this->belongsTo(Categoria::class, 'categoria_id');
  }

  public function precosItemPizza(): \Illuminate\Database\Eloquent\Relations\HasMany {
    return $this->hasMany(ItemPreco::class, 'item_id');
  }

  public function grupo_complemento(): HasMany {
    return $this->hasMany(GrupoComplemento::class,'item_id');
  }

  public function ehAtivo(): bool {
    return $this->trashed();
  }
}
