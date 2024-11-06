@component('mail::message')
# Bonjour {{ $commande->user->name }},

Nous tenons à vous informer qu'une commande a été passée contenant vos produits.

**Détails de la commande :**
- Référence de la commande : {{ $commande->references }}
- Montant total de vos produits : {{ $montantTotal }} FCFA

**Produits commandés :**
@foreach($produits as $produit)
- **Nom du produit :** {{ $produit->libelle}}
- **Quantité :** {{ $produit->pivot->quantite }}  
- **Prix unitaire :** {{ $produit->prix }} FCFA
- **Montant :** {{ $produit->pivot->quantite * $produit->prix }} FCFA
@endforeach

Merci pour votre confiance !



Merci,<br>
{{ config('app.name') }}
@endcomponent
