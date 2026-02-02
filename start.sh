#!/bin/bash

# Script de démarrage rapide pour l'application de gestion des garanties
# Usage: ./start.sh

set -e

echo "🚀 Démarrage de l'application de Gestion des Garanties..."
echo ""

# Couleurs pour les messages
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Vérifier si .env existe
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  Fichier .env non trouvé. Copie de .env.example...${NC}"
    cp .env.example .env
    php artisan key:generate
    echo -e "${GREEN}✅ Fichier .env créé. Veuillez configurer la base de données dans .env${NC}"
    echo ""
    echo "Modifiez les variables suivantes dans .env :"
    echo "  DB_DATABASE=app_cof_garantie"
    echo "  DB_USERNAME=votre_utilisateur"
    echo "  DB_PASSWORD=votre_mot_de_passe"
    echo ""
    read -p "Appuyez sur Entrée une fois la configuration terminée..."
fi

# Vérifier les dépendances Composer
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}📦 Installation des dépendances Composer...${NC}"
    composer install
fi

# Vérifier les dépendances npm
if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}📦 Installation des dépendances npm...${NC}"
    npm install
fi

# Vérifier si les migrations ont été exécutées
echo -e "${YELLOW}🗄️  Vérification de la base de données...${NC}"

# Tenter d'exécuter les migrations
if php artisan migrate --force 2>/dev/null; then
    echo -e "${GREEN}✅ Migrations exécutées avec succès${NC}"
else
    echo -e "${YELLOW}⚠️  Erreur lors de l'exécution des migrations${NC}"
    echo "Vérifiez votre configuration de base de données dans .env"
    exit 1
fi

# Vérifier si le seeder a été exécuté
echo -e "${YELLOW}🌱 Chargement des données initiales...${NC}"
php artisan db:seed --class=TypeGarantieSeeder --force || echo -e "${YELLOW}⚠️  Le seeder a peut-être déjà été exécuté${NC}"

echo ""
echo -e "${GREEN}✅ Configuration terminée !${NC}"
echo ""
echo "Pour démarrer l'application, choisissez une option :"
echo ""
echo "1. Mode développement (recommandé) :"
echo "   npm run dev:all"
echo ""
echo "2. Mode production :"
echo "   npm run build"
echo "   php artisan serve"
echo ""
echo "L'application sera accessible sur : http://localhost:8000"
echo ""



