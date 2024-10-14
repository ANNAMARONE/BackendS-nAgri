<?php

namespace App\Mail;

use App\Models\Commande; // Assurez-vous d'importer le modèle Commande
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommandeCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $commande;
    public $paymentLink;

    /**
     * Create a new message instance.
     *
     * @param Commande $commande
     * @param string|null $paymentLink
     */
    public function __construct(Commande $commande, $paymentLink = null)
    {
        $this->commande = $commande;
        $this->paymentLink = $paymentLink; 
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre commande a été créée',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.commande.created',
        );
    }

   

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->markdown('emails.commande.created')
                    ->subject('Votre commande a été créée')
                    ->with([
                        'commande' => $this->commande,
                        'paymentLink' => $this->paymentLink, 
                    ]);
    }
}
