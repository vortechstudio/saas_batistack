<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur Batistack</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f5f9ff; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; background-color: #f5f9ff; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); }

        /* Header */
        .header { background-color: #0050d8; padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: bold; letter-spacing: -0.5px; }

        /* Content */
        .content { padding: 40px; color: #334155; line-height: 1.6; font-size: 16px; }
        .greeting { font-size: 20px; font-weight: bold; color: #0f172a; margin-bottom: 20px; }

        /* Features Grid */
        .features { display: table; width: 100%; margin: 30px 0; border-spacing: 10px; }
        .feature-item { display: table-cell; width: 33%; background: #f8fafc; padding: 15px; border-radius: 8px; text-align: center; vertical-align: top; border: 1px solid #e2e8f0; }
        .feature-icon { font-size: 24px; margin-bottom: 10px; display: block; }
        .feature-text { font-size: 13px; font-weight: 600; color: #475569; margin: 0; }

        /* Button */
        .btn-container { text-align: center; margin-top: 30px; margin-bottom: 20px; }
        .btn { display: inline-block; background-color: #0050d8; color: #ffffff; padding: 14px 32px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 16px; box-shadow: 0 4px 6px rgba(0, 80, 216, 0.2); transition: background-color 0.2s; }
        .btn:hover { background-color: #000e9c; }

        /* Footer */
        .footer { background-color: #010b40; color: #94a3b8; padding: 25px; text-align: center; font-size: 12px; border-top: 4px solid #00d0d4; }
        .footer a { color: #00d0d4; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Batistack</h1>
        </div>

        <!-- Body -->
        <div class="content">
            <div class="greeting">Bonjour {{ $user->name }},</div>

            <p>Nous sommes ravis de vous accueillir ! Votre espace Batistack est prêt.</p>

            <p>Vous avez maintenant entre les mains l'outil conçu pour simplifier la gestion de votre activité BTP. Voici par où commencer :</p>

            <!-- Quick Start Grid -->
            <div class="features">
                <div class="feature-item">
                    <span class="feature-icon">📝</span>
                    <p class="feature-text">Créez votre<br>premier devis</p>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">🏗️</span>
                    <p class="feature-text">Configurez<br>un chantier</p>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">⚙️</span>
                    <p class="feature-text">Complétez<br>votre profil</p>
                </div>
            </div>

            <p>Notre équipe de support est disponible 24/7 si vous avez la moindre question lors de votre démarrage.</p>

            <div class="btn-container">
                <a href="{{ $dashboardUrl }}" class="btn">Accéder à mon espace</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} Vortech Studio - Batistack.<br>La solution de gestion pour les pros du BTP.</p>
            <p>
                <a href="#">Centre d'aide</a> • <a href="#">Nous contacter</a> • <a href="#">Se désabonner</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
