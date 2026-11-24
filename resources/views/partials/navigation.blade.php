<nav class="navigation">
    <div style="display: flex; align-items: center; gap: 2rem;">
        <a href="{{ route('accueil') }}" class="nav-logo">MediaHub</a>
        <div class="nav-links">
            <a href="{{ route('contenus.index') }}">Contenus</a>
            <a href="{{ route('categories.index') }}">Catégories</a>
            <a href="{{ route('a-propos') }}">À propos</a>
        </div>
    </div>

    <div class="nav-auth" style="display: flex; align-items: center; gap: 1rem;">
        @guest
            <a href="{{ route('connexion') }}" class="btn btn-secondaire" style="padding: 0.35rem 0.8rem; font-size: 0.9rem;">Connexion</a>
            <a href="{{ route('inscription') }}" class="btn btn-primaire" style="padding: 0.35rem 0.8rem; font-size: 0.9rem;">Inscription</a>
        @endguest

        @auth
            <span style="color: #e0e7ff; font-size: 0.9rem;">
                👤 <strong>{{ auth()->user()->name }}</strong>
                <small style="background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 4px; margin-left: 4px;">{{ auth()->user()->role }}</small>
            </span>

            <form method="POST" action="{{ route('deconnexion') }}" style="display: inline-block;">
                @csrf
                <button type="submit" class="btn btn-danger" style="padding: 0.35rem 0.8rem; font-size: 0.9rem; background-color: #b91c1c;">Déconnexion</button>
            </form>
        @endauth
    </div>
</nav>
