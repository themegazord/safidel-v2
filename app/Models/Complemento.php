<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complemento extends Model
{
  /** @use HasFactory<\Database\Factories\ComplementoFactory> */
  use HasFactory;

  protected $fillable = [
    'external_id',
    'grupo_id',
    'nome',
    'descricao',
    'preco',
    'status'
  ];

  public function grupos(): BelongsTo {
    return $this->belongsTo(GrupoComplemento::class, 'grupo_id');
  }
}
