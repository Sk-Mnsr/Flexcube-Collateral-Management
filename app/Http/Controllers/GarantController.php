<?php

namespace App\Http\Controllers;

use App\Models\Garant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class GarantController extends Controller
{
    /**
     * Liste tous les garants
     */
    public function index(Request $request)
    {
        $query = Garant::query();

        // Filtre par recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('numero_piece_identite', 'like', "%{$search}%");
            });
        }

        $garants = $query->orderBy('nom')->orderBy('prenom')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('garants/Index', [
            'garants' => $garants,
        ]);
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        return Inertia::render('garants/Create');
    }

    /**
     * Crée un nouveau garant
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'civilite' => 'required|string|in:M,Mme,Mlle',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'adresse' => 'required|string',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'nationalite' => 'required|string|max:100',
            'activite' => 'nullable|string|max:255',
            'adresse_activite' => 'nullable|string',
            'type_piece_identite' => 'required|string|max:100',
            'numero_piece_identite' => 'required|string|max:100',
            'fichier_piece_identite' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'date_delivrance_piece_identite' => 'required|date',
            'date_expiration_piece_identite' => 'required|date',
            'telephone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Vérifier l'unicité (nom, prénom, date de naissance pour garanties non terminées)
        if (!Garant::canCreate($data)) {
            return redirect()->back()
                ->withErrors(['unicite' => 'Un garant avec les mêmes nom, prénom et date de naissance existe déjà pour une garantie non terminée.'])
                ->withInput();
        }

        // Gérer le fichier de pièce d'identité si fourni
        if ($request->hasFile('fichier_piece_identite')) {
            $file = $request->file('fichier_piece_identite');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('garants/pieces_identite', $filename, 'public');
            $data['fichier_piece_identite'] = $path;
        }

        $garant = Garant::create($data);

        return redirect()->route('garants.show', $garant)
            ->with('success', 'Garant créé avec succès !');
    }

    /**
     * Affiche un garant spécifique
     */
    public function show(Garant $garant)
    {
        $garant->load(['garanties' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return Inertia::render('garants/Show', [
            'garant' => $garant,
        ]);
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Garant $garant)
    {
        return Inertia::render('garants/Edit', [
            'garant' => $garant,
        ]);
    }

    /**
     * Met à jour un garant
     */
    public function update(Request $request, Garant $garant)
    {
        $validator = Validator::make($request->all(), [
            'civilite' => 'required|string|in:M,Mme,Mlle',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'adresse' => 'nullable|string',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:100',
            'activite' => 'nullable|string|max:255',
            'adresse_activite' => 'nullable|string',
            'type_piece_identite' => 'required|string|max:100',
            'numero_piece_identite' => 'required|string|max:100',
            'fichier_piece_identite' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'date_delivrance_piece_identite' => 'required|date',
            'date_expiration_piece_identite' => 'required|date',
            'telephone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Vérifier l'unicité seulement si nom, prénom ou date de naissance changent
        if ($data['nom'] != $garant->nom || 
            $data['prenom'] != $garant->prenom || 
            $data['date_naissance'] != $garant->date_naissance) {
            
            if (!Garant::canCreate($data)) {
                return redirect()->back()
                    ->withErrors(['unicite' => 'Un garant avec les mêmes nom, prénom et date de naissance existe déjà pour une garantie non terminée.'])
                    ->withInput();
            }
        }

        // Gérer le fichier de pièce d'identité si fourni
        if ($request->hasFile('fichier_piece_identite')) {
            // Supprimer l'ancien fichier si existe
            if ($garant->fichier_piece_identite) {
                \Storage::disk('public')->delete($garant->fichier_piece_identite);
            }

            $file = $request->file('fichier_piece_identite');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('garants/pieces_identite', $filename, 'public');
            $data['fichier_piece_identite'] = $path;
        }

        // Mettre à jour les champs, sauf le fichier si non fourni
        $updateData = $data;
        if (!$request->hasFile('fichier_piece_identite')) {
            unset($updateData['fichier_piece_identite']);
        }

        $garant->update($updateData);

        return redirect()->route('garants.show', $garant)
            ->with('success', 'Garant mis à jour avec succès !');
    }

    /**
     * Supprime un garant
     */
    public function destroy(Garant $garant)
    {
        // Vérifier qu'il n'y a pas de garanties liées
        if ($garant->garanties()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer ce garant car il est lié à des garanties.');
        }

        // Supprimer le fichier de pièce d'identité si existe
        if ($garant->fichier_piece_identite) {
            \Storage::disk('public')->delete($garant->fichier_piece_identite);
        }

        $garant->delete();

        return redirect()->route('garants.index')
            ->with('success', 'Garant supprimé avec succès !');
    }
}