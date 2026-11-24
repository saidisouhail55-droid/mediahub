<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé à effectuer cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la création d'une publication.
     * Inclut le mini-défi : titre >= 10 caractères et unique.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titre' => 'required|string|min:10|max:255|unique:publications,titre',
            'contenu' => 'required|string|min:20',
            'categorie_id' => 'required|exists:categories,id',
            'image' => 'nullable|string|max:255',
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
            'titre.required' => 'Le titre de la publication est obligatoire.',
            'titre.string' => 'Le titre doit être une chaîne de caractères.',
            'titre.min' => 'Le titre doit contenir au moins 10 caractères.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'titre.unique' => 'Une publication avec ce titre existe déjà.',
            'contenu.required' => 'Le contenu de la publication est obligatoire.',
            'contenu.string' => 'Le contenu doit être une chaîne de caractères.',
            'contenu.min' => 'Le contenu doit contenir au moins 20 caractères.',
            'categorie_id.required' => 'Veuillez sélectionner une catégorie.',
            'categorie_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
        ];
    }
}
