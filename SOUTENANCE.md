# Soutenance MediaHub — déroulé de 15 minutes

## Plan chronométré

| Temps | Présentation / démonstration |
|---|---|
| 0:00–2:00 | **Projet** : besoin, public visé, périmètre réellement livré et limites connues. |
| 2:00–4:00 | **Architecture** : Laravel 13, PHP 8.3, MySQL, MVC, modèles/relations Eloquent, Blade et API Sanctum. |
| 4:00–8:00 | **Démonstration web** : inscription/connexion; rôles; liste et détail des catégories/publications; création ou modification autorisée. |
| 8:00–10:00 | **Médias** : image/audio/vidéo publics, document privé servi après contrôle de propriété/admin. |
| 10:00–12:00 | **API REST** : connexion, jeton Bearer, consultation paginée et requête de création protégée; montrer un refus 401/403. |
| 12:00–14:00 | **Qualité et sécurité** : validation, CSRF, Gate/Policy, limite de connexion, logs, tests PHPUnit/MySQL test, migrations et Git. |
| 14:00–15:00 | **Conclusion** : acquis, limites assumées, prochaine étape de déploiement/sauvegarde. |

## Préparation de la démonstration

1. Lancer MySQL/Laragon et vérifier l'URL de l'application avant l'oral.
2. Préparer un compte auteur, un compte admin, une catégorie et des publications de démonstration; utiliser uniquement des fichiers de test.
3. Ne pas afficher `.env`, tokens réels, mots de passe ou informations privées dans la capture/projection.
4. Préparer les commandes de démonstration : `php artisan route:list`, `php artisan migrate:status` et `php artisan test --compact`.
5. Montrer les rôles avec les comptes de démonstration, sans modifier les comptes existants.
6. Décrire honnêtement les fonctionnalités absentes : pas de création de commentaires/évaluations ni de recherche textuelle web à ce jour.

## Questions probables du jury

**Pourquoi Laravel ?**
Il fournit une structure MVC, le routage, la validation, Eloquent, les protections web et les outils de test, ce qui accélère le développement tout en gardant un code organisé.

**Pourquoi MySQL plutôt que SQLite ?**
Le projet utilise MySQL avec Laragon et HeidiSQL. La configuration et les tests ont été adaptés à ce choix; SQLite n'est pas le moteur de l'application.

**À quoi sert Eloquent ?**
C'est l'ORM Laravel : il représente les tables avec des modèles et permet de déclarer les relations entre utilisateurs, catégories et publications.

**Comment un utilisateur est-il autorisé à modifier une publication ?**
La Policy vérifie que l'utilisateur est propriétaire ou admin. Les routes API d'écriture exigent en plus un jeton Sanctum.

**Comment les mots de passe sont-ils protégés ?**
Le modèle utilise le cast Laravel `hashed`, et la configuration active bcrypt; le mot de passe brut n'est pas conservé.

**Comment les fichiers privés sont-ils protégés ?**
Les PDF sont stockés sur le disque privé, pas sous `public`; la route de téléchargement vérifie le propriétaire ou le rôle admin avant de servir le fichier.

**À quoi sert le lien `public/storage` ?**
Il relie le dossier public de médias au stockage Laravel. Les fichiers privés n'utilisent pas ce lien.

**Comment les erreurs sont-elles traitées ?**
Les visiteurs voient une réponse générique lorsque le debug est désactivé; les détails sont enregistrés dans `storage/logs` selon le canal choisi.

**Comment limiter les erreurs de déploiement ?**
Vérifier l'environnement, sauvegarder la base et les médias, contrôler les migrations en attente, déployer dans un répertoire séparé, puis activer le cache après la configuration.

**Comment éviter la perte de données pendant les tests ?**
PHPUnit cible `mediahub_test`; le `TestCase` de base échoue si une autre base MySQL est configurée. La base MediaHub normale n'est pas la base de test.

**Que reste-t-il à améliorer ?**
Le tableau de bord admin est un placeholder; commentaires en écriture, évaluations et recherche web n'ont pas encore été implémentés. Un déploiement HTTPS réel et un test de restauration doivent également être organisés.
