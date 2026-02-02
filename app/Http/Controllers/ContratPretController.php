<?php

namespace App\Http\Controllers;

use App\Models\ContratPret;
use App\Models\Garantie;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ContratPretController extends Controller
{
    /**
     * Liste tous les contrats de prêts
     */
    public function index(Request $request)
    {
        $query = ContratPret::query();

        // Filtre par recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_pret', 'like', "%{$search}%")
                  ->orWhere('matricule_client', 'like', "%{$search}%")
                  ->orWhere('nom_client', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->has('statut') && $request->statut) {
            $query->where('statut', $request->statut);
        }

        $contratsPrets = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('contrats-prets/Index', [
            'contratsPrets' => $contratsPrets,
        ]);
    }

    /**
     * Affiche un contrat de prêt spécifique
     */
    public function show(ContratPret $contratPret)
    {
        $contratPret->load(['garanties' => function($query) {
            $query->withPivot('pourcentage_utilisation', 'montant_utilise', 'created_at');
        }]);

        // Récupérer les garanties disponibles pour liaison
        $garantiesDisponibles = \App\Models\Garantie::where('statut', 'normal')
            ->orWhere('statut', 'dation')
            ->get()
            ->filter(function($garantie) {
                return $garantie->estDisponiblePourPret();
            })
            ->map(function($garantie) {
                $garantie->montant_restant = $garantie->calculerMontantRestant();
                return $garantie;
            });

        return Inertia::render('contrats-prets/Show', [
            'contratPret' => $contratPret,
            'garantiesDisponibles' => $garantiesDisponibles,
        ]);
    }

    /**
     * API: Recherche de contrat dans Flexcube (simulation pour l'instant)
     */
    public function rechercherFlexcube(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_pret' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Numéro de prêt requis'], 400);
        }

        $numeroPret = $request->input('numero_pret');

        // TODO: Intégrer avec l'API Flexcube réelle
        // Pour l'instant, on cherche dans la base locale
        $contrat = ContratPret::where('numero_pret', $numeroPret)->first();

        if ($contrat) {
            return response()->json([
                'exists' => true,
                'contrat' => $contrat,
            ]);
        }

        // Simulation de données Flexcube (à remplacer par l'intégration réelle)
        return response()->json([
            'exists' => false,
            'message' => 'Contrat non trouvé dans Flexcube. Intégration à implémenter.',
        ]);
    }

    /**
     * Crée ou met à jour un contrat de prêt depuis Flexcube
     */
    public function syncFlexcube(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_pret' => 'required|string',
            'montant_accorde' => 'required|numeric',
            'date_mise_en_place' => 'required|date',
            'date_maturite' => 'nullable|date',
            'statut' => 'required|string',
            'matricule_client' => 'required|string',
            'nom_client' => 'nullable|string',
            'nature_juridique' => 'nullable|string|in:Entreprise,Particulier',
            'secteur_activite' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validated();
        $data['sync_flexcube_at'] = now();

        $contrat = ContratPret::updateOrCreate(
            ['numero_pret' => $data['numero_pret']],
            $data
        );

        return response()->json([
            'success' => true,
            'contrat' => $contrat,
        ]);
    }

    /**
     * Lie une garantie à un contrat de prêt
     */
    public function lierGarantie(Request $request, ContratPret $contratPret)
    {
        $validator = Validator::make($request->all(), [
            'garantie_id' => 'required|exists:garanties,id',
            'pourcentage_utilisation' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $garantie = Garantie::with('client')->findOrFail($request->input('garantie_id'));
        $pourcentage = $request->input('pourcentage_utilisation');

        // Vérifier que la garantie a un client associé
        if (!$garantie->client) {
            return redirect()->back()
                ->with('error', 'Cette garantie n\'a pas de client associé. Veuillez d\'abord associer un client à la garantie.');
        }

        // Vérifier que le contrat de prêt appartient au client de la garantie
        if ($contratPret->matricule_client !== $garantie->client->matricule) {
            return redirect()->back()
                ->with('error', 'Ce contrat de prêt n\'appartient pas au client associé à cette garantie.');
        }

        // Vérifier que la garantie est disponible
        if (!$garantie->estDisponiblePourPret()) {
            return redirect()->back()
                ->with('error', 'Cette garantie n\'est pas disponible pour un nouveau prêt.');
        }

        // Calculer le montant utilisé
        $montantUtilise = ($garantie->valeur_reelle * $pourcentage) / 100;

        // Vérifier qu'il y a assez de montant restant
        $montantRestant = $garantie->calculerMontantRestant();
        if ($montantUtilise > $montantRestant) {
            return redirect()->back()
                ->with('error', 'Le montant à utiliser dépasse le montant restant disponible sur la garantie.');
        }

        // Vérifier que la garantie n'est pas déjà liée à ce contrat
        if ($contratPret->garanties()->where('garanties.id', $garantie->id)->exists()) {
            return redirect()->back()
                ->with('error', 'Cette garantie est déjà liée à ce contrat de prêt.');
    }

        // Lier la garantie au contrat
        DB::transaction(function() use ($contratPret, $garantie, $pourcentage, $montantUtilise) {
            $contratPret->garanties()->attach($garantie->id, [
                'pourcentage_utilisation' => $pourcentage,
                'montant_utilise' => $montantUtilise,
            ]);
        });

        return redirect()->route('contrats-prets.show', $contratPret)
            ->with('success', 'Garantie liée au contrat avec succès !');
    }

    /**
     * Délie une garantie d'un contrat de prêt
     */
    public function delierGarantie(ContratPret $contratPret, Garantie $garantie)
    {
        // Vérifier que la garantie est bien liée au contrat
        if (!$contratPret->garanties()->where('garanties.id', $garantie->id)->exists()) {
            return redirect()->back()
                ->with('error', 'Cette garantie n\'est pas liée à ce contrat.');
        }

        // Délier la garantie
        $contratPret->garanties()->detach($garantie->id);

        return redirect()->route('contrats-prets.show', $contratPret)
            ->with('success', 'Garantie déliée du contrat avec succès !');
    }
}