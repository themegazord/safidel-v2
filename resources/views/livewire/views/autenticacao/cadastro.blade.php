<div class="flex justify-center items-center py-8">
  <x-card title="Cadastro" subtitle="Selecione se você deseja cadastrar como cliente ou empresa" class="w-1/2 overflow-y-scroll max-h-screen">
    <div class="mb-6">
      @php
        $tipos = [
            ['id' => 'cliente', 'name' => 'Cliente'],
            ['id' => 'empresa', 'name' => 'Empresa'],
        ];

        $configDatepicker = ['altFormat' => 'd/m/Y'];

        $estados = [
          ['id' => 'AC', 'name' => 'Acre'],
          ['id' => 'AL', 'name' => 'Alagoas'],
          ['id' => 'AP', 'name' => 'Amapá'],
          ['id' => 'AM', 'name' => 'Amazonas'],
          ['id' => 'BA', 'name' => 'Bahia'],
          ['id' => 'CE', 'name' => 'Ceará'],
          ['id' => 'DF', 'name' => 'Distrito Federal'],
          ['id' => 'ES', 'name' => 'Espírito Santo'],
          ['id' => 'GO', 'name' => 'Goiás'],
          ['id' => 'MA', 'name' => 'Maranhão'],
          ['id' => 'MT', 'name' => 'Mato Grosso'],
          ['id' => 'MS', 'name' => 'Mato Grosso do Sul'],
          ['id' => 'MG', 'name' => 'Minas Gerais'],
          ['id' => 'PA', 'name' => 'Pará'],
          ['id' => 'PB', 'name' => 'Paraíba'],
          ['id' => 'PR', 'name' => 'Paraná'],
          ['id' => 'PE', 'name' => 'Pernambuco'],
          ['id' => 'PI', 'name' => 'Piauí'],
          ['id' => 'RJ', 'name' => 'Rio de Janeiro'],
          ['id' => 'RN', 'name' => 'Rio Grande do Norte'],
          ['id' => 'RS', 'name' => 'Rio Grande do Sul'],
          ['id' => 'RO', 'name' => 'Rondônia'],
          ['id' => 'RR', 'name' => 'Roraima'],
          ['id' => 'SC', 'name' => 'Santa Catarina'],
          ['id' => 'SP', 'name' => 'São Paulo'],
          ['id' => 'SE', 'name' => 'Sergipe'],
          ['id' => 'TO', 'name' => 'Tocantins'],
        ];

      @endphp
      <x-group label="Defina o tipo do cadastro" wire:model.change="tipoCadastro" :options="$tipos"/>

      <x-form wire:submit.prevent="cadastrar">
        @if($tipoCadastro === 'cliente')
          <x-input label="Nome" wire:model="cliente.nome" required/>
          <x-input label="Email" wire:model="cliente.email" required/>
          <x-input label="CPF" wire:model="cliente.cpf" required/>
          <x-input label="Telefone" wire:model="cliente.telefone" required/>
          <x-datepicker label="Data de nascimento" wire:model="cliente.data_nascimento" :config="$configDatepicker"
                        icon="o-calendar" required/>
        @else
          <x-input label="Razão social" wire:model="empresa.razao_social" required/>
          <x-input label="Nome fantasia" wire:model="empresa.nome_fantasia" required/>
          <x-input label="CNPJ" wire:model="empresa.cnpj" required/>
          <x-input label="Email" wire:model="empresa.email" required/>
          <x-input label="Telefone Comercial" wire:model="empresa.telefone_comercial" required/>
          <x-input label="Telefone Whatsapp" wire:model="empresa.telefone_whatsapp" required/>
          <x-input label="Telefone Contato" wire:model="empresa.telefone_contato" required/>
        @endif

        <div class="divider my-4"></div>

        <div class="flex flex-col md:grid md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <x-input label="Logradouro" wire:model="endereco.logradouro" required/>
          </div>
          <div class="md:col-span-1">
            <x-input label="Número" wire:model="endereco.numero" />
          </div>
        </div>

        <div class="flex flex-col md:grid md:grid-cols-2 gap-4">
          <div class="md:col-span-1">
            <x-input label="Bairro" wire:model="endereco.bairro" required />
          </div>
          <div class="md:col-span-1">
            <x-input label="CEP" wire:model="endereco.cep" required />
          </div>
        </div>

        <div class="flex flex-col md:grid md:grid-cols-2 gap-4">
          <div class="md:col-span-1">
            <x-input label="Cidade" wire:model="endereco.cidade" required />
          </div>
          <div class="md:col-span-1">
            <x-select label="UF" placeholder="Selecione um estado..." :options="$estados" wire:model="endereco.uf" required />
          </div>
        </div>

        <x-textarea label="Complemento" wire:model="endereco.complemento" placeholder="Insira seu complemento" rows="3"/>

        <x-button class="btn btn-primary w-full" label="Cadastrar" type="submit" wire:loading.attr="disabled" spinner="cadastrar"/>
      </x-form>
    </div>
  </x-card>
</div>
