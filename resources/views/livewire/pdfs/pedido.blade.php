<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <style>
    @page {
      margin: 0;
      padding: 0;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 0;
      padding: 0;
    }

    .container {
      width: 100%;
      padding: 10px;
    }

    .header {
      margin: 10px 0;
    }

    .subdados-pedido {
      margin-bottom: 10px;
    }

    .header h1 {
      font-size: 14px;
      margin: 0;
    }

    .fixed-width {
      width: 20%;
    }

    .header p {
      margin: 0;
    }

    .product-list {
      width: 100%;
      border-collapse: collapse;
    }

    .product-list th,
    .product-list td {
      text-align: left;
      padding: 4px 0;
      border-bottom: 1px dashed #000;
    }

    .product-list th {
      font-weight: bold;
    }

    .col-produto {
      width: 40%;
    }

    .col-preco {
      width: 30%;
    }

    .col-total {
      width: 30%;
    }

    .total {
      margin-top: 10px;
      margin-right: 45px;
      text-align: right;
    }

    .footer {
      text-align: center;
      margin-top: 10px;
      border-top: 1px dashed #000;
      padding-top: 5px;
    }
  </style>
</head>
<body>
@php
  // Helpers e variáveis extraídas
  $empresa = $pedido->empresa;
  $end = optional($empresa)->endereco;

  $formatMoney = fn($value) => 'R$ ' . number_format((float)($value ?? 0), 2, ',', '.');
  $na = '—';

  $fmtCep = function ($cep) {
      if (!$cep) return '';
      return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
  };
  $fmtCnpj = function ($cnpj) {
      if (!$cnpj) return '';
      return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
  };
@endphp
<div class="container">
  <div class="header">
    <h1>{{ optional($empresa)->nome_fantasia }}</h1>
    <p>{{ optional($end)->logradouro }}, {{ optional($end)->numero }} - {{ optional($end)->bairro }}</p>
    <p>{{ optional($end)->cidade }}/{{ optional($end)->uf }}</p>
    <p>CEP: {{ $fmtCep(optional($end)->cep) }}</p>
    <p>CNPJ: {{ $fmtCnpj(optional($empresa)->cnpj) }}</p>
  </div>
  <hr>
  <div class="subdados-pedido">
    <p><b>PEDIDO: NR: #{{ $pedido->id }}</b></p>
    <p><b>TIPO: {{ $pedido->defineTipoPedido() }}</b></p>
    <p><b>Cliente: {{ $pedido->cliente_id !== null ? optional($pedido->cliente)->nome : $pedido->nome }}</b></p>
  </div>
  @if (!is_null($pedido->mesa) && !is_null($pedido->comanda))
    <hr>
    <div class="subdados-pedido">
      <p><b>MESA: NR: #{{ $pedido->mesa }}</b></p>
    </div>
  @endif
  <hr>
  <table class="product-list">
    <thead>
    <tr>
      <th class="col-produto">Produto</th>
      <th class="col-preco">Preço</th>
      <th class="col-total">Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($pedido->itens as $item)
      @php
        $isPizza = $item->tipo === 'P';
        $isItem = $item->tipo === 'I';

        $hasSabores = $isPizza && !$item->sabores->isEmpty();
        $hasComplementos = $isItem && !$item->complementos->isEmpty();

        $subtotalSabores = $hasSabores ? (float)$item->sabores->sum('preco_unitario') : 0.0;
        $subtotalComplementos = $hasComplementos ? (float)$item->complementos->sum('preco_unitario') : 0.0;

        $bordaPreco = (float)optional($item->borda)->preco ?? 0.0;
        $massaPreco = (float)optional($item->massa)->preco ?? 0.0;

        $precoUnitario = (float)$item->preco_unitario;
        $totalSimples = $isItem ? ($item->quantidade * $precoUnitario) : 0.0;
      @endphp

      @if(($isPizza && $hasSabores) || ($isItem && $hasComplementos))
        <tr>
          <td style="border-bottom: none;">{{ $item->quantidade }}x {{ $item->nome }}</td>
          <td style="border-bottom: none;">{{ $isItem ? $formatMoney($precoUnitario) : $na }}</td>
          <td style="border-bottom: none;">{{ $isItem ? $na : $na }}</td>
        </tr>
      @endif

      @if (($isPizza && !$hasSabores) || ($isItem && !$hasComplementos))
        <tr>
          <td>{{ $item->quantidade }}x {{ $item->nome }}</td>
          <td>{{ $isItem ? $formatMoney($precoUnitario) : $na }}</td>
          <td>{{ $isItem ? $formatMoney($totalSimples) : $na }}</td>
        </tr>
      @endif

      @if($isPizza)
        <tr>
          <td style="border-bottom: none;" class="complement">Borda: {{ optional($item->borda)->nome }}</td>
          <td style="border-bottom: none;">{{ $na }}</td>
          <td style="border-bottom: none;">{{ $formatMoney($bordaPreco) }}</td>
        </tr>
        <tr>
          <td style="border-bottom: none;" class="complement">Massa: {{ optional($item->massa)->nome }}</td>
          <td style="border-bottom: none;">{{ $na }}</td>
          <td style="border-bottom: none;">{{ $formatMoney($massaPreco) }}</td>
        </tr>
      @endif

      @if($hasSabores)
        @foreach($item->sabores as $sabor)
          <tr>
            <td style="border-bottom: none;" class="complement">+{{ $sabor->qtde }}x {{ $sabor->nome }}</td>
            <td style="border-bottom: none;">{{ $na }}</td>
            <td style="border-bottom: none;">{{ $formatMoney($sabor->preco_unitario) }}</td>
          </tr>
        @endforeach
        <tr>
          <td>Total</td>
          <td></td>
          <td>{{ $formatMoney($subtotalSabores + $bordaPreco + $massaPreco) }}</td>
        </tr>
      @endif

      @if($hasComplementos)
        @foreach($item->complementos as $complemento)
          <tr>
            <td style="border-bottom: none;" class="complement">+{{ $complemento->qtde }}
              x {{ $complemento->nome }}</td>
            <td style="border-bottom: none;">{{ $na }}</td>
            <td style="border-bottom: none;">{{ $formatMoney($complemento->preco_unitario) }}</td>
          </tr>
        @endforeach
        <tr>
          <td>Total</td>
          <td></td>
          <td>{{ $formatMoney($subtotalComplementos + $precoUnitario) }}</td>
        </tr>
      @endif
    @endforeach
    </tbody>
  </table>
  <div class="total">
    <p><strong>Total: {{ $formatMoney(optional($pedido->financeiro)->total) }}</strong></p>
  </div>
  <div class="footer">
    <p>Obrigado pela preferência!</p>
    <p>Volte sempre!</p>
  </div>
</div>
</body>
</html>
