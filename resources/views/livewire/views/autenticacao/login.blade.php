<div class="flex justify-center items-center h-screen">
  <x-card shadow separator progress-indicator>
    <slot:title>
      <div class="text-center">
        <h2 class="text-2xl font-bold ">Login</h2>
      </div>
    </slot:title>

    <slot:sub-title>
      <p>Entre com suas credenciais para entrar no sistema.</p>
    </slot:sub-title>

    <x-form wire:submit.prevent="logar" class="space-y-4 mt-4">
      <div class="space-y-2">
        <x-input label="Email" wire:model="email" required/>
      </div>

      <div class="space-y-2">
        <x-password label="Senha" wire:model="password" clearable required/>
      </div>

      <div class="flex items-center justify-between">
        <div>
          <x-checkbox label="Manter conectado?" wire:model="manterConectado" />
        </div>

        <a class="link" href="{{ route('autenticacao.recupera-senha') }}">
          Esqueceu sua senha?
        </a>
      </div>

      <x-button type="submit" class="btn btn-primary w-full" label="Entrar" wire:loading.attr="disabled" spinner="logar" />
    </x-form>

    <div class="text-center py-4">
      <p class="text-sm ">
        Não tem uma conta?
        <a href="{{ route('autenticacao.cadastro') }}" class="text-secondary link hover:underline">
          Crie sua conta.
        </a>
      </p>
    </div>
  </x-card>
</div>
