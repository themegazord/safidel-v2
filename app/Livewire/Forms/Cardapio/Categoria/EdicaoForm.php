<?php

namespace App\Livewire\Forms\Cardapio\Categoria;

use Livewire\Attributes\Validate;
use Livewire\Form;

class EdicaoForm extends Form
{
    private const TIPO_PIZZA = 'P';
    private const REQ_IF_PIZZA = 'required_if:tipo,' . self::TIPO_PIZZA;

    private const RULES = [
        'tipo' => ['required'],
        'nome' => ['required', 'max:50'],

        // Coleções exigidas quando for pizza
        'tamanhos' => [self::REQ_IF_PIZZA],
        'massas' => [self::REQ_IF_PIZZA],
        'bordas' => [self::REQ_IF_PIZZA],

        // Campos internos
        'tamanhos.*.external_id' => [self::REQ_IF_PIZZA],
        'tamanhos.*.nome' => [self::REQ_IF_PIZZA, 'max:50'],
        'tamanhos.*.qtde_pedacos' => [self::REQ_IF_PIZZA],
        'tamanhos.*.qtde_sabores' => [self::REQ_IF_PIZZA],

        'massas.*.nome' => [self::REQ_IF_PIZZA, 'max:50'],
        'massas.*.preco' => [self::REQ_IF_PIZZA],

        'bordas.*.nome' => [self::REQ_IF_PIZZA, 'max:50'],
        'bordas.*.preco' => [self::REQ_IF_PIZZA],
    ];

    private const MESSAGES = [
        // Campos raiz
        'tipo.required' => 'O campo tipo é obrigatório',
        'nome.required' => 'O campo nome é obrigatório',

        // Coleções
        'tamanhos.required_if' => 'O campo tamanhos é obrigatório',
        'massas.required_if' => 'O campo massas é obrigatório',
        'bordas.required_if' => 'O campo bordas é obrigatório',

        // Tamanhos
        'tamanhos.*.external_id.required_if' => 'O campo código PDV do tamanho é obrigatório',
        'tamanhos.*.nome.required_if' => 'O campo nome do tamanho é obrigatório',
        'tamanhos.*.qtde_pedacos.required_if' => 'O campo quantidade de pedaços é obrigatório',
        'tamanhos.*.qtde_sabores.required_if' => 'O campo quantidade de sabores é obrigatório',

        // Massas
        'massas.*.nome.required_if' => 'O campo nome da massa é obrigatório',
        'massas.*.preco.required_if' => 'O campo preço da massa é obrigatório',

        // Bordas
        'bordas.*.nome.required_if' => 'O campo nome da borda é obrigatório',
        'bordas.*.preco.required_if' => 'O campo preço da borda é obrigatório',

        // Limites de tamanho
        'nome.max' => 'O nome da categoria deve conter no máximo :max caracteres',
        'tamanhos.*.nome.max' => 'O nome do tamanho deve conter no máximo :max caracteres',
        'massas.*.nome.max' => 'O nome da massa deve conter no máximo :max caracteres',
        'bordas.*.nome.max' => 'O nome da borda deve conter no máximo :max caracteres',
    ];

    #[Validate(rule: self::RULES, message: self::MESSAGES)]
    public ?int $cardapio_id = null;

    public ?string $tipo = null;
    public ?string $nome = null;

    /**
     * @var array<int, array{
     *     id?: int,
     *     external_id?: string|null,
     *     nome?: string,
     *     qtde_pedacos?: int,
     *     qtde_sabores?: array<int,int>
     * }>
     */
    public array $tamanhos = [];

    /**
     * @var array<int, array{
     *     id?: int,
     *     external_id?: string|null,
     *     nome?: string,
     *     preco?: int|float
     * }>
     */
    public array $massas = [];

    /**
     * @var array<int, array{
     *     id?: int,
     *     external_id?: string|null,
     *     nome?: string,
     *     preco?: int|float
     * }>
     */
    public array $bordas = [];
}
