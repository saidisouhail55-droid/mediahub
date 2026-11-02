<?php

namespace App\Http\Controllers;


class PageController extends Controller
{
       public function accueil()
    {
        return view('accueil');
    }

    

    // Page à propos
    public function aPropos()
    {
        return 'À propos de MediaHub';
    }
}

