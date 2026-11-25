# MediaHub

MediaHub est une application Laravel de gestion et de consultation de contenus multimédias.

## Technologies et fonctionnalités

- Laravel 13, PHP 8.3 et MySQL (développement local avec Laragon).
- Authentification avec rôles auteur/admin et autorisations par Gate/Policy.
- CRUD des catégories et publications, validation et pagination.
- Téléversement d'images, audio/vidéo publics et documents privés.
- API REST JSON protégée pour les opérations d'écriture avec Laravel Sanctum.
- Tests PHPUnit exécutés sur une base MySQL distincte `mediahub_test`.

## Installation et documentation

- [Installation Windows/Laragon](INSTALLATION.md)
- [Déploiement et sauvegarde/restauration](DEPLOIEMENT.md)
- [Recette fonctionnelle et limites](RECETTE_FONCTIONNELLE.md)
- [Plan de soutenance](SOUTENANCE.md)
- [Documentation de l'API](API.md)

Ne remplacez pas un `.env` existant et ne lancez jamais de commande de réinitialisation de base sur des données conservées.