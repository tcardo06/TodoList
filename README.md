# ToDoList - Projet Symfony 3 (OpenClassrooms)

Ce projet est une application de gestion de tâches réalisée avec **Symfony 3**, dans le cadre du parcours Développeur d’Application PHP/Symfony d’OpenClassrooms.

Il inclut :
- un système d’authentification sécurisé ;
- une gestion des rôles (utilisateur et administrateur) ;
- une gestion de tâches (CRUD + règles d’accès strictes) ;
- une interface d’administration pour gérer les utilisateurs ;
- des tests automatisés (unitaires + fonctionnels) ;
- un rapport de couverture de code supérieur à 70%.

---

## 🚀 Installation du projet

### 1. Cloner le repository
```bash
git clone https://github.com/tcardo06/TodoList.git
cd todolist
```

### 2. Démarrer l’environnement Docker
```bash
docker compose up -d
```

### 3. Installer les dépendances Composer
```bash
docker compose exec app composer install
```

### 4. Mettre à jour la base de données

#### Créer le schéma :
```bash
docker compose exec app php bin/console doctrine:schema:update --force
```

#### Charger les fixtures :
```bash
docker compose exec app php bin/console doctrine:fixtures:load
```

Les fixtures installent :
- un utilisateur admin (`admin / todolist`)
- un utilisateur simple (`user / todolist`)
- un utilisateur « anonyme »
- une tâche d’exemple

---

## ▶️ Lancer l’application

Une fois Docker démarré, rendez-vous sur :

👉 **http://localhost:8000**

---

## 🔑 Authentification

Identifiants par défaut :

| Rôle  | Identifiant | Mot de passe |
|-------|-------------|---------------|
| Admin | admin       | todolist      |
| User  | user        | todolist      |

---

## 📌 Fonctionnalités principales

### ✔️ Gestion des tâches
- Création, édition, suppression
- Chaque tâche est automatiquement liée à l’utilisateur connecté
- Règles d’autorisation strictes :
  - un utilisateur peut supprimer **uniquement ses tâches**
  - **seuls les admins** peuvent supprimer les tâches de l’utilisateur anonyme

### ✔️ Gestion des utilisateurs (admin uniquement)
- Accès réservé aux ROLE_ADMIN
- Liste des utilisateurs
- Modification du rôle
- Création d’utilisateur

---

## 🧪 Tests automatisés

### Lancer les tests :
```bash
docker compose exec app php phpunit.phar
```

### Lancer les tests avec couverture
```bash
docker compose exec app phpdbg -qrr phpunit.phar --coverage-html coverage/
```

Les fichiers de couverture seront disponibles dans :

```
/coverage/index.html
```

Le taux final doit être **≥ 70%**.

---

## 📁 Structure du projet

```
todolist/
│
├── app/                 → configuration Symfony
├── src/AppBundle        → code source MVC
├── tests/               → tests PHPUnit
├── coverage/            → rapport généré
├── diagrams/            → diagrammes à fournir
├── docs/                → documentation PDF
└── README.md
```

## 📄 Licence

Projet éducatif réalisé dans le cadre du parcours OpenClassrooms.  
Libre de réutilisation à des fins pédagogiques.
