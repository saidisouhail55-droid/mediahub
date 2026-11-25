# Recette fonctionnelle de MediaHub

## Périmètre réellement présent

| Fonctionnalité | Présence et vérification |
|---|---|
| Inscription, connexion, déconnexion | Présentes; tests d'authentification réussis. Mot de passe haché par Laravel. |
| Rôles auteur/admin et politique de publication | Présents; propriétaire et admin peuvent gérer une publication, les autres auteurs sont refusés. |
| Tableau de bord admin | Route présente; désormais réservée à un utilisateur authentifié de rôle admin. La page est actuellement un texte de démonstration, pas un vrai tableau de bord. |
| Catégories | CRUD présent; actions réservées aux admins, suppression bloquée si la catégorie est utilisée. Tests réussis. |
| Publications | CRUD web/API présent; validation, auteur contrôlé côté serveur, pagination et suppression logique. Tests réussis. |
| Médias | Image/minature, audio/vidéo publics et PDF privés présents; tests de validation, stockage et autorisation réussis. |
| Commentaires | Affichage et relations présents; pas de formulaire ni de route permettant d'en créer/modifier/supprimer. |
| Évaluations | Aucun modèle, route ou écran d'évaluation identifié. |
| Recherche web | Pas de recherche textuelle identifiée. La liste API accepte un filtre `categorie_id`; les listes web et API sont paginées. |
| API REST | Connexion Sanctum, liste/détail publics, écriture protégée par jeton; filtre API par catégorie. |
| Tags | Modèle, relation et affichage éventuel présents; pas de gestion complète des tags par l'interface/API identifiée. |

## Résultats automatisés

Base et commandes vérifiées :

- La connexion par défaut de l'application est MySQL/Laragon.
- Les tests sont dirigés vers la base distincte `mediahub_test`; `tests/TestCase.php` bloque toute exécution avec une autre base.
- `php artisan migrate:status` sur la base de développement : 12 migrations exécutées, aucune en attente.
- Suite complète après les corrections : **78 tests réussis**, **323 assertions**, 0 échec. Commande : `php artisan test --compact`.
- La suite utilise `RefreshDatabase` uniquement sur la base dédiée de test.

## Résultats de la vérification manuelle de l'environnement

- PHP CLI 8.3.36 et Composer 2.10.3 disponibles.
- Extensions PHP nécessaires au projet observées, dont `pdo_mysql`, `gd`, `mbstring`, `fileinfo`, `openssl`, `xml`, `curl` et `zip`.
- MySQL accessible; moteur confirmé MySQL, et schéma existant cohérent avec les migrations.
- `.env` indique un contexte local avec `APP_DEBUG=true`; c'est attendu pour le développement et ne doit pas être copié tel quel en production.
- `.env` n'est pas suivi par Git et est ignoré; les fichiers sensibles ne sont pas publiés.
- Lien `public/storage` présent et pointant vers `storage/app/public`. Des fichiers de médias existent dans les espaces public et privé; aucun média n'a été modifié pendant cet audit.
- Hash des mots de passe configuré sur bcrypt; les formulaires web utilisent le middleware web/CSRF Laravel.

## Corrections réalisées pendant l'atelier

- L'URL `/admin/tableau-de-bord` était publique. Elle exige maintenant une authentification et le Gate `gerer-categories`; tests visiteur, auteur et admin.
- Les deux points de connexion (web et API) étaient sans limitation explicite. Ils partagent maintenant une limite de cinq tentatives par minute, basée sur l'adresse normalisée et l'IP; test de franchissement de limite.
- README/API et guides mis en cohérence avec MySQL/Laragon.

## Points restant à traiter

- **À CORRIGER / décision fonctionnelle** : ajouter le cycle d'écriture des commentaires si demandé; aujourd'hui les commentaires sont seulement affichés.
- **À CORRIGER / décision fonctionnelle** : ajouter une recherche textuelle web et des évaluations uniquement si elles font partie du périmètre attendu.
- **À FAIRE avant production** : fournir une vraie page de tableau de bord admin si nécessaire, domaine/HTTPS, secrets de production, sauvegardes planifiées et restauration testée.
- **NON TESTÉ** : véritable VirtualHost Apache/Nginx, HTTPS public, déploiement réel, import de sauvegarde.
- **NON TESTÉ** : recette dans un navigateur externe. Les vérifications fonctionnelles automatiques ont été réalisées via PHPUnit.
- **NON TESTÉ** : build Vite; Node.js/npm ne sont pas disponibles dans l'environnement de travail.
- Les caches de production doivent être testés sur le serveur isolé. `optimize:clear` vide aussi le cache applicatif MySQL; ne pas le lancer sans l'option `--except=cache` si ces données doivent être conservées.
