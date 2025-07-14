# EduCare - Plateforme de Lutte contre la Précarité Étudiante

## 📋 Description du Projet

EduCare est une plateforme web dédiée à la lutte contre la précarité étudiante. Elle met en relation les étudiants en difficulté avec des organismes d'aide (associations, institutions publiques comme le CROUS, etc.). La plateforme permet aux petites associations de gagner en visibilité et d'atteindre efficacement leur public cible.

Ce repository contient la partie **backend** du projet, développée avec le framework Symfony 6.3.
[Voir le front-end du projet](https://github.com/Slyannn/EduCare_frontend.git)

## 📦 Installation & Configuration

### Prérequis
```bash
# Versions requises
PHP >= 8.1
Composer
Base de données (MySQL/PostgreSQL/SQLite)
```

### Installation
```bash
# Cloner le repository
git clone [url-du-repo]
cd afreesoft-backend

# Installer les dépendances
composer install

# Configuration environnement
cp .env .env.local
# Éditer .env.local avec vos paramètres

# Base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# Démarrer le serveur
symfony server:start
```

### Variables d'Environnement
```env
# Base de données
DATABASE_URL="mysql://user:password@127.0.0.1:3306/educare"

# JWT Secret
JWT_SECRET="your-secret-key"

# Email (Mailer)
MAILER_DSN="smtp://localhost:1025"

# CORS
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
```

## 🎯 Utilisation

### Workflow Typique

#### Pour un Étudiant :
1. **Inscription** → Vérification email → Connexion
2. **Profil** → Sélection des besoins (aide alimentaire, logement, etc.)
3. **Recherche** → Visualisation des organismes correspondants
4. **Contact** → Envoi de messages aux organismes intéressants
5. **Évaluation** → Avis après utilisation des services

#### Pour un Organisme :
1. **Inscription** → Upload de certification → Attente validation
2. **Validation admin** → Activation du compte → Notification email
3. **Profil** → Configuration des services proposés
4. **Réception** → Messages des étudiants intéressés
5. **Gestion** → Mise à jour des informations

#### Pour un Administrateur :
1. **Connexion** → Interface d'administration
2. **Validation** → Examen des demandes d'organismes
3. **Gestion** → CRUD des besoins et services
4. **Monitoring** → Suivi de l'activité plateforme


## 🏗️ Architecture du Projet

### Entités Principales

#### 👤 **User** - Système d'authentification unifié
- **Rôles disponibles** : `ROLE_STUDENT`, `ROLE_ORGANISM`, `ROLE_ADMIN`
- **Authentification** : JWT (JSON Web Token)
- **Vérification** : Système d'activation par email
- **Relations** : OneToOne avec Student et Organism

#### 🎓 **Student** - Profil étudiant
- **Informations** : Prénom, nom, université
- **Localisation** : Adresse complète
- **Besoins** : Liste des services recherchés (Many-to-Many avec Need)
- **Avis** : Peut laisser des reviews sur les organismes
- **Fonctionnalités** :
  - Inscription et profil personnalisé
  - Sélection de besoins spécifiques
  - Recherche d'organismes par besoins
  - Système de messaging avec les organismes

#### 🏢 **OrganismAdmin** - Profil d'organisme
- **Informations** : Nom, description, email, téléphone, site web
- **Visuel** : Logo de l'organisation
- **Services** : Liste des besoins couverts (Many-to-Many avec Need)
- **Statut** : Validation par les administrateurs
- **Fonctionnalités** :
  - Inscription avec certification (document PDF)
  - Gestion des services proposés
  - Réception des messages étudiants

#### 🏥 **Organism** - Profil de certification
- **Certification** : Document PDF requis pour validation
- **Statut** : Activé/Désactivé par les admins
- **Relations** : OneToOne avec OrganismAdmin et User

#### 📋 **Need** - Catégories de besoins
- **Services disponibles** : Aide alimentaire, logement, transport, etc.
- **Relations** : Many-to-Many avec Students et OrganismAdmins
- **Gestion** : CRUD via interface admin

#### ⭐ **Review** - Système d'évaluation
- **Contenu** : Titre, note, commentaire
- **Relations** : Étudiant (auteur) → Organisme (cible)
- **Utilité** : Aide à la sélection des organismes

#### 📬 **Message** - Communication
- **Contenu** : Expéditeur, destinataire, sujet, message
- **Envoi automatique** : Email + copie à l'expéditeur
- **Traçabilité** : Stockage en base de données

#### 📍 **Address** - Géolocalisation
- **Informations** : Rue, ville, code postal, pays
- **Relations** : OneToMany avec Students et OrganismAdmins

#### 👨‍💼 **Admin** - Administration de la plateforme
- **Gestion** : Validation des organismes, gestion des besoins
- **Accès** : Interface d'administration sécurisée

## 🚀 Fonctionnalités Principales

### 🔐 **Authentification & Sécurité**

#### Inscription/Connexion
- **Étudiants** : Inscription avec informations universitaires
- **Organismes** : Inscription avec certification obligatoire
- **JWT** : Tokens sécurisés pour l'authentification API
- **Validation email** : Activation obligatoire des comptes

#### Sécurité
- **CORS** : Configuration pour les requêtes cross-origin
- **CSRF** : Protection contre les attaques CSRF
- **Hashage** : Mots de passe sécurisés avec Symfony Password Hasher

### 📱 **API REST Complète**

#### Endpoints Étudiants (`/api/student`)
```
POST /signup          - Inscription étudiant
PUT  /update/{id}     - Mise à jour profil
GET  /all             - Liste des étudiants
POST /{id}/need       - Ajout d'un besoin
DELETE /{id}/need/{need} - Suppression d'un besoin
GET  /{id}/organisms  - Organismes correspondant aux besoins
```

#### Endpoints Organismes (`/api/organism`)
```
POST /signup          - Inscription organisme
PUT  /update/{id}     - Mise à jour profil
GET  /all             - Liste des organismes
POST /sendMessage     - Envoi de message
```

#### Endpoints Authentification (`/api/auth`)
```
POST /login              - Connexion utilisateur
GET  /currentUser/{token} - Informations utilisateur connecté
```

#### Endpoints Utilitaires
```
GET  /api/needs/         - Liste des besoins disponibles
POST /api/reviews/add    - Ajout d'un avis
GET  /api/reviews/       - Liste des avis
GET  /api/verif/{token}  - Vérification email
POST /api/resend_verif/{email} - Renvoi email vérification
```

### 👨‍💼 **Interface d'Administration**

#### Gestion des Organismes (`/admin/organism`)
- **Validation** : Approbation/rejet des candidatures
- **Statut** : Activation/désactivation des comptes
- **Certificats** : Téléchargement et vérification des documents
- **Notification** : Emails automatiques de validation

#### Gestion des Besoins (`/admin/need`)
- **CRUD complet** : Création, lecture, mise à jour, suppression
- **Catégorisation** : Organisation des services disponibles

#### Tableau de Bord (`/admin/home`)
- **Vue d'ensemble** : Statistiques et monitoring
- **Accès rapide** : Navigation vers les sections principales

### 📧 **Système de Messaging**

#### Envoi d'Emails
- **Templates Twig** : Emails HTML personnalisés
- **Types d'emails** :
  - Confirmation d'inscription
  - Validation d'organisme
  - Contact entre utilisateurs
  - Copie des messages envoyés

#### Communication Inter-Utilisateurs
- **Étudiant → Organisme** : Contact direct via la plateforme
- **Copie automatique** : Confirmation d'envoi
- **Stockage** : Historique des messages en base

### 📁 **Gestion de Fichiers**

#### Upload Sécurisé
- **Logos** : Images des organismes (JPEG, PNG)
- **Certificats** : Documents PDF de validation
- **Stockage** : `/public/uploads/` organisé par type
- **Sécurité** : Validation des types MIME

#### Service UploadFile
- **Noms uniques** : Évite les conflits de fichiers
- **Slugification** : Noms de fichiers sécurisés
- **Organisation** : Dossiers par catégorie (logo/, certificate/)

### 🔍 **Recherche et Matching**

#### Algorithme de Correspondance
- **Besoins étudiants** : Sélection de services recherchés
- **Services organismes** : Déclaration des aides proposées
- **Matching automatique** : Organismes pertinents selon les besoins
- **Géolocalisation** : Prise en compte de l'adresse

#### Système d'Avis
- **Notation** : Évaluation des organismes
- **Commentaires** : Retours d'expérience détaillés
- **Aide à la décision** : Transparence pour les futurs utilisateurs

## 🛠️ Technologies Utilisées

### Framework & Version
- **Symfony 6.3** - Framework PHP moderne
- **PHP 8.1+** - Version minimale requise
- **Doctrine ORM** - Gestionnaire de base de données

### Bundles Symfony
- **Security Bundle** - Authentification et autorisation
- **Mailer Bundle** - Envoi d'emails
- **Twig Bundle** - Moteur de templates
- **Form Bundle** - Gestion des formulaires
- **Validator Bundle** - Validation des données
- **CORS Bundle** - Support des requêtes cross-origin

### Services Personnalisés
- **JwtService** - Gestion des tokens JWT
- **SendMailService** - Service d'envoi d'emails
- **UploadFile** - Gestion des uploads de fichiers

### Base de Données
- **Doctrine Migrations** - Versioning de la base
- **Fixtures** - Données de test (admin par défaut)

## 🌟 Points Forts du Système

### 🔒 **Sécurité Renforcée**
- Authentification JWT stateless
- Validation des uploads de fichiers
- Protection CSRF sur l'admin
- Hashage sécurisé des mots de passe

### ⚡ **Performance & Scalabilité**
- API REST optimisée
- Serialisation contrôlée (évite les références circulaires)
- CORS configuré pour le développement multi-domaines
- Structure modulaire pour l'extension

### 🎨 **Flexibilité**
- Système de rôles extensible
- Besoins configurables dynamiquement
- Templates d'emails personnalisables
- Architecture MVC respectée

### 📊 **Traçabilité**
- Logs des connexions
- Historique des messages
- Timestamping automatique
- Workflow de validation transparent

## 🚀 Évolutions Futures Possibles

### Fonctionnalités Avancées
- **Géolocalisation** : Recherche par proximité géographique
- **Notifications** : Système de push notifications
- **Chat temps réel** : WebSocket pour messaging instantané
- **Mobile API** : Optimisations pour applications mobiles
- **Analytics** : Tableaux de bord statistiques avancés

### Intégrations
- **Réseaux sociaux** : Connexion via OAuth
- **Services externes** : APIs gouvernementales
- **Paiements** : Système de donations
- **Cartographie** : Intégration Google Maps/OpenStreetMap

## 📄 Licence

Ce projet est sous licence propriétaire. Tous droits réservés.

## 👥 Contact & Support

Pour toute question concernant EduCare, veuillez contacter l'équipe de développement.

---

*EduCare - Ensemble contre la précarité étudiante* 🎓💙 
