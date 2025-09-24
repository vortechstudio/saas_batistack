<x-mail::message>
# ⚠️ Suppression de votre compte Batistack programmée

Bonjour {{ $customer->user->fullname }},

Nous avons bien reçu votre demande de suppression de compte. Cette action a été programmée et sera effective dans **7 jours**.

## 📅 Informations importantes

- **Date de la demande :** {{ $deletionRequest->requested_at->format('d/m/Y à H:i') }}
- **Suppression programmée le :** {{ $deletionRequest->scheduled_for->format('d/m/Y à H:i') }}
- **Adresse IP :** {{ $deletionRequest->ip_address }}

@if($deletionRequest->reason)
## 💬 Raison indiquée
{{ $deletionRequest->reason }}
@endif

## 🚨 Ce qui va se passer

**Immédiatement :**
- Votre compte a été désactivé temporairement
- Tous vos services ont été suspendus
- Vos méthodes de paiement ont été désactivées

**Dans 7 jours ({{ $deletionRequest->scheduled_for->format('d/m/Y') }}) :**
- **Suppression définitive** de toutes vos données personnelles
- **Résiliation automatique** de tous vos services actifs
- **Suppression** de tous vos fichiers et configurations
- **Anonymisation** des données conservées pour conformité légale

## 🔄 Vous pouvez encore annuler

Si vous changez d'avis, vous pouvez **annuler cette suppression** à tout moment avant le {{ $deletionRequest->scheduled_for->format('d/m/Y à H:i') }}.

<x-mail::button :url="route('client.account.dashboard', ['action' => 'cancelDeletion'])" color="error">
Annuler la suppression de mon compte
</x-mail::button>

## 📋 Données qui seront conservées

Conformément au RGPD et aux obligations légales, certaines données seront conservées de manière anonymisée :
- Historique des transactions (sans données personnelles)
- Logs de sécurité (anonymisés)
- Données de facturation (pour conformité fiscale)

## 📞 Besoin d'aide ?

Si cette demande n'émane pas de vous ou si vous avez des questions, contactez immédiatement notre support :

<x-mail::button url="mailto:support@batistack.com" color="secondary">
Contacter le support
</x-mail::button>

## ⏰ Rappel important

**Cette action sera irréversible après le {{ $deletionRequest->scheduled_for->format('d/m/Y à H:i') }}.**

Nous regrettons de vous voir partir et espérons vous revoir bientôt sur Batistack.

Cordialement,<br>
L'équipe {{ config('app.name') }}

---

<small>
**Informations techniques :**<br>
Demande ID: {{ $customer->id }}<br>
Date de traitement: {{ now()->format('d/m/Y H:i:s') }}<br>
Confirmations reçues: Perte de données ({{ $deletionRequest->confirmations['data_loss'] ? 'Oui' : 'Non' }}), Résiliation services ({{ $deletionRequest->confirmations['services_termination'] ? 'Oui' : 'Non' }}), Facturation finale ({{ $deletionRequest->confirmations['billing_final'] ? 'Oui' : 'Non' }})
</small>
</x-mail::message>
