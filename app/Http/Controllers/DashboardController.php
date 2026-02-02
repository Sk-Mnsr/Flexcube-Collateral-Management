<?php

namespace App\Http\Controllers;

use App\Models\Garantie;
use App\Models\ContratPret;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. Total des garanties
        $totalGaranties = Garantie::count();

        // 2. Garanties expirées (date_expiration < aujourd'hui)
        $garantiesExpirees = Garantie::whereNotNull('date_expiration')
            ->where('date_expiration', '<', $today)
            ->count();

        // 3. Garanties non affectées (pas de contrats actifs liés avec montant utilisé > 0)
        $garantiesNonAffectees = Garantie::whereNotIn('id', DB::table('garantie_contrat_pret')
            ->join('contrats_prets', 'garantie_contrat_pret.contrat_pret_id', '=', 'contrats_prets.id')
            ->where('contrats_prets.statut', 'actif')
            ->where('garantie_contrat_pret.montant_utilise', '>', 0)
            ->pluck('garantie_id')
        )->count();

        // 4. Prêts non couverts (contrats actifs sans garanties ou avec garanties insuffisantes)
        $pretsNonCouverts = ContratPret::where('statut', 'actif')
            ->where(function($query) {
                // Contrats sans garanties
                $query->whereDoesntHave('garanties')
                    // Ou contrats dont la somme des montants utilisés des garanties < montant accordé
                    ->orWhereRaw('montant_accorde > COALESCE((
                        SELECT SUM(montant_utilise)
                        FROM garantie_contrat_pret
                        WHERE garantie_contrat_pret.contrat_pret_id = contrats_prets.id
                    ), 0)');
            })
            ->count();

        // 5. Garanties partagées entre plusieurs prêts (garanties liées à plus d'un contrat actif)
        $garantiesPartagees = Garantie::whereHas('contratsPret', function($query) {
                $query->where('contrats_prets.statut', 'actif');
            })
            ->withCount(['contratsPret' => function($query) {
                $query->where('contrats_prets.statut', 'actif');
            }])
            ->get()
            ->filter(function($garantie) {
                return $garantie->contrats_pret_count > 1;
            })
            ->count();

        // 6. Informations pour la revue trimestrielle
        $derniereRevue = Carbon::now()->startOfQuarter();
        $prochaineRevue = Carbon::now()->addQuarter()->startOfQuarter();
        $joursAvantRevue = Carbon::now()->diffInDays($prochaineRevue, false);

        // 7. Garanties levées (statut = main_leve)
        $garantiesLevees = Garantie::where('statut', 'main_leve')->count();

        // 8. Garanties soldées (statut = vendu)
        $garantiesSoldees = Garantie::where('statut', 'vendu')->count();

        // 9. Garanties cédées (statut = mutation_tiers - cédées à un tiers)
        $garantiesCedees = Garantie::where('statut', 'mutation_tiers')->count();

        // 10. Garanties adjugées (statut = realisation - adjudiquées/réalisées)
        $garantiesAdjuguees = Garantie::where('statut', 'realisation')->count();

        // 11. Garanties en attente de main levée (statut = realisation qui peuvent passer à main_leve)
        // Ce sont toutes les garanties en réalisation qui peuvent passer à main levée
        // Pour optimiser, on charge les types de garanties nécessaires
        $garantiesEnAttenteMainLevee = Garantie::where('statut', 'realisation')
            ->with('typeGarantie')
            ->get()
            ->filter(function($garantie) {
                return $garantie->peutPasserA('main_leve');
            })
            ->count();

        return Inertia::render('Dashboard', [
            'statistiques' => [
                'totalGaranties' => $totalGaranties,
                'garantiesExpirees' => $garantiesExpirees,
                'garantiesNonAffectees' => $garantiesNonAffectees,
                'pretsNonCouverts' => $pretsNonCouverts,
                'garantiesPartagees' => $garantiesPartagees,
                'garantiesLevees' => $garantiesLevees,
                'garantiesSoldees' => $garantiesSoldees,
                'garantiesCedees' => $garantiesCedees,
                'garantiesAdjuguees' => $garantiesAdjuguees,
                'garantiesEnAttenteMainLevee' => $garantiesEnAttenteMainLevee,
                'derniereRevue' => $derniereRevue->format('d/m/Y'),
                'prochaineRevue' => $prochaineRevue->format('d/m/Y'),
                'joursAvantRevue' => $joursAvantRevue,
            ],
        ]);
    }
}

