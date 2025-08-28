<?php

namespace App\Traits;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

trait TrataMGCObjectStoreTrait
{
  use Toast, WithFileUploads;

  protected function criarClienteS3(): S3Client
  {
    return new S3Client([
      'region' => getenv('MGC_REGION'),
      'version' => 'latest',
      'credentials' => [
        'key' => getenv('MGC_ID'),
        'secret' => getenv('MGC_SECRET_ACCESS_KEY')
      ],
      'endpoint' => getenv('MGC_ENDPOINT'),
      'use_path_style_endpoint' => (bool) getenv('MGC_USE_PATH_STYLE_ENDPOINT'),
    ]);
  }

  public function uploadImagem(string $nomeBucket, TemporaryUploadedFile $imagem): string|bool
  {
    try {
      $s3client = $this->criarClienteS3();

      $caminhoArquivo = $imagem->getRealPath();
      $nomeArquivo = $imagem->getClientOriginalName();

      if (!file_exists($caminhoArquivo)) {
        $this->warning("O arquivo {$caminhoArquivo} não foi encontrado.");
        return 0;
      }

      $resultado = $s3client->putObject([
        'Bucket' => $nomeBucket,
        'Key' => uuid_create(),
        'SourceFile' => $caminhoArquivo,
        'ContentType' => mime_content_type($caminhoArquivo),
      ]);

      $this->success('Imagem enviada com sucesso!');

      return $resultado->get('ObjectURL');

    } catch (AwsException $e) {
      $this->warning('Erro ao enviar a imagem para a nuvem: ' . $e->getMessage());
      return 0;
    }
  }

  public function removeImagem(string $nomeBucket, string $link_imagem): void {
    try {
      $s3client = $this->criarClienteS3();

      $s3client->deleteObject([
        'Bucket' => $nomeBucket,
        'Key' => substr($link_imagem, -36)
      ]);
      $this->info('Imagem removida da nuvem');
    } catch (AwsException $e) {
      $this->warning('Erro ao remover a imagem da nuvem: ' . $e->getMessage());
    }
  }
}
