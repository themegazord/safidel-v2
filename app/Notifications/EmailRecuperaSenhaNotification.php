<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailRecuperaSenhaNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new notification instance.
   */
  public function __construct(private readonly string $token)
  {
    //
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['mail'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toMail(object $notifiable): MailMessage
  {
    return (new MailMessage)
      ->subject('Código de Recuperação de Senha')
      ->greeting('Olá ' . $notifiable->name . ',')
      ->line('Você solicitou a redefinição da sua senha.')
      ->line('Aqui está o seu código de verificação:')
      ->line('**' . $this->token . '**')
      ->line('Este código é válido por 2 minutos.')
      ->line('Se você não solicitou a recuperação de senha, ignore este e-mail.')
      ->salutation('Atenciosamente, Equipe Safi Delivery');
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return [
      //
    ];
  }
}
