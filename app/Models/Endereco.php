<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Endereco extends Model
{
  use HasFactory;
  protected $fillable = [
    'logradouro',
    'bairro',
    'cidade',
    'uf',
    'cep',
    'numero',
    'complemento'
  ];

  public function empresa(): \Illuminate\Database\Eloquent\Relations\HasOne {
    return $this->hasOne(Empresa::class, 'endereco_id');
  }

  public function cliente(): \Illuminate\Database\Eloquent\Relations\HasOne {
    return $this->hasOne(Cliente::class,'endereco_id');
  }
}
