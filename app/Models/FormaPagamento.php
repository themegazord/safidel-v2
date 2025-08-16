<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
  protected $table = 'forma_pagamento';

  protected $fillable = [
    'empresa_id',
    'formas'
  ];

  protected function casts(): array
  {
    return [
      'formas' => 'array'
    ];
  }

  public static function formasPagamentoPadrao(): array {
    return [
      'pix' => 'Pix',
      'dinheiro' => 'Dinheiro',
      'master_debito' => 'Mastercard - Débito',
      'master_credito' => 'Mastercard - Crédito',
      'elo_debito' => 'Elo - Débito',
      'elo_credito' => 'Elo - Crédito',
      'visa_credito' => 'Visa - Crédito',
      'visa_debito' => 'Visa - Débito',
      'amex_credito' => 'Amex - Crédito',
      'vr_refeicao' => 'VR Refeição',
      'alelo_refeicao' => 'Alelo',
      'outro_refeicao' => 'Outro ticket refeição'
    ];
  }
}
