# 📋 Cahier des Charges - Éléments Restants

## 🔍 État Actuel

### ✅ Fonctionnalités Implémentées

1. **Gestion des Garants**
   - Création, modification, suppression, consultation
   - Informations personnelles et professionnelles complètes
   - Pièce d'identité avec dates d'expiration

2. **Gestion des Garanties**
   - CRUD complet
   - Calcul automatique de la valeur réelle (décote)
   - Workflow de statuts avec transitions contrôlées
   - Liaison avec contrats de prêts
   - Liaison avec matricules clients
   - Documentation (fichiers et texte)
   - Affichage des statistiques (montant utilisé, restant, taux d'utilisation)

3. **Gestion des Types de Garanties**
   - CRUD complet
   - Configuration de la pondération et décote

4. **Gestion des Contrats de Prêts**
   - CRUD complet
   - Liaison avec garanties
   - Recherche Flexcube (TODO: intégration API réelle)

5. **Système d'Utilisateurs et Rôles**
   - Gestion des utilisateurs
   - Rôles : IT (Admin), Analyste Risque, Chargé d'Affaires
   - Permissions par rôle

6. **Workflow de Statuts**
   - Transitions définies et validées
   - Changement de statut depuis l'interface

---

## ⚠️ Éléments à Compléter / Améliorer

### 1. 🔐 **Permissions et Validation des Changements de Statut**

**Problème identifié :**
- Ligne 352 de `GarantieController.php` : `// TODO: Ajouter la vérification du rôle juridique`
- Aucune vérification de rôle spécifique pour certains changements de statut sensibles

**À implémenter :**
- Définir quels rôles peuvent effectuer quelles transitions
- Exemple : Seul le rôle "Juridique" peut passer de "contentieux" à "realisation"
- Ajouter des vérifications dans `changerStatut()` du contrôleur

**Fichiers à modifier :**
- `app/Http/Controllers/GarantieController.php` (ligne 352)
- Potentiellement créer un nouveau rôle "Juridique" si nécessaire

---

### 2. 📊 **Historique et Audit Trail**

**Problème :**
- Aucun historique des changements de statut
- Seulement le dernier modificateur est stocké (`modifie_par`, `date_modification`)
- Impossible de voir qui a fait quoi et quand

**À implémenter :**
- Créer une table `garantie_historiques` ou `activity_logs`
- Enregistrer :
  - Ancien statut / Nouveau statut
  - Utilisateur qui a effectué le changement
  - Date/heure
  - Commentaire optionnel
- Afficher l'historique dans la vue `Show.vue`

**Migrations nécessaires :**
```php
// Exemple de structure
- garantie_id (foreign key)
- ancien_statut
- nouveau_statut
- utilisateur_id (foreign key)
- commentaire (nullable)
- created_at
```

---

### 3. 📄 **Affichage des Documentations dans Show.vue**

**Problème :**
- Les documentations sont créées et stockées
- Mais elles ne sont pas affichées dans la vue de détail (`Show.vue`)

**À implémenter :**
- Ajouter une section "Documentation" dans `resources/js/pages/garanties/Show.vue`
- Afficher la liste des documentations avec possibilité de télécharger les fichiers
- Vérifier que `documentations` est bien chargé dans le contrôleur `show()`

**Fichiers à modifier :**
- `resources/js/pages/garanties/Show.vue`
- Vérifier `app/Http/Controllers/GarantieController.php::show()`

---

### 4. 📈 **Rapports et Statistiques**

**Manquant :**
- Dashboard avec vue d'ensemble
- Statistiques globales (nombre de garanties par statut, valeurs totales, etc.)
- Rapports personnalisés
- Export Excel/PDF

**À implémenter :**
- Créer une page Dashboard (`resources/js/pages/Dashboard.vue`)
- Ajouter des graphiques (Chart.js ou similaire)
- Statistiques :
  - Total garanties par statut
  - Valeur totale des garanties
  - Garanties expirant bientôt
  - Taux d'utilisation moyen
- Boutons d'export (Excel, PDF)

---

### 5. 🔔 **Notifications et Alertes**

**Manquant :**
- Notifications pour les garanties expirant bientôt
- Alertes pour les garanties en contentieux
- Notifications pour les changements de statut importants

**À implémenter :**
- Système de notifications (email ou in-app)
- Jobs Laravel pour vérifier les échéances
- Configuration des seuils d'alerte (ex: 30 jours avant expiration)

---

### 6. 🔌 **Intégration API Flexcube**

**Problème identifié :**
- Ligne 86 de `ContratPretController.php` : `// TODO: Intégrer avec l'API Flexcube réelle`
- Actuellement, la recherche Flexcube est simulée

**À implémenter :**
- Intégration réelle avec l'API Flexcube
- Authentification API
- Gestion des erreurs réseau
- Cache des résultats si nécessaire

**Fichiers à modifier :**
- `app/Http/Controllers/ContratPretController.php`

---

### 7. ✍️ **Commentaires et Justification des Changements**

**Manquant :**
- Pas de champ pour justifier un changement de statut
- Pas de commentaires associés aux garanties

**À implémenter :**
- Ajouter un champ "Commentaire" lors du changement de statut
- Optionnellement : système de commentaires général pour les garanties

---

### 8. 🗓️ **Gestion des Échéances**

**Manquant :**
- Pas d'alerte automatique pour les garanties expirant bientôt
- Pas de vue calendrier des échéances

**À implémenter :**
- Vue calendrier des garanties expirant
- Filtre par période d'expiration
- Jobs pour envoyer des rappels

---

### 9. 🔍 **Recherche et Filtres Avancés**

**Partiellement implémenté :**
- Recherche basique par nom, référence, garant
- Filtre par statut et type

**À améliorer :**
- Recherche avancée avec plusieurs critères
- Filtres par dates (création, expiration)
- Filtres par montants (valeur, valeur réelle)
- Sauvegarde de recherches favorites

---

### 10. 📱 **Responsive Design et UX**

**À vérifier :**
- Toutes les vues sont-elles responsive ?
- Les formulaires sont-ils optimisés mobile ?
- Les tableaux sont-ils lisibles sur petit écran ?

---

## 🎯 Priorités Suggérées

### 🔴 Priorité Haute
1. **Affichage des documentations** (Show.vue) - Impact immédiat utilisateur
2. **Historique des changements de statut** - Traçabilité essentielle
3. **Permissions pour changement de statut** - Sécurité et conformité

### 🟡 Priorité Moyenne
4. **Commentaires lors du changement de statut** - Justification nécessaire
5. **Notifications d'expiration** - Prévention importante
6. **Intégration Flexcube réelle** - Si l'API est disponible

### 🟢 Priorité Basse
7. **Dashboard et statistiques** - Amélioration de l'expérience
8. **Export Excel/PDF** - Utile mais pas critique
9. **Recherche avancée** - Amélioration progressive

---

## 📝 Notes Techniques

### TODOs dans le Code
- `app/Http/Controllers/GarantieController.php:352` - Vérification rôle juridique
- `app/Http/Controllers/ContratPretController.php:86` - Intégration API Flexcube

### Tables Potentielles à Créer
- `garantie_historiques` - Historique des changements
- `notifications` - Système de notifications
- `commentaires_garanties` - Commentaires sur les garanties (optionnel)

---

## ✅ Validation Finale

Avant de considérer le projet comme terminé, vérifier :
- [ ] Tous les TODOs sont résolus
- [ ] Toutes les fonctionnalités du cahier des charges initial sont implémentées
- [ ] Les tests utilisateurs sont passés
- [ ] La documentation est à jour
- [ ] Les permissions sont correctement configurées
- [ ] Les données sensibles sont protégées

