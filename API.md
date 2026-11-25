# API REST MediaHub

Base URL : `${APP_URL}/api`. En local avec `php artisan serve`, l'exemple est `http://localhost:8000/api`; avec un VirtualHost Laragon, utiliser son URL locale.

Toutes les réponses sont en JSON. Pour les requêtes protégées, envoyer :

```http
Authorization: Bearer <jeton>
Accept: application/json
```

## Authentification

### Obtenir un jeton

`POST /api/connexion`

Authentification requise : Non

Requête :

```json
{
  "email": "api@mediahub.test",
  "password": "password"
}
```

Réponse `200` :

```json
{
  "token": "1|exemple...",
  "token_type": "Bearer"
}
```

Erreur `422` : champs d'authentification absents ou invalides.

Erreur `401` :

```json
{
  "message": "Identifiants incorrects."
}
```

Erreur `429` : plus de cinq tentatives de connexion par minute pour la même adresse e-mail et la même IP.

## Publications

### Liste paginée

`GET /api/publications`

Authentification requise : Non

Paramètre facultatif : `?categorie_id=<id>`

Exemple : `GET /api/publications?categorie_id=2`

Réponse `200` :

```json
{
  "data": [
    {
      "id": 12,
      "titre": "Une publication MediaHub",
      "contenu": "Description de la publication.",
      "categorie": {"id": 2, "nom": "Musique"},
      "auteur": {"id": 4, "name": "Auteur"},
      "image": "http://mediahub.test/storage/publications/image.jpg",
      "fichier_media": null,
      "type_media": null,
      "publie_le": "2026-11-24"
    }
  ],
  "links": {},
  "meta": {}
}
```

### Détail

`GET /api/publications/{id}`

Authentification requise : Non

Réponse `200` :

```json
{
  "data": {
    "id": 12,
    "titre": "Une publication MediaHub",
    "contenu": "Description de la publication.",
    "categorie": {"id": 2, "nom": "Musique"},
    "auteur": {"id": 4, "name": "Auteur"},
    "image": null,
    "fichier_media": null,
    "type_media": null,
    "publie_le": "2026-11-24"
  }
}
```

Erreur `404` : publication introuvable.

### Créer

`POST /api/publications`

Authentification requise : Oui

Requête JSON :

```json
{
  "titre": "Ma nouvelle publication",
  "contenu": "Description suffisamment détaillée.",
  "categorie_id": 2
}
```

Réponse `201` : la publication sous la forme `{"data": {...}}`.

Erreur `401` : jeton absent ou invalide.

Erreur `422` :

```json
{
  "message": "Le titre de la publication est obligatoire.",
  "errors": {
    "titre": ["Le titre de la publication est obligatoire."]
  }
}
```

Les champs `image`, `fichier_media` et `document_prive` peuvent être envoyés en multipart.

### Modifier

`PUT/PATCH /api/publications/{id}`

Authentification requise : Oui

Seul l'auteur ou un administrateur peut modifier la publication.

Requête :

```json
{
  "titre": "Titre modifié",
  "contenu": "Contenu modifié suffisamment détaillé.",
  "categorie_id": 2
}
```

Réponse `200` : `{"data": {...}}`.

Erreurs :

- `401` : jeton absent ou invalide
- `403` : utilisateur non propriétaire
- `404` : publication introuvable
- `422` : données invalides

### Supprimer

`DELETE /api/publications/{id}`

Authentification requise : Oui

Seul l'auteur ou un administrateur peut supprimer la publication.

Réponse `204` : aucune donnée.

Erreurs :

- `401` : jeton absent ou invalide
- `403` : utilisateur non propriétaire
- `404` : publication introuvable

## Catégories

### Liste des catégories

`GET /api/categories`

Authentification requise : Non

Réponse `200` :

```json
[
  {
    "id": 2,
    "nom": "Musique",
    "slug": "musique",
    "nombre_publications": 8
  }
]
```
