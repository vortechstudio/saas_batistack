@component('mail::message')
# Bienvenue chez BatiStack, {{ $user->name }} !

Nous sommes ravis de vous accueillir dans la communauté BatiStack.

Votre compte a été créé avec succès et votre adresse email a été vérifiée. Vous pouvez maintenant accéder à toutes les fonctionnalités de notre plateforme.

@if($user->customer)
**Informations de votre entreprise :**
- Nom de l'entreprise : {{ $user->customer->company_name }}
- Contact : {{ $user->customer->contact_name }}
- Email : {{ $user->customer->email }}
@endif

@component('mail::button', ['url' => route('dashboard')])
Accéder à mon tableau de bord
@endcomponent

## Prochaines étapes

1. **Explorez votre tableau de bord** - Découvrez toutes les fonctionnalités disponibles
2. **Configurez votre profil** - Complétez vos informations si nécessaire
3. **Contactez-nous** - Si vous avez des questions, notre équipe est là pour vous aider

Si vous avez des questions, n'hésitez pas à nous contacter.

Merci de nous faire confiance !

L'équipe BatiStack
@endcomponent
