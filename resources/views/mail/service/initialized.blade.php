<x-mail::message>
# 🎉 Votre service Batistack est prêt !

Bonjour {{ $customer->first_name }},

Nous avons le plaisir de vous informer que votre service Batistack a été initialisé avec succès et est maintenant prêt à être utilisé.

## 📋 Détails de votre service
- **Produit :** {{ $product->name }}
- **Domaine :** {{ $service->domain }}
- **Date d'activation :** {{ $service->created_at->format('d/m/Y à H:i') }}
- **Statut :** {{ $service->status->label() }}

## 🔧 Informations d'installation
@if(isset($installationDetails['installation_completed']) && $installationDetails['installation_completed'])
✅ **Installation :** Terminée avec succès
@endif

@if(isset($installationDetails['modules_activated']) && count($installationDetails['modules_activated']) > 0)
## 🚀 Modules activés
@foreach($installationDetails['modules_activated'] as $module)
- {{ $module }}
@endforeach
@endif

@if(isset($installationDetails['features']) && count($installationDetails['features']) > 0)
## ⭐ Fonctionnalités incluses
@foreach($installationDetails['features'] as $feature)
- {{ $feature['name'] }}
@endforeach
@endif

## 🌐 Accès à votre service

<x-mail::button :url="'https://' . $service->domain">
Accéder à mon service
</x-mail::button>

## 📞 Support et assistance

Votre service est maintenant opérationnel. Si vous avez des questions ou besoin d'assistance, notre équipe support est à votre disposition.

<x-mail::button :url="route('client.dashboard')" color="secondary">
Accéder à mon tableau de bord
</x-mail::button>

## 📚 Prochaines étapes

1. **Connectez-vous** à votre service via le lien ci-dessus
2. **Configurez** vos paramètres selon vos besoins
3. **Explorez** les fonctionnalités disponibles
4. **Contactez le support** si vous avez des questions

Nous vous remercions pour votre confiance et vous souhaitons une excellente utilisation de Batistack !

Cordialement,<br>
L'équipe {{ config('app.name') }}

---

<small>
**Informations techniques :**<br>
Service ID: {{ $service->uuid }}<br>
Date d'initialisation: {{ now()->format('d/m/Y H:i:s') }}
</small>
</x-mail::message>
