<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Panier extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'panier_produit')
                    ->withPivot('quantite', 'prix_unitaire', 'montant_total', 'reference')
                    ->withTimestamps();
    }
    public function calculerMontantTotal()
    {
        $total = 0;

        foreach ($this->produits as $produit) {
            $total += $produit->pivot->montant_total;
        }

        return $total;
    }
    public function validerCommande()
    {
        $this->etat_commande = 'validée';
        $this->save();

        foreach ($this->produits as $produit) {
            $produit->quantite -= $produit->pivot->quantite;
            $produit->save();
        }
    }
    public function expedierCommande()
    {
        $this->etat_commande = 'expédiée';
        $this->save();
    }
    public function supprimerProduit($produitId)
    {
        $produit = $this->produits()->where('produit_id', $produitId)->first();

        if ($produit) {
            $this->produits()->detach($produitId);
            $this->montant_total -= $produit->pivot->montant_total;
            $this->save();
        }
    }
    public function modifierProduit($produitId, $nouvelleQuantite)
    {
        $produit = $this->produits()->where('produit_id', $produitId)->first();
    
        if ($produit) {
            // Mettre à jour la quantité du produit
            $produit->pivot->quantite = $nouvelleQuantite;
            $produit->pivot->save();
    
            // Recalculer le montant total du panier
            $this->montant_total = $this->produits->sum(function($produit) {
                return $produit->pivot->quantite * $produit->pivot->prix_unitaire;
            });
    
            $this->save();
        }
    }
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
