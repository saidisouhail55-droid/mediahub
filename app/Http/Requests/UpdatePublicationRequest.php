<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePublicationRequest extends FormRequest
{
    /**
     * Semaine 9 : seul un utilisateur authentifié peut modifier.
     * L'autorisation réelle est vérifiée dans ContenuController via PublicationPolicy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la modification d'une publication.
     *
     * Semaine 7 : exclut la publication courante de la règle d'unicité du titre.
     * Semaine 9 : ajout des règles pour image, fichier_media et document_prive.
     *
     * Explication clé : 'nullable' + 'file' signifie que si l'utilisateur ne
     * téléverse pas de nouveau fichier (champ vide), la validation passe et
     * on conserve l'ancien fichier en base de données.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titre' => [
                'required',
                'string',
                'min:10',
                'max:255',
                Rule::unique('publications', 'titre')->ignore($this->publication),
            ],
            'contenu'        => 'required|string|min:20',
            'categorie_id'   => 'required|exists:categories,id',

            // Fichiers : nullable = on conserve l'existant si aucun nouveau n'est envoyé
            'image'          => 'nullable|file|image|max:2048',
            'fichier_media'  => 'nullable|file|mimes:mp3,mp4,wav|max:20480',
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
