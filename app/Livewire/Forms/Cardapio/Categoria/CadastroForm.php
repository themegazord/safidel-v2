<?php

namespace App\Livewire\Forms\Cardapio\Categoria;

use Livewire\Attributes\Validate;
use Livewire\Form;

class CadastroForm extends Form
{
    // Domínio
    private const TIPO_PIZZA = 'P';
    private const REQ_IF_PIZZA = 'required_if:tipo,' . self::TIPO_PIZZA;

    // Defaults tipados e centralizados
    private const DEFAULT_TAMANHO = [
        'external_id' => null,
        'nome' => '',
        'qtde_pedacos' => 0,
        'qtde_sabores' => [1],
    ];
    private const DEFAULT_MASSA = [
        'nome' => '',
        'preco' => 0,
    ];
    private const DEFAULT_BORDA = [
        'nome' => '',
        'preco' => 0,
    ];

    // Regras de validação
    private const RULES = [
        'tipo' => ['required'],
        'nome' => ['required', 'max:50'],
        'tamanho' => [self::REQ_IF_PIZZA],
        'massa' => [self::REQ_IF_PIZZA],
        'borda' => [self::REQ_IF_PIZZA],
        'tamanho.*.external_id' => [self::REQ_IF_PIZZA],
        'tamanho.*.nome' => [self::REQ_IF_PIZZA, 'max:50'],
        'tamanho.*.qtde_pedacos' => [self::REQ_IF_PIZZA],
        'tamanho.*.qtde_sabores' => [self::REQ_IF_PIZZA],
        'massa.*.nome' => [self::REQ_IF_PIZZA, 'max:50'],
        'massa.*.preco' => [self::REQ_IF_PIZZA],
        'borda.*.nome' => [self::REQ_IF_PIZZA, 'max:50'],
        'borda.*.preco' => [self::REQ_IF_PIZZA],
    ];

    // Mensagens específicas por campo para "obrigatório" + limites de tamanho
    private const MESSAGES = [
        // Campos raiz
        'tipo.required' => 'O campo tipo é obrigatório',
        'nome.required' => 'O campo nome é obrigatório',

        // Coleções (quando tipo = P)
        'tamanho.required_if' => 'O campo tamanhos é obrigatório',
        'massa.required_if' => 'O campo massas é obrigatório',
        'borda.required_if' => 'O campo bordas é obrigatório',

        // Tamanhos
        'tamanho.*.external_id.required_if' => 'O campo código PDV do tamanho é obrigatório',
        'tamanho.*.nome.required_if' => 'O campo nome do tamanho é obrigatório',
        'tamanho.*.qtde_pedacos.required_if' => 'O campo quantidade de pedaços é obrigatório',
        'tamanho.*.qtde_sabores.required_if' => 'O campo quantidade de sabores é obrigatório',

        // Massas
        'massa.*.nome.required_if' => 'O campo nome da massa é obrigatório',
        'massa.*.preco.required_if' => 'O campo preço da massa é obrigatório',

        // Bordas
        'borda.*.nome.required_if' => 'O campo nome da borda é obrigatório',
        'borda.*.preco.required_if' => 'O campo preço da borda é obrigatório',

        // Limites de tamanho
        'nome.max' => 'O nome da categoria deve conter no máximo :max caracteres',
        'tamanho.*.nome.max' => 'O nome do tamanho deve conter no máximo :max caracteres',
        'massa.*.nome.max' => 'O nome da massa deve conter no máximo :max caracteres',
        'borda.*.nome.max' => 'O nome da borda deve conter no máximo :max caracteres',
    ];

    #[Validate(rule: self::RULES, message: self::MESSAGES)]
    public ?int $cardapio_id = null;

    public ?string $tipo = null;
    public ?string $nome = null;

    /**
     * @var array<int, array{
     *     external_id: string|null,
     *     nome: string,
     *     qtde_pedacos: int,
     *     qtde_sabores: array<int,int>
     * }>
     */
    public array $tamanho = [self::DEFAULT_TAMANHO];

    /**
     * @var array<int, array{
     *     nome: string,
     *     preco: int|float
     * }>
     */
    public array $massa = [self::DEFAULT_MASSA];

    /**
     * @var array<int, array{
     *     nome: string,
     *     preco: int|float
     * }>
     */
    public array $borda = [self::DEFAULT_BORDA];
}
