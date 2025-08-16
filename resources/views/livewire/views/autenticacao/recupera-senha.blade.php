<div class="flex justify-center items-center h-screen">
  @if($step === 'email')
    <x-card title="Processo de recuperação de senha" subtitle="Insira seu email por favor...">
      <x-form wire:submit.prevent="enviarEmail">
        <x-input label="Email" wire:model="email" type="email"/>

        <x-slot:actions>
          <x-button type="submit" label="Enviar código" wire:loading.attr="disabled" spinner="enviarEmail"
                    class="btn btn-primary w-full"/>
        </x-slot:actions>
      </x-form>
    </x-card>
  @endif

  @if($step === 'token')
    <x-card title="Processo de recuperação de senha" subtitle="Insira o código" separator>
      <x-pin wire:model="tokenValidacao" size="6" @completed="$wire.isCompleted = true" numeric/>

      <x-slot:actions>
        <div class="flex flex-col w-full">
          <x-button class="btn btn-primary w-full" label="Validar" wire:click="validarToken"/>

          <button
            x-data="{
            seconds: 0,
            start() {
                this.seconds = 120
                let timer = setInterval(() => {
                    if (this.seconds > 0) {
                        this.seconds--
                    } else {
                        clearInterval(timer)
                    }
                }, 1000)
              }
            }"
            x-on:click="$wire.enviarEmail(); start()"
            :disabled="seconds > 0"
            class="btn btn-primary w-full mt-4"
          >
            <span x-show="seconds === 0">Reenviar código</span>
            <span x-show="seconds > 0">Aguarde (<span x-text="seconds"></span>)</span>
          </button>
        </div>
      </x-slot:actions>
    </x-card>
  @endif

  @if($step === 'nova-senha')
    <x-card title="Processo de recuperação de senha" subtitle="Insira sua nova senha..." separator>
      <x-form wire:submit.prevent="alterarSenha">
        <x-password clearable label="Nova senha" wire:model="novaSenha"/>
        <x-password clearable label="Confirmar nova senha" wire:model="confirmarNovaSenha"/>
        <x-slot:actions>
          <x-button type="submit" label="Alterar senha" wire:loading.attr="disabled" spinner="alterarSenha"
                    class="btn btn-primary w-full"/>
        </x-slot:actions>
      </x-form>
    </x-card>
  @endif
</div>
