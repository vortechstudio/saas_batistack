#!/bin/bash

# Définition de couleurs pour une meilleure lisibilité
RED="\033[0;31m"
GREEN="\033[0;32m"
YELLOW="\033[0;33m"
NC="\033[0m" # Pas de couleur

# Nom du fichier de sortie
OUTPUT_FILE="commit.txt"

echo -e "${YELLOW}Vérification des modifications Git en cours...${NC}"
echo "---------------------------------------------"

# Récupérer les modifications (format court)
# M = Modifié, A = Ajouté, D = Supprimé, ?? = Non suivi (Untracked)
CHANGES=$(git status -s)

# Vérifier si la variable CHANGES est vide (aucune modification)
if [ -z "$CHANGES" ]; then
    echo -e "${GREEN}Aucune modification détectée. L'arbre de travail est propre.${NC}"
    # Écrire aussi dans le fichier de sortie
    echo "Aucune modification détectée. L'arbre de travail est propre." > $OUTPUT_FILE
else
    # S'il y a des modifications, les afficher (résumé) DANS LE TERMINAL
    echo "Modifications et fichiers non suivis (Résumé) :"
    echo -e "${RED}$CHANGES${NC}"
    echo "---------------------------------------------"

    # Écrire le rapport complet dans commit.txt (SANS COULEURS)
    echo -e "${YELLOW}Génération du rapport complet dans $OUTPUT_FILE...${NC}"

    # Utiliser ">" pour (sur)écrire le fichier
    echo "Modifications et fichiers non suivis (Résumé) :" > $OUTPUT_FILE
    # Réutiliser la variable CHANGES
    echo "$CHANGES" >> $OUTPUT_FILE
    echo "---------------------------------------------" >> $OUTPUT_FILE

    # Afficher le DIFF pour les fichiers modifiés MAIS NON STAGED
    echo "Détail des modifications NON STAGED (non ajoutées) :" >> $OUTPUT_FILE
    # --no-color garantit un fichier texte propre
    git diff --no-color >> $OUTPUT_FILE
    echo "---------------------------------------------" >> $OUTPUT_FILE

    # Afficher le DIFF pour les fichiers modifiés ET STAGED (prêts pour commit)
    echo "Détail des modifications STAGED (ajoutées au commit) :" >> $OUTPUT_FILE
    git diff --cached --no-color >> $OUTPUT_FILE
    echo "---------------------------------------------" >> $OUTPUT_FILE

    echo -e "${GREEN}Rapport $OUTPUT_FILE généré avec succès.${NC}"


    # Le "prompt" : attendre que l'utilisateur appuie sur Entrée
    # 'read -p' affiche un message avant d'attendre l'input
    read -p "Appuyez sur [Entrée] pour terminer..."
fi

exit 0
