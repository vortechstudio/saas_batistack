import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";
import fs from 'fs'; // Importation du module File System de Node.js
import os from 'os'; // Importation du module OS de Node.js

function herdSsl(host) {
    // Herd fonctionne uniquement sur macOS
    if (os.platform() !== 'darwin') {
        return false;
    }

    const homeDir = os.homedir();
    const keyPath = `${homeDir}/Library/Application Support/Herd/config/valet/Certificates/${host}.key`;
    const certPath = `${homeDir}/Library/Application Support/Herd/config/valet/Certificates/${host}.crt`;

    // Si les fichiers de certificat n'existent pas, on ne fait rien
    if (!fs.existsSync(keyPath) || !fs.existsSync(certPath)) {
        return false;
    }

    // Retourne la configuration HTTPS pour Vite
    return {
        key: fs.readFileSync(keyPath),
        cert: fs.readFileSync(certPath),
    };
}

export default defineConfig(({ command }) => {

    // Récupère le APP_URL du .env. S'il n'est pas défini, utilise 'localhost' par défaut.
    // L'erreur "localhost" suggère que c'est ce domaine qui est utilisé.
    const appUrl = process.env.APP_URL || 'http://localhost';
    const host = new URL(appUrl).hostname; // Extrait 'localhost' ou 'batistack.test' etc.

    // Tente de charger la configuration SSL de Herd pour cet hôte
    const httpsConfig = herdSsl(host);

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0', // Écoute sur toutes les interfaces (corrige EADDRNOTAVAIL)
            port: 5173, // Vite tentera 5174 si ce port est pris

            // Active le HTTPS seulement si les certificats de Herd ont été trouvés
            https: httpsConfig || false,

            hmr: {
                host: host, // Dit au navigateur de se connecter à 'localhost' (ou l'hôte de l'APP_URL)
            }
        },
    };
});
