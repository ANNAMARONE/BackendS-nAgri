@component('mail::message')
# Nouvelle Commande Créée

Bonjour {{ $commande->user->name }},

Votre commande a été créée avec succès. Voici les détails :

- **Référence :** {{ $commande->references }}
- **Montant Total :** {{ $commande->montant_total }} FCFA
- **Méthode de Paiement :** {{ $commande->payment_method }}
- **Statut de la Commande :** {{ $commande->status_de_commande }}

@component('mail::button', ['url' => $paymentLink ?? '#'])
Payer Maintenant
@endcomponent

Merci d'avoir choisi SénAgri !

Cordialement,  
L'équipe SénAgri
@endcomponent
