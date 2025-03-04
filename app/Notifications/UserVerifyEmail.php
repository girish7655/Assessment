<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;



class UserVerifyEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
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

     public function toMail($notifiable)
     {
         // Generate the verification URL
         $verificationUrl = URL::signedRoute('verification.verify', [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification())
        ]);
    
        // Render the email content with the view and pass necessary data
        $content = view('emails.verification', [
            'verificationUrl' => $verificationUrl,
            'notifiable' => $notifiable
        ])->render();
    
        // Inline CSS manually using CssToInlineStyles
        $cssToInline = new CssToInlineStyles();
        $contentWithInlineCss = $cssToInline->convert($content, '/* Your CSS styles */');
    
        // Return the MailMessage and use the 'view()' method
        return (new MailMessage)
            ->subject('Thanks for Joining Us! Please login to your account')
            ->line('Click the button below to login to your account.')
            ->view('emails.verification', [
                'contentWithInlineCss' => $contentWithInlineCss, 
                'verificationUrl' => $verificationUrl, 
                'notifiable' => $notifiable
            ]);
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
