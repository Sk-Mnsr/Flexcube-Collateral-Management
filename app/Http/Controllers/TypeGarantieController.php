<?php

namespace App\Http\Controllers;

use App\Models\TypeGarantie;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class TypeGarantieController extends Controller
{
    /**
     * Liste tous les types de garanties (admin seulement)
     */
    public function index(Request $request)
    {
        $query = TypeGarantie::query();

        // Filtre par recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('libelle', 'like', "%{$search}%");
            });
        }

        $typesGaranties = $query->orderBy('libelle')
            ->paginate($request->get('per_page', 15));

        return Inertia::render('types-garanties/Index', [
            'typesGaranties' => $typesGaranties,
        ]);
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        return Inertia::render('types-garanties/Create');
    }

    /**
     * Crée un nouveau type de garantie
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20|unique:types_garanties,code',
            'libelle' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'decote_pourcentage' => 'required|numeric|min:0|max:100',
            'ponderation_pourcentage' => 'required|numeric|min:0|max:100',
            'actif' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        TypeGarantie::create($data);

        return redirect()->route('types-garanties.index')
            ->with('success', 'Type de garantie créé avec succès !');
    }

    /**
     * Affiche un type de garantie spécifique
     */
    public function show(TypeGarantie $typeGarantie)
    {
        return Inertia::render('types-garanties/Show', [
            'typeGarantie' => $typeGarantie,
        ]);
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(TypeGarantie $typeGarantie)
    {
        return Inertia::render('types-garanties/Edit', [
            'typeGarantie' => $typeGarantie,
        ]);
    }

    /**
     * Met à jour un type de garantie
     */
    public function update(Request $request, TypeGarantie $typeGarantie)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20|unique:types_garanties,code,' . $typeGarantie->id,
            'libelle' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'decote_pourcentage' => 'required|numeric|min:0|max:100',
            'ponderation_pourcentage' => 'required|numeric|min:0|max:100',
            'actif' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Si la pondération change, recalculer toutes les garanties de ce type
        // (optionnel selon les besoins métier)
        
        $typeGarantie->update($data);

        return redirect()->route('types-garanties.index')
            ->with('success', 'Type de garantie mis à jour avec succès !');
    }

    /**
     * Supprime un type de garantie
     */
    public function destroy(TypeGarantie $typeGarantie)
    {
        // Vérifier qu'il n'y a pas de garanties liées
        if ($typeGarantie->garanties()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer ce type de garantie car il est utilisé par des garanties.');
        }

        $typeGarantie->delete();

        return redirect()->route('types-garanties.index')
            ->with('success', 'Type de garantie supprimé avec succès !');
    }
}