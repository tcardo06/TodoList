# Contributing Guide – ToDoList (Symfony 3 Project)

Bienvenue dans le projet **ToDoList** !  
Ce document explique comment contribuer efficacement, en respectant les bonnes pratiques, les normes de qualité et le workflow Git utilisé dans le projet.

---

## 🧩 1. Pré-requis techniques

Avant toute contribution, vous devez disposer de :

- PHP 7.2+
- Composer (installé côté conteneur Docker)
- Docker + Docker Compose
- Symfony 3 (inclus dans le projet)
- MySQL (via Docker)
- PHPUnit 5.7 pour les tests

---

## 🐳 2. Installation du projet (mode développeur)

1. Cloner le dépôt :

```bash
git clone https://github.com/tcardo06/TodoList.git
cd TodoList
```

2. Lancer les services Docker :

```bash
docker compose up -d
```

3. Installer les dépendances :

```bash
docker compose exec app composer install
```

4. Créer la base de données :

```bash
docker compose exec app php bin/console doctrine:database:create
docker compose exec app php bin/console doctrine:schema:update --force
```

5. Charger les fixtures :

```bash
docker compose exec app php bin/console doctrine:fixtures:load
```

Le projet est maintenant accessible via :

```
http://localhost:8000/app.php
```

---

## 🌿 3. Workflow Git à respecter

Nous utilisons un workflow simple mais propre :

### 🔹 Branche principale

- `main` → version stable
- `dev` → version de développement

### 🔹 Création de branches de travail

Chaque fonctionnalité ou correction doit être réalisée via une branche dédiée :

```bash
git checkout dev
git pull
git checkout -b feature/nom-de-ta-feature
```

Exemples :

- `feature/auth-improvements`
- `fix/delete-task-ownership`
- `tests/taskcontroller-tests`

### 🔹 Commits propres et explicites

Règles :

- Un commit = une action identifiable
- Toujours écrire un message clair

Exemples :

```
feat(task): add permissions to restrict deletion
fix(user): prevent admin role editing during creation
test(controller): add functional tests for task deletion
```

### 🔹 Avant un push

Toujours lancer les tests :

```bash
docker compose exec app php phpunit.phar
```

Si vous ajoutez du code, assurez-vous d'améliorer la couverture.

### 🔹 Push & Pull Request

Push vers votre branche :

```bash
git push origin feature/ma-feature
```

Puis créez une Pull Request vers `dev` :

- Description claire
- Explication du problème résolu
- Screenshots si applicable
- Checklist :

```
[ ] Le code respecte PSR-12
[ ] Aucun debug / dump
[ ] Tests existants OK
[ ] Nouveaux tests ajoutés si nécessaire
[ ] Pas de duplication de logique
```

---

## 🧹 4. Règles de style & Qualité du code

Nous appliquons les conventions suivantes :

### ✔️ PSR-12 obligatoire  
Formatage automatique recommandé :

```
docker compose exec app ./vendor/bin/php-cs-fixer fix
```

### ✔️ Controllers épurés  
Pas de logique métier → utiliser des services.

### ✔️ Requêtes Doctrine optimisées  
Éviter :

```php
$repo->findAll(); // sauf cas justifié
```

### ✔️ Code commenté si logique complexe

---

## 🧪 5. Règles concernant les tests

Chaque nouvelle fonctionnalité doit s'accompagner de tests :

- Tests unitaires : entités, services
- Tests fonctionnels : contrôleurs, sécurité
- Taux de couverture minimum global : **70 %**

Lancer les tests :

```bash
docker compose exec app php phpunit.phar
```

Générer le rapport de couverture HTML :

```bash
docker compose exec app phpdbg -qrr phpunit.phar --coverage-html var/coverage
```

---

## 🔐 6. Règles spécifiques à l’authentification & sécurité

- Toute route sensible doit être protégée dans `security.yml`.
- ROLE_ADMIN uniquement sur `/users`.
- Une tâche ne peut être supprimée que par :
  - l’auteur → OUI
  - un admin → uniquement pour tâches anonymes
- Jamais exposer un mot de passe en clair.
- Encodage via :

```php
$encoder->encodePassword($user, $user->getPassword());
```

---

## 🧾 7. Comment ouvrir une Issue

Merci d’utiliser le modèle suivant :

```
### Description du problème

### Étapes pour reproduire

### Comportement attendu

### Logs éventuels

### Suggestion de correction
```

---

## 🤝 8. Comment contribuer de manière efficace

1. Lire et comprendre le fonctionnement actuel  
2. Vérifier qu’il n’existe pas déjà une issue similaire  
3. Discuter si besoin avant d’implémenter  
4. Respecter les bonnes pratiques et les tests  
5. Soumettre une PR propre et documentée  

---

## 📬 9. Contact

Pour toute question, veuillez contacter l’auteur du projet ou ouvrir une issue GitHub.

---

Merci de contribuer au projet ToDoList et de suivre ces règles afin de garantir un code propre, stable et maintenable ✨
