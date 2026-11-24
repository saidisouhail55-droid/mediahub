<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire d'inscription.
     */
    public function create()
    {
        return view('auth.inscription');
    }

    /**
     * Traiter la demande d'inscription et connecter automatiquement l'utilisateur.
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => 'auteur',
        ]);

        Auth::login($user);

        return redirect()
            ->route('accueil')
            ->with('succes', 'Bienvenue sur MediaHub ! Votre compte a été créé avec succès.');
    }

    /**
     * Afficher le formulaire de connexion.
     */
    public function creerConnexion()
    {
        return view('auth.connexion');
    }

    /**
     * Authentifier l'utilisateur et régénérer la session pour prévenir la fixation de session.
     */
    public function connecter(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Identifiants incorrects. Veuillez vérifier votre adresse e-mail et votre mot de passe.',
                ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('accueil'))
            ->with('succes', 'Ravi de vous revoir sur MediaHub, ' . Auth::user()->name . ' !');
    }

    /**
     * Déconnecter l'utilisateur, invalider la session et régénérer le jeton CSRF.
     */
    public function deconnecter(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('accueil')
            ->with('succes', 'Vous avez été déconnecté avec succès.');
    }
}
