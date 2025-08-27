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

  public function empresa(): \Illuminate\Database\Eloquent\Relations\HasOne
  {
    return $this->hasOne(Empresa::class, 'endereco_id');
  }

  public function cliente(): \Illuminate\Database\Eloquent\Relations\HasOne
  {
    return $this->hasOne(Cliente::class, 'endereco_id');
  }

  public function enderecoFormatado(): string
  {
    $logradouro = trim((string)$this->logradouro);
    $numero = $this->numero !== null && $this->numero !== '' ? (string)$this->numero : 'S/N';
    $cidade = trim((string)$this->cidade);
    $uf = strtoupper((string)$this->uf);

    $primeiraParte = $logradouro !== '' ? $logradouro : '';
    if ($primeiraParte !== '') {
      $primeiraParte .= ', ' . $numero;
    } else {
      $primeiraParte = $numero !== '' ? $numero : '';
    }

    $segundaParte = '';
    if ($cidade !== '' && $uf !== '') {
      $segundaParte = $cidade . '/' . $uf;
    } elseif ($cidade !== '') {
      $segundaParte = $cidade;
    } elseif ($uf !== '') {
      $segundaParte = $uf;
    }

    if ($primeiraParte !== '' && $segundaParte !== '') {
      return $primeiraParte . ' - ' . $segundaParte;
    }

    return $primeiraParte !== '' ? $primeiraParte : $segundaParte;
  }
}
