# Installation locale de MediaHub (Windows / Laragon)

## Prérequis

- Windows 10/11 et Laragon.
- PHP 8.3 avec `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `gd`, `xml`, `curl` et `zip`.
- Composer 2.
- MySQL démarré depuis Laragon.
- Node.js et npm uniquement si les assets doivent être reconstruits.
- Git pour cloner le dépôt.

Versions vérifiées dans l'environnement de travail : PHP 8.3.36, Laravel 13.44.1, Composer 2.10.3 et MySQL. Les tests s'exécutent séparément sur la base `mediahub_test`.

## Récupérer le projet

Dans le terminal Laragon/PowerShell :

```powershell
cd D:\laragon\www
git clone https://github.com/saidisouhail55-droid/mediahub.git
cd mediahub
composer install
```

Si le projet est déjà présent, ouvrir son dossier existant et ne pas recloner par-dessus.

## Configurer MySQL et l'environnement

1. Démarrer MySQL dans Laragon.
2. Dans HeidiSQL, vérifier la base MediaHub existante. Pour une nouvelle installation uniquement, créer une base dédiée avec le jeu de caractères `utf8mb4`. Ne pas supprimer ni recréer une base existante.
3. Vérifier si `.env` existe avant toute copie :

```powershell
Test-Path .env
```

S'il n'existe pas, créer le fichier à partir de `.env.example`. S'il existe déjà, ne pas l'écraser : sauvegarder le fichier puis ne modifier que les paramètres nécessaires. Garder `DB_CONNECTION=mysql` et configurer `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` et `DB_PASSWORD` pour votre instance Laragon. Ne partagez jamais le contenu de `.env`.
4. Générer `APP_KEY` seulement pour une première installation lorsque la clé est vide :

```powershell
php artisan key:generate
```

Ne jamais régénérer la clé d'une installation qui contient déjà des données chiffrées ou des sessions actives.

## Installer le schéma et démarrer

Vérifier d'abord la connexion et les migrations :

```powershell
php artisan migrate:status
```

Sur une base neuve réservée à cette installation, appliquer les migrations en attente :

```powershell
php artisan migrate
```

Ne jamais utiliser `migrate:fresh`, `migrate:refresh` ou `db:wipe` sur MediaHub. Sur la base de développement existante, faire une sauvegarde avant toute migration en attente. L'installation actuelle a déjà toutes ses migrations appliquées.

Les catégories d'exemple et le compte administrateur sont facultatifs. `AdminSeeder` exige `ADMIN_NAME`, `ADMIN_EMAIL` et `ADMIN_PASSWORD` configurés localement. Ne lancez pas de seeders sur une base remplie sans avoir vérifié leur effet et sauvegardé la base.

Vérifier `public/storage`. S'il est absent et que `storage/app/public` est le bon dossier cible :

```powershell
php artisan storage:link
```

Les images et médias publics sont dans `storage/app/public`; les documents privés sont dans `storage/app/private` et se téléchargent uniquement par une route autorisée.

Démarrer l'application :

```powershell
php artisan serve
```

Ouvrir `http://127.0.0.1:8000`. Laragon peut aussi exposer le projet via son VirtualHost. Dans tous les cas, la racine web doit être le dossier `public`, jamais la racine du dépôt.

## Assets et tests

Si les dépendances JavaScript sont installées et qu'un build est nécessaire :

```powershell
npm install
npm run build
```

La suite de tests est protégée par `tests/TestCase.php` et `phpunit.xml` : elle refuse de s'exécuter si la base MySQL n'est pas `mediahub_test`.

```powershell
php artisan test
```

Ne changez pas cette garde pour pointer les tests vers la base MediaHub de développement.
