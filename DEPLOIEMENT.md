# Déploiement de MediaHub

Ce guide prépare un déploiement réel ou simulé. Aucun déploiement Internet n'est effectué par ce document ou par l'audit. Le `.env` local reste un environnement de développement MySQL/Laragon.

## Prérequis serveur

- PHP 8.3, Composer 2 et les extensions `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `gd`, `xml`, `curl` et `zip`.
- MySQL compatible, base et utilisateur dédiés à la production, avec droits minimaux nécessaires.
- Apache avec `mod_rewrite` ou Nginx avec PHP-FPM.
- HTTPS avec certificat valide.
- Espace sauvegardé pour MySQL, médias publics et documents privés.

Confirmer les extensions sur le serveur avec `php -m`. Ne jamais copier les identifiants locaux de Laragon dans la configuration de production.

## Variables de production

Utiliser `.env.production.example` comme liste de paramètres, pas comme fichier de secrets. Sur le serveur seulement, créer/configurer son propre `.env` avec les secrets fournis par un gestionnaire de secrets :

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://votre-domaine`.
- `DB_CONNECTION=mysql` et paramètres MySQL propres à la production.
- `APP_KEY` créée une seule fois pour cette installation et conservée de façon sécurisée.
- `LOG_CHANNEL=daily`, `LOG_LEVEL=warning`.
- `SESSION_SECURE_COOKIE=true` lorsque le site est servi uniquement en HTTPS.

Ne copiez pas ce fichier sur le `.env` local et ne versionnez jamais un `.env` rempli. Ne régénérez pas une `APP_KEY` existante : cela peut rendre les données chiffrées illisibles.

## Procédure de déploiement

1. Exporter une sauvegarde vérifiée de la base MySQL et copier les médias publics et privés (voir « Sauvegarde et restauration »).
2. Transférer le code dans un nouveau répertoire de version ou cloner le dépôt sur le serveur. Ne pas faire `composer install --no-dev` dans le Laragon de développement.
3. Dans le dossier de l'application sur le serveur :

```sh
composer install --no-dev --optimize-autoloader --no-interaction
```

4. Configurer le `.env` de ce serveur, les droits MySQL et les répertoires `storage` et `bootstrap/cache`.
5. Vérifier la connexion et les changements de schéma :

```sh
php artisan migrate:status
```

Examiner chaque migration en attente et la sauvegarde avant de poursuivre. Appliquer uniquement les migrations attendues :

```sh
php artisan migrate --force
```

Ne jamais utiliser `migrate:fresh`, `migrate:refresh` ou `db:wipe` sur une base contenant des données. Ne pas lancer automatiquement les seeders de développement en production.
6. Vérifier le lien `public/storage`. Le créer uniquement s'il est absent et que la cible `storage/app/public` est correcte :

```sh
php artisan storage:link
```

7. Préparer les caches dans le répertoire de production :

```sh
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

Après un changement du code ou de la configuration, les caches concernés doivent être reconstruits. Attention : dans Laravel 13, `php artisan optimize:clear` efface également le cache applicatif via `cache:clear`. Ici le cache par défaut est stocké dans MySQL; ne lancez pas cette commande à l'aveugle si cette table contient des données/cache partagé. Pour supprimer les caches de déploiement sans vider le cache applicatif :

```sh
php artisan optimize:clear --except=cache
```

Puis reconstruire les caches nécessaires. La commande `optimize:clear` ne doit pas servir à « réparer » une erreur en production sans comprendre ce qu'elle efface.
8. Vérifier les droits minimaux d'écriture du compte PHP sur `storage` et `bootstrap/cache`. Ne rendez pas tout le dépôt accessible en écriture au serveur web.
9. Effectuer la recette post-déploiement : pages publiques, connexion, accès admin, formulaires, fichiers publics/privés, API, logs et tâches planifiées.

## Serveur web et HTTPS

La racine publique du VirtualHost doit pointer vers `<répertoire MediaHub>/public`. Ne configurez jamais la racine web sur le dossier du dépôt : cela pourrait rendre `.env`, `vendor` ou le code source accessibles.

Exemple Apache à adapter au serveur :

```apache
DocumentRoot /var/www/mediahub/current/public
<Directory /var/www/mediahub/current/public>
    AllowOverride All
    Require all granted
</Directory>
```

Activer `mod_rewrite` et la réécriture HTTPS côté VirtualHost/proxy. Avec Nginx, utiliser `root .../public`, envoyer les requêtes vers `public/index.php` et configurer PHP-FPM; refuser l'accès aux fichiers cachés, notamment `.env`. Installer et renouveler un certificat TLS (par exemple Let's Encrypt) et forcer HTTP vers HTTPS.

## Stockage des médias et logs

- `storage/app/public` contient les images et fichiers audio/vidéo exposés volontairement.
- `storage/app/private` contient les PDF privés; ne pas les placer dans `public`.
- `public/storage` doit cibler `storage/app/public`.
- Les erreurs sont journalisées sous `storage/logs` par le canal Laravel configuré. Garder `APP_DEBUG=false` en production et ne jamais afficher les traces aux visiteurs.

## Sauvegarde et restauration

### Base MySQL avec HeidiSQL

1. Sélectionner la base de production, puis utiliser l'export SQL.
2. Inclure structure et données; enregistrer un fichier horodaté hors de la racine web.
3. Chiffrer et limiter l'accès à la sauvegarde. Une sauvegarde qui n'a jamais été restaurée dans un environnement isolé n'est pas vérifiée.

### Médias et configuration

Copier ensemble `storage/app/public` et `storage/app/private`, en préservant les chemins et permissions. Sauvegarder la configuration de restauration sans publier de secrets : conserver les valeurs sensibles dans un coffre sécurisé séparé. Garder la même `APP_KEY` pour l'installation restaurée.

### Restaurer et vérifier sans écraser la production

1. Créer une base de test/restauration distincte; ne pas importer par-dessus la base active.
2. Avec HeidiSQL, importer le dump dans cette base isolée.
3. Restaurer les deux dossiers de médias vers leurs chemins correspondants.
4. Configurer une copie isolée de l'application avec les identifiants de test, puis vérifier `php artisan migrate:status`, l'ouverture de pages, le chargement d'une image et le téléchargement autorisé d'un PDF.
5. Documenter la date et le résultat. Ne jamais tester une restauration sur la base MediaHub de développement ou de production.
