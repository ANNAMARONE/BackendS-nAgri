<?php

namespace App\Mail;

use App\Models\Commande; 
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommandeInfo extends Mailable
{
    use Queueable, SerializesModels;

    public $commande;
    public $produits;
    public $montantTotal;

    /**
     * Create a new message instance.
     *
     * @param Commande $commande
     * @param  $produits
     * @param float $montantTotal
     */
    public function __construct(Commande $commande, $produits, float $montantTotal)
    {
        $this->commande = $commande;
        $this->produits = $produits; // Corrigez ici
        $this->montantTotal = $montantTotal; // Corrigez ici
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vous avez reçu une commande',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.commande.createdInfo',
        );
    }

    public function build()
    {
        return $this->markdown('emails.commande.createdInfo') // Corrigez le chemin de la vue si nécessaire
            ->subject('Vous avez une nouvelle commande')
            ->with([
                'commande' => $this->commande,
                'produits' => $this->produits,
                'montantTotal' => $this->montantTotal,
            ]);
    }
}
