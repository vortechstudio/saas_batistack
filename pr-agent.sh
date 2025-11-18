#!/bin/bash

# Configuration
PR_NUMBER="$1"                  # Numéro de la PR (en argument)
OLLAMA_MODEL="llama3"           # Modèle Ollama à utiliser
OLLAMA_URL="http://localhost:11434/api/generate"

if [ -z "$PR_NUMBER" ]; then
    echo "❌ Utilisation : $0 <numero_pr>"
    exit 1
fi

# 🔄 Récupération des commits
echo "📥 Récupération des commits de la PR #$PR_NUMBER..."
COMMITS=$(gh pr view "$PR_NUMBER" --json commits --jq '.commits[].messageHeadline')

if [ -z "$COMMITS" ]; then
    echo "❌ Aucun commit trouvé pour la PR #$PR_NUMBER"
    exit 1
fi

# 🧠 Préparation prompt pour Ollama
PROMPT="Voici une liste de commits d'une Pull Request : [$COMMITS], Génère une description claire, professionnelle,
concise et orientée utilisateur de cette PR.
Écris en français. Format Markdown. Sans Résonnement.
Seul les commit (feat, fix, release, breaking) doivent être pris en compte.
Essaye de différencier les types de commit et met les en forme (ex: feat => Nouvelle fonctionnalité, etc...)"
echo $PROMPT
