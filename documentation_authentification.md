# Documentation technique – Authentification de l'application ToDoList

Cette documentation décrit le fonctionnement de l'authentification de l'application Symfony **ToDoList**. Elle vise un développeur junior qui découvre le projet et doit comprendre où intervenir pour maintenir ou faire évoluer la sécurité.

## 1. Vue d'ensemble

- **Version du framework** : application Symfony 3 (structure `app/`, annotations de routes).
- **Objectif sécurité** : authentifier des utilisateurs via un formulaire, protéger les pages de l'application aux utilisateurs connectés et gérer la déconnexion.
- **Stockage** : Doctrine ORM avec l'entité `AppBundle\Entity\User` persistée en base de données (table `user`).

## 2. Fichiers clés à connaître

| Rôle | Fichier | Contenu principal |
| --- | --- | --- |
| Configuration de la sécurité | `app/config/security.yml` | Provider Doctrine, firewall `main`, règles d'accès `access_control`, configuration du formulaire de login et du logout. |
| Contrôleur d'authentification | `src/AppBundle/Controller/SecurityController.php` | Action `loginAction` qui affiche le formulaire, routes déclarées pour `login_check` et `logout` (gérées automatiquement par le firewall). |
| Template du formulaire | `app/Resources/views/security/login.html.twig` | Formulaire HTML qui poste vers `login_check` et affiche les erreurs. |
| Entité utilisateur | `src/AppBundle/Entity/User.php` | Implémentation de `UserInterface`, stockage du mot de passe, du username et de l'email, rôle par défaut `ROLE_USER`. |
| Encodage du mot de passe | `src/AppBundle/Controller/UserController.php` | Utilisation du service `security.password_encoder` lors de la création/modification d'un utilisateur. |
| Formulaire de gestion des utilisateurs | `src/AppBundle/Form/UserType.php` | Champs `username`, `password` (répété) et `email`. |
| Layout / liens de navigation | `app/Resources/views/base.html.twig` | Affiche les boutons *Se connecter* / *Se déconnecter* selon `app.user`. |

## 3. Stockage des utilisateurs et des rôles

- **Entité** : `AppBundle\Entity\User` possède les propriétés `username`, `password`, `email` et un identifiant auto-généré. Le mot de passe est stocké encodé.
- **Rôle attribué** : la méthode `getRoles()` renvoie toujours `['ROLE_USER']`. Il n'existe actuellement ni rôle administrateur ni notion d'autorisations fines par utilisateur.
- **Encodage** : la configuration `security.encoders` impose `bcrypt`. Lorsqu'un utilisateur est créé ou modifié, `UserController` encode le mot de passe via `security.password_encoder` avant de persister l'entité.

## 4. Parcours de connexion (login)

1. **Route et action** : la route `/login` (`name: login`) est définie par annotation dans `SecurityController::loginAction`.
2. **Affichage du formulaire** : `loginAction` récupère via `security.authentication_utils` le dernier nom d'utilisateur et une éventuelle erreur d'authentification, puis rend `security/login.html.twig` avec ces valeurs.
3. **Soumission** : le formulaire envoie une requête POST vers la route `login_check`. Aucun code applicatif n'est exécuté : le firewall `main` intercepte cette route, vérifie les identifiants via le provider Doctrine (recherche par `username`), puis :
   - en cas d'échec : redirige vers `/login` avec un message d'erreur consommé par le template ;
   - en cas de succès : redirige vers `/` (car `always_use_default_target_path` est activé, `default_target_path: /`).
4. **Session utilisateur** : après succès, l'utilisateur est disponible via `app.user` dans les templates et via le token de sécurité côté contrôleur.

## 5. Parcours de déconnexion (logout)

- La route `/logout` (`name: logout`) est déclarée dans `SecurityController` mais ne contient pas de logique : le firewall `main` la prend en charge et détruit le token + la session. 
- Le bouton *Se déconnecter* n'apparaît dans le layout que lorsque `app.user` est défini.

## 6. Règles d'accès et autorisations

- **Définition** : `access_control` dans `app/config/security.yml` autorise l'accès anonyme à `/login` et à toutes les URL commençant par `/users`. Toute autre URL (`^/`) nécessite le rôle `ROLE_USER`.
- **Conséquence** : 
  - Les pages de gestion d'utilisateurs sont actuellement accessibles sans authentification (configuration potentiellement à sécuriser).
  - Comme chaque utilisateur possède uniquement `ROLE_USER`, l'application ne distingue pas les rôles (pas de pages réservées aux admins).
- **Protection des tâches** : les routes des tâches (`/tasks/...`) sont couvertes par la règle `ROLE_USER`. Il n'y a pas d'association entre une tâche et son créateur ; aucun contrôle d'appartenance n'est effectué dans `TaskController`.

## 7. Étendre ou modifier l'authentification sans casser l'existant

### Ajouter des rôles (ex. administrateur)
1. Ajouter une propriété `roles` (array) dans `User`, avec une colonne Doctrine type `json_array` ou `simple_array`, et modifier `getRoles()`/`setRoles()` pour retourner cette liste.
2. Adapter les formulaires (`UserType`) et l'interface d'administration pour définir ces rôles.
3. Mettre à jour `access_control` pour restreindre certaines routes (ex. `/users` réservé à `ROLE_ADMIN`).

### Sécuriser la gestion des utilisateurs
- Modifier `access_control` pour exiger `ROLE_ADMIN` sur `/users` et ses sous-routes.
- Vérifier que les contrôleurs utilisent `@Security` ou `$this->denyAccessUnlessGranted()` pour renforcer les contrôles.

### Restreindre les tâches à leur auteur
1. Ajouter une relation `ManyToOne` vers `User` dans `Task` (ex. propriété `author`).
2. Lors de la création d'une tâche, définir `author` à `getUser()` dans `TaskController`.
3. Dans les actions `edit`, `toggle`, `delete`, utiliser `denyAccessUnlessGranted('OWNER', $task)` ou vérifier `task->getAuthor() === $this->getUser()`.
4. Mettre en place un `TaskVoter` si nécessaire pour centraliser ces règles.

### Modifier le comportement de redirection après login
- Ajuster `default_target_path` dans `security.yml` (ou utiliser `target_path` dans la session) pour rediriger vers une page spécifique.

### Changer l'algorithme d'encodage
- Mettre à jour la section `encoders` dans `security.yml` (ex. `auto` pour les algorithmes recommandés par Symfony) et vérifier que les mots de passe existants restent compatibles.

### Points de vigilance
- Ne placez pas de logique métier dans `login_check` ou `logout` : ces routes doivent rester gérées par le firewall.
- Toute modification de l'entité `User` nécessite une migration Doctrine (ou mise à jour de schéma) et éventuellement l'adaptation des formulaires/contrôleurs.
- Gardez la cohérence entre les rôles retournés par `getRoles()` et les règles `access_control` : un rôle non retourné rendrait une route inaccessible.

## 8. Références rapides

- **Formulaire de login** : `app/Resources/views/security/login.html.twig`
- **Contrôleur de login/logout** : `src/AppBundle/Controller/SecurityController.php`
- **Configuration du firewall** : `app/config/security.yml`
- **Gestion des utilisateurs** : `src/AppBundle/Controller/UserController.php` + `src/AppBundle/Form/UserType.php`
- **Entité utilisateur** : `src/AppBundle/Entity/User.php`
- **Liens de navigation** : `app/Resources/views/base.html.twig`