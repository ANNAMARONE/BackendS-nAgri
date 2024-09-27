<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function produits()
    {
        return $this->belongsToMany(Produit::class)
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
