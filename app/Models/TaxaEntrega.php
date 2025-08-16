<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxaEntrega extends Model
{
  protected $table = 'taxa_entrega';

  protected $fillable = [
    'empresa_id',
    'raio',
    'tempo',
    'taxa',
    'corCirculo',
    'corPreenchimento',
  ];

  public function empresa(): BelongsTo {
    return $this->belongsTo(Empresa::class);
  }
}
