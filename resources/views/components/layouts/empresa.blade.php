<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">

{{-- NAVBAR mobile only --}}
<x-nav sticky class="lg:hidden">
  <x-slot:brand>
    SF
  </x-slot:brand>
  <x-slot:actions>
    <label for="main-drawer" class="lg:hidden me-3">
      <x-icon name="o-bars-3" class="cursor-pointer"/>
    </label>
  </x-slot:actions>
</x-nav>

{{-- MAIN --}}
<x-main full-width>
  {{-- SIDEBAR --}}
  <x-slot:sidebar drawer="main-drawer" class="bg-base-100 lg:bg-inherit">

    {{-- BRAND --}}
    <div class="ml-5 pt-5">{{ getenv('APP_NAME') }}</div>

    {{-- MENU --}}
    <x-menu activate-by-route>

      {{-- User --}}
      @if($user = auth()->user())
        <x-menu-separator/>

        <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
          <x-slot:actions>
            <x-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff" no-wire-navigate
                      link="/logout"/>
          </x-slot:actions>
        </x-list-item>

        <x-menu-separator/>
      @endif

      <x-menu-separator title="Desempenho e vendas"/>

      <x-menu-item title="Desempenho" icon="o-chart-bar"
                   link="{{ route('aplicacao.empresa.dashboard') }}"
                   no-wire-navigate/>
{{--      <x-menu-item title="Pedidos" icon="o-document-text"--}}
{{--                   link="{{ route('aplicacao.empresa.pedidos', ['cnpj' => substr(request()->path(), 0, 14)]) }}"--}}
{{--                   no-wire-navigate/>--}}
{{--      <x-menu-item title="Pedidos mesa" icon="o-tag"--}}
{{--                   link="{{ route('aplicacao.empresa.pedido_mesa', ['cnpj' => substr(request()->path(), 0, 14)]) }}"--}}
{{--                   no-wire-navigate/>--}}

      <x-menu-separator title="Marketing"/>

{{--      <x-menu-item title="Seus clientes" icon="o-users" link="####" no-wire-navigate/>--}}
{{--      <x-menu-item title="Promoções" icon="o-percent-badge"--}}
{{--                   link="{{ route('aplicacao.empresa.promocoes.listagem', ['cnpj' => substr(request()->path(), 0, 14)]) }}"--}}
{{--                   no-wire-navigate/>--}}

      <x-menu-separator title="Configurações da Loja"/>

      <x-menu-item title="Cardápios" icon="o-book-open"
                   link="{{route('aplicacao.empresa.cardapios')}}"
                   no-wire-navigate/>
{{--      <x-menu-item title="Configurações de entrega" icon="o-map-pin"--}}
{{--                   link="{{ route('aplicacao.empresa.config_entrega', ['cnpj' => substr(request()->path(), 0, 14)]) }}"--}}
{{--                   no-wire-navigate/>--}}
{{--      <x-menu-item title="Horários" icon="o-clock"--}}
{{--                   link="{{ route('aplicacao.empresa.horarios', ['cnpj' => substr(request()->path(), 0, 14)]) }}"--}}
{{--                   no-wire-navigate/>--}}
{{--      <x-menu-item title="Agendamentos" icon="o-calendar-days" link="####" no-wire-navigate/>--}}
{{--      <x-menu-item title="Formas de pagamento" icon="o-credit-card"--}}
{{--                   link="{{route('aplicacao.empresa.forma_pagamento', ['cnpj' => substr(request()->path(), 0, 14)])}}"--}}
{{--                   no-wire-navigate/>--}}
{{--      <x-menu-item title="QR Code das mesas" icon="o-qr-code"--}}
{{--                   link="{{route('aplicacao.empresa.qrcode', ['cnpj' => substr(request()->path(), 0, 14)])}}"--}}
{{--                   no-wire-navigate/>--}}

      <x-menu-sub title="Sua loja" icon="o-home-modern">
{{--        <x-menu-item title="Loja"--}}
{{--                     link="{{route('aplicacao.empresa.config_empresa.loja', ['cnpj' => substr(request()->path(), 0, 14)])}}"--}}
{{--                     no-wire-navigate/>--}}
{{--        <x-menu-item title="Endereço"--}}
{{--                     link="{{route('aplicacao.empresa.config_empresa.endereco', ['cnpj' => substr(request()->path(), 0, 14)])}}"--}}
{{--                     no-wire-navigate/>--}}
{{--        <x-menu-item title="Integrações"--}}
{{--                     link="{{route('aplicacao.empresa.config_empresa.integracoes', ['cnpj' => substr(request()->path(), 0, 14)])}}"--}}
{{--                     no-wire-navigate/>--}}
{{--        <x-menu-item title="Configurações"--}}
{{--                     link="{{route('aplicacao.empresa.config_empresa.configuracoes', ['cnpj' => substr(request()->path(), 0, 14)])}}"--}}
{{--                     no-wire-navigate/>--}}
      </x-menu-sub>

      <x-menu-separator title="Ajuda"/>

      <x-menu-item title="Chamados e ajuda" icon="o-question-mark-circle" link="####" no-wire-navigate/>

      <!-- Botão de logout -->

      <x-menu-item title="Sair" icon="o-arrow-left-end-on-rectangle"
                   link="{{ route('autenticacao.logout') }}" no-wire-navigate/>

    </x-menu>
  </x-slot:sidebar>

  {{-- The `$slot` goes here --}}
  <x-slot:content>
    {{ $slot }}
  </x-slot:content>
</x-main>

{{--  TOAST area --}}
<x-toast/>
</body>
</html>
