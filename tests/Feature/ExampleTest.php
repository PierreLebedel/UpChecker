<?php

use Illuminate\Support\Facades\Route;

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Détendez-vous')
        ->assertSee('Connexion')
        ->assertSee('GitHub')
        ->assertSee('Production API')
        ->assertSee('Choisissez vos canaux d’alerte');

    if (Route::has('register')) {
        $response->assertSee('Créer un compte');
    } else {
        $response->assertDontSee('Créer un compte');
    }
});
