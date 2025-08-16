<?php

namespace App\Models;

use App\Models\Pedido;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Empresa extends Model
{
  use HasFactory, Notifiable;

  protected $fillable = [
    'interacao_id',
    "endereco_id",
    "razao_social",
    "nome_fantasia",
    "cnpj",
    "chave_pix",
    "email",
    'telefone_comercial',
    'telefone_contato',
    'telefone_whatsapp',
    'logo',
    'capa',
    'tokenIfood',
    'lifetimeTokenIfood',
    'esta_recebendo_pedidos_ifood'
  ];

  public function fusosHorarios(): array
  {
    return [
      ['id' => 1, 'name' => 'America/Araguaina'],
      ['id' => 2, 'name' => 'America/Bahia'],
      ['id' => 3, 'name' => 'America/Belem'],
      ['id' => 4, 'name' => 'America/Boa_Vista'],
      ['id' => 5, 'name' => 'America/Campo_Grande'],
      ['id' => 6, 'name' => 'America/Cuiaba'],
      ['id' => 7, 'name' => 'America/Eirunepe'],
      ['id' => 8, 'name' => 'America/Fortaleza'],
      ['id' => 9, 'name' => 'America/Maceio'],
      ['id' => 10, 'name' => 'America/Manaus'],
      ['id' => 11, 'name' => 'America/Noronha'],
      ['id' => 12, 'name' => 'America/Porto_Velho'],
      ['id' => 13, 'name' => 'America/Recife'],
      ['id' => 14, 'name' => 'America/Rio_Branco'],
      ['id' => 15, 'name' => 'America/Santarem'],
      ['id' => 16, 'name' => 'America/Sao_Paulo'],
    ];
  }


  public function endereco(): \Illuminate\Database\Eloquent\Relations\BelongsTo
  {
    return $this->belongsTo(Endereco::class, 'endereco_id');
  }

  public function cardapios(): \Illuminate\Database\Eloquent\Relations\HasMany
  {
    return $this->hasMany(Cardapio::class, 'empresa_id');
  }

  public function pedidos(): \Illuminate\Database\Eloquent\Relations\HasMany
  {
    return $this->hasMany(Pedido::class, 'empresa_id');
  }

  public function pedidosRecentes(): Collection
  {
    return $this->pedidos()
        ->latest() // Ordena por created_at DESC
        ->whereDate('created_at', now()->toDateString()) // Filtra apenas pedidos do dia atual
        ->whereIn('status', ['pendente', 'sendo preparado', 'pronto para entrega']) // Filtra por status ativos
        ->get();
  }

  public function taxas_entrega(): \Illuminate\Database\Eloquent\Relations\HasMany
  {
    return $this->hasMany(TaxaEntrega::class, 'empresa_id');
  }

  public function integracoes(): HasMany
  {
    return $this->hasMany(Integracao::class, 'empresa_id');
  }

  public function configuracoes(): HasMany
  {
    return $this->hasMany(Configuracao::class, 'empresa_id');
  }

  public function promocoes(): HasMany
  {
    return $this->hasMany(Promocao::class, 'empresa_id');
  }

  public function mesa(): HasMany
  {
    return $this->hasMany(Mesa::class);
  }

  public function horarios_indisponibilidade(): HasMany
  {
    return $this->hasMany(HorarioFuncionamento::class);
  }

  public function horarios_indisponibilidades(): HasMany {
    return $this->hasMany(HorarioIndisponibilidade::class);
  }
}
