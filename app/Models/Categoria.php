<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
  /** @use HasFactory<\Database\Factories\CategoriaFactory> */
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'cardapio_id',
    'tipo',
    'nome',
    'status'
  ];

  public function cardapio(): BelongsTo {
    return $this->belongsTo(Cardapio::class, 'cardapio_id');
  }

  public function tamanhos(): HasMany {
    return $this->hasMany(CategoriaTamanho::class, 'categoria_id');
  }

  public function massas(): HasMany {
    return $this->hasMany(CategoriaMassa::class, 'categoria_id');
  }

  public function bordas(): HasMany {
    return $this->hasMany(CategoriaBorda::class, 'categoria_id');
  }

  public function itens(): HasMany {
    return $this->hasMany(Item::class, 'categoria_id');
  }

  public function ehAtivo(): bool {
    return $this->trashed();
  }
}
