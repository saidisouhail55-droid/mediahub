<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationRequest extends FormRequest
{
    /**
     * Semaine 9 : seul un utilisateur authentifié peut créer une publication.
     * La vérification est déjà assurée par le middleware 'auth' sur la route,
     * mais authorize() renvoie true pour ne pas bloquer doublement.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la création d'une publication.
     *
     * Semaine 7 : titre ≥ 10 caractères et unique, contenu ≥ 20 caractères.
     * Semaine 9 : ajout des règles pour image, fichier_media et document_prive.
     *
     * Explication des règles fichier :
     * - 'file'   : vérifie que c'est bien un fichier téléversé (pas une chaîne)
     * - 'image'  : vérifie que le MIME type réel est image/* (GD PHP valide le contenu)
     * - 'mimes'  : vérifie l'extension ET le type MIME réel
     * - 'max'    : taille maximale en kilo-octets (2048 = 2 Mo, 20480 = 20 Mo)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titre'          => 'required|string|min:10|max:255|unique:publications,titre',
            'contenu'        => 'required|string|min:20',
            'categorie_id'   => 'required|exists:categories,id',

            // Image de couverture : optionnelle, max 2 Mo, type image vérifié par GD
            'image'          => 'nullable|file|image|max:2048',

            // Fichier audio ou vidéo : optionnel, extensions autorisées, max 20 Mo
            'fichier_media'  => 'nullable|file|mimes:mp3,mp4,wav|max:20480',

            // Document privé : optionnel, PDF uniquement, max 10 Mo
            'document_prive' => 'nullable|file|mimes:pdf|max:10240',
        ];
    }

    /**
     * Messages d'erreur personnalisés en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titre.required'          => 'Le titre de la publication est obligatoire.',
            'titre.string'            => 'Le titre doit être une chaîne de caractères.',
            'titre.min'               => 'Le titre doit contenir au moins 10 caractères.',
            'titre.max'               => 'Le titre ne peut pas dépasser 255 caractères.',
            'titre.unique'            => 'Une publication avec ce titre existe déjà.',
            'contenu.required'        => 'Le contenu de la publication est obligatoire.',
            'contenu.string'          => 'Le contenu doit être une chaîne de caractères.',
            'contenu.min'             => 'Le contenu doit contenir au moins 20 caractères.',
            'categorie_id.required'   => 'Veuillez sélectionner une catégorie.',
            'categorie_id.exists'     => 'La catégorie sélectionnée n\'existe pas.',
            'image.file'              => 'Le fichier image n\'est pas valide.',
            'image.image'             => 'Le fichier doit être une image (JPEG, PNG, GIF, WebP, SVG).',
            'image.max'               => 'L\'image ne doit pas dépasser 2 Mo.',
            'fichier_media.file'      => 'Le fichier multimédia n\'est pas valide.',
            'fichier_media.mimes'     => 'Le fichier multimédia doit être au format MP3, MP4 ou WAV.',
            'fichier_media.max'       => 'Le fichier multimédia ne doit pas dépasser 20 Mo.',
            'document_prive.file'     => 'Le document n\'est pas valide.',
            'document_prive.mimes'    => 'Le document privé doit être au format PDF.',
            'document_prive.max'      => 'Le document privé ne doit pas dépasser 10 Mo.',
        ];
    }
}
