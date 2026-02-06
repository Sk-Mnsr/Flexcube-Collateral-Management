<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('auth/Login', [
       // 'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

use App\Http\Controllers\DashboardController;

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';

use App\Http\Controllers\UserController;
use App\Http\Controllers\GarantieController;
use App\Http\Controllers\GarantController;
use App\Http\Controllers\TypeGarantieController;
use App\Http\Controllers\ContratPretController;
use App\Http\Controllers\ClientController;

    // Routes pour les utilisateurs
    // - SuperAdmin uniquement : toutes les opérations (fait partie de Configuration)
    Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class)->middleware('role:it');
    Route::post('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle')->middleware('role:it');

    // Routes pour la gestion des garanties
    // - SuperAdmin et Admin : lecture et édition complète
    // - Juridique : lecture seule + changement de statut
    // - Chargé d'Affaires : lecture seule (index, show)
    Route::get('garanties', [GarantieController::class, 'index'])->name('garanties.index');
    Route::get('garanties/create', [GarantieController::class, 'create'])->name('garanties.create')->middleware('role:it,admin');
    Route::post('garanties', [GarantieController::class, 'store'])->name('garanties.store')->middleware('role:it,admin');
    Route::get('garanties/{garantie}', [GarantieController::class, 'show'])->name('garanties.show');
    Route::get('garanties/{garantie}/edit', [GarantieController::class, 'edit'])->name('garanties.edit')->middleware('role:it,admin');
    Route::match(['put', 'post'], 'garanties/{garantie}', [GarantieController::class, 'update'])->name('garanties.update')->middleware('role:it,admin');
    Route::delete('garanties/{garantie}', [GarantieController::class, 'destroy'])->name('garanties.destroy')->middleware('role:it,admin');
    Route::post('garanties/{garantie}/changer-statut', [GarantieController::class, 'changerStatut'])->name('garanties.changer-statut')->middleware('role:juridique');
    Route::get('liaisons', [GarantieController::class, 'liaison'])->name('garanties.liaison')->middleware('role:it,admin');
    Route::post('garanties/{garantie}/lier-contrat-pret', [GarantieController::class, 'lierContratPret'])->name('garanties.lier-contrat-pret')->middleware('role:it,admin');
    Route::delete('garanties/{garantie}/delier-contrat-pret/{contratPret}', [GarantieController::class, 'delierContratPret'])->name('garanties.delier-contrat-pret')->middleware('role:it,admin');

    // Routes pour les garants
    // - SuperAdmin et Admin : lecture et édition complète
    // - Juridique et Chargé d'Affaires : lecture seule (index, show)
    Route::get('garants', [GarantController::class, 'index'])->name('garants.index');
    Route::get('garants/create', [GarantController::class, 'create'])->name('garants.create')->middleware('role:it,admin');
    Route::post('garants', [GarantController::class, 'store'])->name('garants.store')->middleware('role:it,admin');
    Route::get('garants/{garant}', [GarantController::class, 'show'])->name('garants.show');
    Route::get('garants/{garant}/edit', [GarantController::class, 'edit'])->name('garants.edit')->middleware('role:it,admin');
    Route::match(['put', 'post'], 'garants/{garant}', [GarantController::class, 'update'])->name('garants.update')->middleware('role:it,admin');
    Route::delete('garants/{garant}', [GarantController::class, 'destroy'])->name('garants.destroy')->middleware('role:it,admin');

    // Routes pour les types de garanties
    // - SuperAdmin uniquement : toutes les opérations (fait partie de Configuration)
    Route::get('types-garanties', [TypeGarantieController::class, 'index'])->name('types-garanties.index');
    Route::get('types-garanties/create', [TypeGarantieController::class, 'create'])->name('types-garanties.create')->middleware('role:it');
    Route::post('types-garanties', [TypeGarantieController::class, 'store'])->name('types-garanties.store')->middleware('role:it');
    Route::get('types-garanties/{typeGarantie}', [TypeGarantieController::class, 'show'])->name('types-garanties.show');
    Route::get('types-garanties/{typeGarantie}/edit', [TypeGarantieController::class, 'edit'])->name('types-garanties.edit')->middleware('role:it');
    Route::put('types-garanties/{typeGarantie}', [TypeGarantieController::class, 'update'])->name('types-garanties.update')->middleware('role:it');
    Route::delete('types-garanties/{typeGarantie}', [TypeGarantieController::class, 'destroy'])->name('types-garanties.destroy')->middleware('role:it');

    // Routes pour les contrats de prêts
    // - SuperAdmin et Admin : lecture et édition complète
    // - Juridique et Chargé d'Affaires : lecture seule (index, show)
    Route::get('contrats-prets', [ContratPretController::class, 'index'])->name('contrats-prets.index');
    Route::get('contrats-prets/create', [ContratPretController::class, 'create'])->name('contrats-prets.create')->middleware('role:it,admin');
    Route::post('contrats-prets', [ContratPretController::class, 'store'])->name('contrats-prets.store')->middleware('role:it,admin');
    Route::get('contrats-prets/{contratPret}', [ContratPretController::class, 'show'])->name('contrats-prets.show');
    Route::get('contrats-prets/{contratPret}/edit', [ContratPretController::class, 'edit'])->name('contrats-prets.edit')->middleware('role:it,admin');
    Route::put('contrats-prets/{contratPret}', [ContratPretController::class, 'update'])->name('contrats-prets.update')->middleware('role:it,admin');
    Route::delete('contrats-prets/{contratPret}', [ContratPretController::class, 'destroy'])->name('contrats-prets.destroy')->middleware('role:it,admin');
    Route::post('api/contrats-prets/rechercher-flexcube', [ContratPretController::class, 'rechercherFlexcube'])->name('api.contrats-prets.rechercher-flexcube')->middleware('role:it,admin');
    Route::post('api/contrats-prets/sync-flexcube', [ContratPretController::class, 'syncFlexcube'])->name('api.contrats-prets.sync-flexcube')->middleware('role:it,admin');
    Route::post('contrats-prets/{contratPret}/lier-garantie', [ContratPretController::class, 'lierGarantie'])->name('contrats-prets.lier-garantie')->middleware('role:it,admin');
    Route::delete('contrats-prets/{contratPret}/garanties/{garantie}', [ContratPretController::class, 'delierGarantie'])->name('contrats-prets.delier-garantie')->middleware('role:it,admin');

    // Routes pour les clients (lecture seule)
    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');

    // Route pour servir les fichiers de manière sécurisée
    Route::get('storage/{path}', function ($path) {
        $filePath = storage_path('app/public/' . $path);
        
        if (!file_exists($filePath)) {
            abort(404);
        }
        
        // Vérifier que le fichier est dans un répertoire autorisé
        $allowedDirs = ['documentations_garanties', 'garanties', 'garants'];
        $pathParts = explode('/', $path);
        
        if (!in_array($pathParts[0], $allowedDirs)) {
            abort(403, 'Accès non autorisé à ce fichier');
        }
        
        return response()->file($filePath);
    })->where('path', '.*')->name('storage.file');
});
