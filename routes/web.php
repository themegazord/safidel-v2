<?php

use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;
use App\Livewire\Views;

Route::group([], function () {
  Route::get('/login', Views\Autenticacao\Login::class)->name('autenticacao.login');
  Route::get('/logout', function () {
    auth()->logout();
    return redirect()->route('autenticacao.login');
  })->name('autenticacao.logout');
  Route::get('/cadastro', Views\Autenticacao\Cadastro::class)->name('autenticacao.cadastro');
  Route::get('/recupera-senha', Views\Autenticacao\RecuperaSenha::class)->name('autenticacao.recupera-senha');
});

Route::middleware(\App\Http\Middleware\SomenteEmpresaMiddleware::class)->prefix('empresa')->group(function () {
  Route::get('dashboard', Views\Aplicacao\Empresa\Dashboard::class)
    ->name('aplicacao.empresa.dashboard');
  Route::prefix('cardapios')->group(function () {
    Route::get('/', Views\Aplicacao\Empresa\Cardapios::class)->name('aplicacao.empresa.cardapios');
    Route::get('/categorias/{cardapio_id}/', Views\Aplicacao\Empresa\Categorias::class)->name('aplicacao.empresa.categorias');
  });
});
