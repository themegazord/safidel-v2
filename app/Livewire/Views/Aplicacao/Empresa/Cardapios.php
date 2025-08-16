<?php

namespace App\Livewire\Views\Aplicacao\Empresa;

use App\Models\Cardapio;
use App\Livewire\Forms\Cardapio\CadastroForm;
use App\Livewire\Forms\Cardapio\EdicaoForm;
use App\Models\Empresa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class Cardapios extends Component
{
  use Toast;

  public Collection $cardapios;
  public bool $drawerCadastroCardapio = false;
  public bool $drawerEdicaoCardapio = false;
  public bool $confirmRemocao = false;
  public Empresa $empresa;
  public CadastroForm $cardapio;
  public EdicaoForm $cardapioEdicao;
  public Cardapio $cardapioAtual;

  public array $diasSemana = [
    ['id' => 0, 'nome' => 'Domingo'],
    ['id' => 1, 'nome' => 'Segunda-feira'],
    ['id' => 2, 'nome' => 'Terça-feira'],
    ['id' => 3, 'nome' => 'Quarta-feira'],
    ['id' => 4, 'nome' => 'Quinta-feira'],
    ['id' => 5, 'nome' => 'Sexta-feira'],
    ['id' => 6, 'nome' => 'Sábado']
  ];

  public array $tipoFuncionamento = [
    ['id' => 'delivery', 'name' => 'Delivery'],
    ['id' => 'retirada', 'name' => 'Retirada'],
    ['id' => 'mesa', 'name' => 'Atendimento na mesa'],
  ];

  public function mount(): void
  {
    $this->empresa = Auth::user()->empresa;
    $this->carregarCardapios();
  }

  #[Layout('components.layouts.empresa')]
  #[Title('Cardápios')]
  public function render()
  {
    return view('livewire.views.aplicacao.empresa.cardapios');
  }

  // Evita "strings mágicas" para campos permitidos
  private const CARDAPIO_FIELDS = ['nome', 'descricao', 'dias_funcionamento', 'tipo_funcionamento'];

  public function cadastrarCardapio(): void
  {
    $this->cardapio->validate();

    $dados = array_merge(
      $this->cardapio->only(self::CARDAPIO_FIELDS),
      ['empresa_id' => $this->empresa->id]
    );

    // Usa a relação para garantir a associação correta com a empresa
    $this->empresa->cardapios()->create($dados);

    $this->finalizarOperacaoCardapio('Cardápio cadastrado com sucesso!', 'drawerCadastroCardapio');
  }

  public function resetForm(): void
  {
    $this->cardapio->reset();
    $this->cardapioEdicao->reset();
  }

  public function setCardapioAtual(Cardapio $cardapio, string $modo): void
  {
    $this->cardapioAtual = $cardapio;

    if ($modo === 'edicao') {
      $this->drawerEdicaoCardapio = true;
      $this->cardapioEdicao->fill($cardapio->only(self::CARDAPIO_FIELDS));
    }

    if ($modo === 'remover') {
      $this->confirmRemocao = true;
    }
  }

  public function editarCardapio(): void
  {
    $this->cardapioEdicao->validate();

    // Atualiza apenas campos permitidos
    $this->cardapioAtual->update($this->cardapioEdicao->only(self::CARDAPIO_FIELDS));

    $this->finalizarOperacaoCardapio('Cardápio editado com sucesso!', 'drawerEdicaoCardapio');
  }

  public function removerCardapio(int $cardapioId): void
  {
    // Busca limitada à empresa atual para evitar remoções indevidas
    $cardapio = $this->empresa->cardapios()->whereKey($cardapioId)->first();

    if (!$cardapio) {
      $this->error('Cardápio inexistente');
      $this->finalizarRemocao();
      return;
    }

    $cardapio->delete();

    $this->success('Cardápio removido com sucesso!');
    $this->finalizarRemocao();
  }

  private function finalizarRemocao(): void
  {
    $this->carregarCardapios();
    $this->confirmRemocao = false;
  }


  private function carregarCardapios(): void
  {
    $this->cardapios = $this->empresa->cardapios()->get();
  }

  /**
   * Centraliza o fluxo pós-operação (cadastrar/editar): fecha drawer, recarrega, limpa e exibe mensagem.
   */
  private function finalizarOperacaoCardapio(string $mensagem, string $drawerProperty): void
  {
    $this->{$drawerProperty} = false;
    $this->carregarCardapios();
    $this->resetForm();
    $this->success($mensagem);
  }


}
