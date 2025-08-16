<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SomenteEmpresaMiddleware
{
  private const FORBIDDEN_MESSAGE = 'Acesso negado';

  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = $request->user();

    if ($user === null || !$user->isEmpresa()) {
      abort(403, self::FORBIDDEN_MESSAGE);
    }

    return $next($request);
  }
}

