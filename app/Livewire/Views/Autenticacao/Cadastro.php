<?php

namespace App\Livewire\Views\Autenticacao;

use App\Livewire\Forms\Autenticacao\Cliente\CadastroForm as CadastroCliente;
use App\Livewire\Forms\Autenticacao\Empresa\CadastroForm as CadastroEmpresa;
use App\Livewire\Forms\Endereco\CadastroForm as CadastroEndereco;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Endereco;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class Cadastro extends Component
{
  use Toast;
  public $tipoCadastro = 'cliente'; // "cliente" ou "empresa"

  public CadastroCliente $cliente;
  public CadastroEmpresa $empresa;
  public CadastroEndereco $endereco;

  public function updated($prop): void {
   if ($prop === 'tipoCadastro') {
     $this->resetForm();
   }
  }
  public function cadastrar()
  {
    if ($this->tipoCadastro === 'cliente') {
      $this->cliente->limpaCaracteresEspeciais();
      $this->cliente->validate();
    } else {
      $this->empresa->limpaCaracteresEspeciais();
      $this->empresa->validate();
    }

    $this->endereco->limpaCaracteresEspeciais();
    $this->endereco->validate();

    $endereco = Endereco::query()->create($this->endereco->all());

    if ($this->tipoCadastro === 'cliente') {
      $this->cliente->endereco_id = $endereco->id;
      $this->cliente->data_nascimento = date('Y-m-d', strtotime($this->cliente->data_nascimento));
      Cliente::query()->create($this->cliente->all());
      User::query()->create([
        'name' => $this->cliente->nome,
        'email' => $this->cliente->email,
        'password' => \Hash::make($this->cliente->cpf),
      ]);
    } else {
      $this->empresa->endereco_id = $endereco->id;
      Empresa::query()->create($this->empresa->all());
      User::query()->create([
        'name' => $this->empresa->nome_fantasia,
        'email' => $this->empresa->email,
        'password' => \Hash::make($this->empresa->cnpj),
      ]);
    }

    $this->success('Conta criada com sucesso!', redirectTo: route('autenticacao.login'));
  }

  private function resetForm()
  {
    $this->cliente->reset();
    $this->empresa->reset();
    $this->endereco->reset();
  }

  #[Layout('components.layouts.autenticacao')]
  #[Title('Cadastro')]
  public function render(): \Illuminate\Contracts\View\View
  {
    return view('livewire.views.autenticacao.cadastro');
  }
}
