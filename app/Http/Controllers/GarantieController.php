<?php

namespace App\Http\Controllers;

use App\Models\Garantie;
use App\Models\Garant;
use App\Models\TypeGarantie;
use App\Models\MatriculeClient;
use App\Models\DocumentationGarantie;
use Illuminate\Support\Facades\Storage;
use App\Models\ContratPret;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GarantieController extends Controller
{
    /**
     * Liste toutes les garanties
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter', 'all'); // all, normale, contentieux, realisation, etc.

        $query = Garantie::with(['typeGarantie', 'garant']);

        // Admin voit toutes les garanties
        if (!$user || !$user->isAdmin()) {
            // Pour les autres rôles, on peut ajouter des restrictions si nécessaire
            // Pour l'instant, on laisse tout visible si authentifié
        }

        // Filtre par statut
        if ($filter !== 'all') {
            $query->where('statut', $filter);
        }

        // Filtre par statut spécifique
        if ($request->has('statut') && $request->statut) {
            $query->where('statut', $request->statut);
        }

        // Filtre par recherche (nom, référence, garant)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('reference_unique', 'like', "%{$search}%")
                  ->orWhereHas('garant', function($subQ) use ($search) {
                      $subQ->where('nom', 'like', "%{$search}%")
                           ->orWhere('prenom', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par type de garantie
        if ($request->has('type_garantie_id') && $request->type_garantie_id) {
            $query->where('type_garantie_id', $request->type_garantie_id);
        }

        $garanties = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Calculer les montants utilisés et restants pour chaque garantie
        $garanties->getCollection()->transform(function ($garantie) {
            $montantUtilise = $garantie->calculerMontantUtilise();
            $montantRestant = $garantie->calculerMontantRestant();
            $pourcentageUtilisation = $garantie->calculerPourcentageUtilisation();
            $disponible = $garantie->estDisponiblePourPret();

            $garantie->montant_utilise = $montantUtilise;
            $garantie->montant_restant = $montantRestant;
            $garantie->pourcentage_utilisation = round($pourcentageUtilisation, 2);
            $garantie->disponible_pour_pret = $disponible;

            return $garantie;
        });

        return Inertia::render('garanties/Index', [
            'garanties' => $garanties,
            'filter' => $filter,
            'typesGaranties' => TypeGarantie::where('actif', true)->orderBy('libelle')->get(),
        ]);
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        return Inertia::render('garanties/Create', [
            'typesGaranties' => TypeGarantie::where('actif', true)
                ->orderBy('libelle')
                ->get(['id', 'libelle', 'code', 'decote_pourcentage', 'ponderation_pourcentage']),
            'garants' => Garant::orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'date_naissance']),
            'matriculesClients' => MatriculeClient::orderBy('nom')->get(['id', 'matricule', 'nom', 'nature_juridique', 'secteur_activite']),
            'clients' => Client::orderBy('nom')->orderBy('prenom')->get(['id', 'matricule', 'nom', 'prenom', 'telephone']),
        ]);
    }

    /**
     * Crée une nouvelle garantie
     */
    public function store(Request $request)
    {
        $rules = [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'emplacement' => 'nullable|string|max:255',
            'type_garantie_id' => 'required|exists:types_garanties,id',
            'garant_id' => 'required|exists:garants,id',
            'valeur' => 'required|numeric|min:0',
            'date_creation' => 'required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_creation',
            'matricules_clients' => 'nullable|array',
            'matricules_clients.*' => 'exists:matricules_clients,id',
            'client_id' => 'nullable|exists:clients,id',
            'documentations' => 'nullable|array',
        ];

        // Ajouter les règles de validation pour chaque documentation
        if ($request->has('documentations')) {
            foreach ($request->input('documentations', []) as $index => $doc) {
                $rules["documentations.{$index}.type_documentation"] = 'required|in:texte,fichier';
                $rules["documentations.{$index}.nom"] = 'required|string|max:255';
                $rules["documentations.{$index}.description"] = 'nullable|string';
                $rules["documentations.{$index}.valeur"] = 'nullable|numeric|min:0';
                
                if (isset($doc['type_documentation']) && $doc['type_documentation'] === 'fichier') {
                    $rules["documentations.{$index}.fichier"] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
                }
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Calculer la valeur réelle en utilisant le type de garantie
        $typeGarantie = TypeGarantie::findOrFail($data['type_garantie_id']);
        $valeurReelle = $typeGarantie->calculerValeurReelle($data['valeur']);

        // Générer la référence unique
        $data['reference_unique'] = Garantie::generateReference();
        $data['valeur_reelle'] = $valeurReelle;
        $data['statut'] = 'normal';

        // Créer la garantie
        $garantie = Garantie::create($data);

        // Lier les matricules clients si fournis (ancien système)
        if (isset($data['matricules_clients']) && is_array($data['matricules_clients'])) {
            $garantie->matriculesClients()->attach($data['matricules_clients']);
        }

        // client_id est déjà assigné via $data dans la création

        // Créer les documentations associées
        if ($request->has('documentations')) {
            $documentations = $request->input('documentations', []);
            
            if (is_array($documentations)) {
                foreach ($documentations as $index => $docData) {
                    if (!is_array($docData) || empty($docData['type_documentation'])) {
                        continue;
                    }

                    $docFields = [
                        'garantie_id' => $garantie->id,
                        'type_documentation' => $docData['type_documentation'],
                        'nom' => $docData['nom'] ?? '',
                        'description' => $docData['description'] ?? null,
                        'valeur' => isset($docData['valeur']) && $docData['valeur'] !== '' ? $docData['valeur'] : null,
                    ];

                    // Gérer l'upload de fichier si c'est un fichier
                    if ($docData['type_documentation'] === 'fichier' && $request->hasFile("documentations.{$index}.fichier")) {
                        $file = $request->file("documentations.{$index}.fichier");
                        $path = $file->store('documentations_garanties', 'public');
                        $docFields['chemin_fichier'] = $path;
                    } elseif ($docData['type_documentation'] === 'texte' && isset($docData['description'])) {
                        // Pour les documents texte, la description est stockée dans le champ description
                        $docFields['description'] = $docData['description'];
                    }

                    DocumentationGarantie::create($docFields);
                }
            }
        }

        return redirect()->route('garanties.show', $garantie)
            ->with('success', 'Garantie créée avec succès !');
    }

    /**
     * Affiche une garantie spécifique
     */
    public function show(Garantie $garantie)
    {
        $garantie->load([
            'typeGarantie',
            'garant',
            'client',
            'matriculesClients',
            'documentations',
            'historiques.utilisateur' => function($query) {
                $query->select('id', 'name', 'email');
            },
            'contratsPret' => function($query) {
                $query->where('statut', 'actif')
                      ->withPivot('pourcentage_utilisation', 'montant_utilise', 'created_at');
            },
        ]);

        // Calculer les montants
        $montantUtilise = $garantie->calculerMontantUtilise();
        $montantRestant = $garantie->calculerMontantRestant();
        $pourcentageUtilisation = $garantie->calculerPourcentageUtilisation();

        // Liste des statuts possibles selon le workflow (utilise la méthode peutPasserA du modèle)
        $statutsPossibles = [];
        $tousLesStatuts = ['normal', 'contentieux', 'realisation', 'mutation_tiers', 'mutation_cofina', 'main_leve', 'vendu', 'dation'];
        
        foreach ($tousLesStatuts as $statut) {
            if ($garantie->peutPasserA($statut)) {
                $statutsPossibles[] = $statut;
            }
        }

        return Inertia::render('garanties/Show', [
            'garantie' => $garantie,
            'montantUtilise' => $montantUtilise,
            'montantRestant' => $montantRestant,
            'pourcentageUtilisation' => round($pourcentageUtilisation, 2),
            'disponiblePourPret' => $garantie->estDisponiblePourPret(),
            'statutsPossibles' => $statutsPossibles,
            'historiques' => $garantie->historiques->sortByDesc('created_at')->values(),
        ]);
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Garantie $garantie)
    {
        $garantie->load(['typeGarantie', 'garant', 'client', 'matriculesClients', 'documentations']);

        return Inertia::render('garanties/Edit', [
            'garantie' => $garantie,
            'typesGaranties' => TypeGarantie::where('actif', true)->orderBy('libelle')->get(),
            'garants' => Garant::orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'date_naissance']),
            'clients' => Client::orderBy('nom')->orderBy('prenom')->get(['id', 'matricule', 'nom', 'prenom', 'telephone']),
            'matriculesClients' => MatriculeClient::orderBy('nom')->get(['id', 'matricule', 'nom', 'nature_juridique', 'secteur_activite']),
        ]);
    }

    /**
     * Met à jour une garantie
     */
    public function update(Request $request, Garantie $garantie)
    {
        $rules = [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'emplacement' => 'nullable|string|max:255',
            'type_garantie_id' => 'required|exists:types_garanties,id',
            'garant_id' => 'required|exists:garants,id',
            'valeur' => 'required|numeric|min:0',
            'date_creation' => 'required|date',
            'date_expiration' => 'nullable|date|after_or_equal:date_creation',
            'matricules_clients' => 'nullable|array',
            'matricules_clients.*' => 'exists:matricules_clients,id',
            'client_id' => 'nullable|exists:clients,id',
            'documentations' => 'nullable|array',
        ];

        // Ajouter les règles de validation pour chaque documentation
        if ($request->has('documentations')) {
            foreach ($request->input('documentations', []) as $index => $doc) {
                $rules["documentations.{$index}.type_documentation"] = 'required|in:texte,fichier';
                $rules["documentations.{$index}.nom"] = 'required|string|max:255';
                $rules["documentations.{$index}.description"] = 'nullable|string';
                $rules["documentations.{$index}.valeur"] = 'nullable|numeric|min:0';
                
                // Pour les fichiers, vérifier si c'est un document existant ou nouveau
                if (isset($doc['type_documentation']) && $doc['type_documentation'] === 'fichier') {
                    // Si c'est un nouveau document (pas d'ID ou pas de chemin), le fichier est requis
                    if (!isset($doc['id']) || !isset($doc['chemin_fichier']) || !$doc['chemin_fichier']) {
                        $rules["documentations.{$index}.fichier"] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
                    } else {
                        // Si c'est un document existant, le fichier est optionnel (pour le remplacer)
                        $rules["documentations.{$index}.fichier"] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
                    }
                }
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Recalculer la valeur réelle si le type ou la valeur change
        if ($data['type_garantie_id'] != $garantie->type_garantie_id || $data['valeur'] != $garantie->valeur) {
            $typeGarantie = TypeGarantie::findOrFail($data['type_garantie_id']);
            $data['valeur_reelle'] = $typeGarantie->calculerValeurReelle($data['valeur']);
        }

        // Marquer qui a modifié
        $data['modifie_par'] = Auth::id();
        $data['date_modification'] = now();

        $garantie->update($data);

        // Mettre à jour les matricules clients
        if (isset($data['matricules_clients'])) {
            $garantie->matriculesClients()->sync($data['matricules_clients']);
        }

        // Gérer les documentations
        if ($request->has('documentations')) {
            $documentations = $request->input('documentations', []);
            $existingDocIds = [];
            
            foreach ($documentations as $index => $docData) {
                if (!is_array($docData) || empty($docData['type_documentation'])) {
                    continue;
                }

                $docFields = [
                    'garantie_id' => $garantie->id,
                    'type_documentation' => $docData['type_documentation'],
                    'nom' => $docData['nom'] ?? '',
                    'description' => $docData['description'] ?? null,
                    'valeur' => isset($docData['valeur']) && $docData['valeur'] !== '' ? $docData['valeur'] : null,
                ];

                // Si c'est un document existant (avec ID)
                if (isset($docData['id']) && $docData['id']) {
                    $existingDocIds[] = $docData['id'];
                    $existingDoc = DocumentationGarantie::find($docData['id']);
                    
                    if ($existingDoc && $existingDoc->garantie_id === $garantie->id) {
                        // Gérer le remplacement du fichier si un nouveau fichier est fourni
                        if ($docData['type_documentation'] === 'fichier' && $request->hasFile("documentations.{$index}.fichier")) {
                            // Supprimer l'ancien fichier
                            if ($existingDoc->chemin_fichier) {
                                \Storage::disk('public')->delete($existingDoc->chemin_fichier);
                            }
                            
                            $file = $request->file("documentations.{$index}.fichier");
                            $path = $file->store('documentations_garanties', 'public');
                            $docFields['chemin_fichier'] = $path;
                        }
                        
                        // Mettre à jour le document existant
                        $existingDoc->update($docFields);
                    }
                } else {
                    // C'est un nouveau document
                    if ($docData['type_documentation'] === 'fichier' && $request->hasFile("documentations.{$index}.fichier")) {
                        $file = $request->file("documentations.{$index}.fichier");
                        $path = $file->store('documentations_garanties', 'public');
                        $docFields['chemin_fichier'] = $path;
                    } elseif ($docData['type_documentation'] === 'texte' && isset($docData['description'])) {
                        $docFields['description'] = $docData['description'];
                    }
                    
                    DocumentationGarantie::create($docFields);
                }
            }
            
            // Supprimer les documentations qui ne sont plus dans la liste
            $garantie->documentations()
                ->whereNotIn('id', $existingDocIds)
                ->get()
                ->each(function ($doc) {
                    if ($doc->chemin_fichier) {
                        \Storage::disk('public')->delete($doc->chemin_fichier);
                    }
                    $doc->delete();
                });
        }

        return redirect()->route('garanties.show', $garantie)
            ->with('success', 'Garantie mise à jour avec succès !');
    }

    /**
     * Supprime une garantie
     */
    public function destroy(Garantie $garantie)
    {
        // Vérifier qu'il n'y a pas de contrats de prêts actifs liés
        $contratsActifs = $garantie->contratsPret()
            ->where('statut', 'actif')
            ->count();

        if ($contratsActifs > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer cette garantie car elle est liée à des contrats de prêts actifs.');
        }

        $garantie->delete();

        return redirect()->route('garanties.index')
            ->with('success', 'Garantie supprimée avec succès !');
    }

    /**
     * Change le statut d'une garantie (workflow)
     */
    public function changerStatut(Request $request, Garantie $garantie)
    {
        // Vérifier que seul le rôle juridique peut changer le statut
        if (!Auth::user()->isJuridique()) {
            return redirect()->back()
                ->with('error', 'Seul le service juridique peut changer le statut des garanties.');
        }

        // Validation des fichiers
        $rules = [
            'nouveau_statut' => 'required|string|in:contentieux,realisation,mutation_tiers,mutation_cofina,vendu,main_leve,dation',
            'commentaire' => 'nullable|string|max:1000',
            'documents_justificatifs' => 'nullable|array',
            'documents_justificatifs.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max par fichier
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $nouveauStatut = $request->input('nouveau_statut');
        $commentaire = $request->input('commentaire');
        $documentsJustificatifs = [];

        // Gérer l'upload des documents justificatifs si fournis
        if ($request->hasFile('documents_justificatifs')) {
            $files = $request->file('documents_justificatifs');
            // S'assurer que c'est un tableau (peut être un seul fichier ou un tableau)
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    // Nettoyer le nom du fichier pour éviter les caractères spéciaux
                    $originalName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                    $filename = time() . '_' . $garantie->id . '_' . uniqid() . '_' . $originalName;
                    $path = $file->storeAs('garanties/justificatifs', $filename, 'public');
                    $documentsJustificatifs[] = $path;
                }
            }
        }

        // Stocker les chemins en JSON
        $documentJustificatif = !empty($documentsJustificatifs) ? json_encode($documentsJustificatifs) : null;

        // Vérifier que la transition est autorisée
        if (!$garantie->peutPasserA($nouveauStatut)) {
            return redirect()->back()
                ->with('error', 'Transition de statut non autorisée.');
        }

        // Vérifier les permissions selon le type de transition
        $user = Auth::user();
        
        // Seul le rôle juridique peut changer le statut des garanties (selon les règles de gestion)
        // Le rôle IT est également autorisé pour les besoins techniques
        if (!$user->isJuridique() && !$user->isIt()) {
            return redirect()->back()
                ->with('error', 'Vous n\'avez pas les permissions nécessaires. Seul le service juridique peut changer le statut des garanties.');
        }

        // Vérifier si cette transition nécessite spécifiquement le rôle juridique
        // Toutes les transitions sensibles (Contentieux, Réalisation, Mutation, Main levée, Vente) nécessitent le rôle juridique
        if ($garantie->transitionRequiertJuridique($nouveauStatut) && !$user->isJuridique() && !$user->isIt()) {
            return redirect()->back()
                ->with('error', 'Cette transition nécessite le rôle juridique. Seul le service juridique peut effectuer cette opération.');
        }

        // Changer le statut avec historique et document
        $success = $garantie->changerStatut($nouveauStatut, Auth::id(), $commentaire, $documentJustificatif);

        if (!$success) {
            return redirect()->back()
                ->with('error', 'Impossible de changer le statut de la garantie.');
        }

        return redirect()->route('garanties.show', $garantie)
            ->with('success', 'Statut de la garantie mis à jour avec succès !');
    }

    /**
     * Affiche la page de liaison garantie-contrats de prêts
     */
    public function liaison(Request $request)
    {
        $garantieId = $request->query('garantie_id');
        $garantie = null;
        $contratsPretsDisponibles = collect();
        $contratsPretsLies = collect();

        if ($garantieId) {
            $garantie = Garantie::with(['contratsPret' => function($query) {
                $query->withPivot('pourcentage_utilisation', 'montant_utilise', 'created_at');
            }, 'typeGarantie', 'garant', 'client'])->findOrFail($garantieId);

            // Calculer les montants
            $montantUtilise = $garantie->calculerMontantUtilise();
            $montantRestant = $garantie->calculerMontantRestant();

            // Récupérer les contrats de prêts liés
            $contratsPretsLies = $garantie->contratsPret;

            // Récupérer uniquement les contrats de prêts du client associé à la garantie
            $contratsPretsDisponibles = ContratPret::where('statut', 'actif')
                ->with('garanties')
                ->orderBy('numero_pret');
            
            // Filtrer par le matricule du client si un client est associé
            if ($garantie->client && $garantie->client->matricule) {
                $contratsPretsDisponibles->where('matricule_client', $garantie->client->matricule);
            } else {
                // Si aucune garantie n'a de client, retourner une collection vide
                $contratsPretsDisponibles = collect();
                
                // Filtrer les garanties disponibles
                $garantiesDisponibles = Garantie::with(['typeGarantie', 'garant', 'client'])
                    ->whereHas('client')
                    ->whereIn('statut', ['normal', 'contentieux', 'dation'])
                    ->orderBy('reference_unique', 'desc')
                    ->get(['id', 'reference_unique', 'nom', 'valeur_reelle', 'statut', 'type_garantie_id', 'garant_id', 'client_id'])
                    ->filter(function($g) {
                        return $g->calculerMontantRestant() > 0;
                    })
                    ->values();
                
                return Inertia::render('liaisons/Index', [
                    'garantie' => $garantie,
                    'garanties' => $garantiesDisponibles,
                    'contratsPretsDisponibles' => $contratsPretsDisponibles,
                    'contratsPretsLies' => $contratsPretsLies,
                    'warning' => 'Cette garantie n\'a pas de client associé. Veuillez d\'abord associer un client à la garantie.',
                ]);
            }
            
            $contratsPretsDisponibles = $contratsPretsDisponibles->get();

            // Passer les données supplémentaires
            $garantie->montant_utilise = $montantUtilise;
            $garantie->montant_restant = $montantRestant;
        }

        // Récupérer uniquement les garanties disponibles pour la liaison
        // (celles qui ont un client, un statut approprié, et du montant disponible)
        $garanties = Garantie::with(['typeGarantie', 'garant', 'client'])
            ->whereHas('client') // Uniquement les garanties avec un client associé
            ->whereIn('statut', ['normal', 'contentieux', 'dation']) // Statuts qui permettent la liaison
            ->orderBy('reference_unique', 'desc')
            ->get(['id', 'reference_unique', 'nom', 'valeur_reelle', 'statut', 'type_garantie_id', 'garant_id', 'client_id'])
            ->filter(function($garantie) {
                // Filtrer celles qui ont encore du montant disponible
                $montantRestant = $garantie->calculerMontantRestant();
                return $montantRestant > 0;
            })
            ->values(); // Réindexer la collection après le filtre

        return Inertia::render('liaisons/Index', [
            'garantie' => $garantie,
            'garanties' => $garanties,
            'contratsPretsDisponibles' => $contratsPretsDisponibles,
            'contratsPretsLies' => $contratsPretsLies,
        ]);
    }

    /**
     * Lie un contrat de prêt à une garantie
     */
    public function lierContratPret(Request $request, Garantie $garantie)
    {
        $validator = Validator::make($request->all(), [
            'contrat_pret_id' => 'required|exists:contrats_prets,id',
            'montant_utilise' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $contratPret = ContratPret::findOrFail($request->input('contrat_pret_id'));
        $montantUtilise = $request->input('montant_utilise');

        // Charger le client de la garantie
        $garantie->load('client');

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

        // Calculer le pourcentage automatiquement
        $pourcentage = ($garantie->valeur_reelle > 0) ? ($montantUtilise / $garantie->valeur_reelle) * 100 : 0;

        // Vérifier qu'il y a assez de montant restant
        $montantRestant = $garantie->calculerMontantRestant();
        if ($montantUtilise > $montantRestant) {
            return redirect()->back()
                ->with('error', 'Le montant à utiliser dépasse le montant restant disponible sur la garantie.');
        }

        // Vérifier que le montant du prêt ne dépasse pas la valeur pondérée de la garantie
        if ($contratPret->montant_accorde > $garantie->valeur_reelle) {
            return redirect()->back()
                ->with('error', 'Le montant du prêt (' . number_format($contratPret->montant_accorde, 0, ',', ' ') . ' FCFA) ne peut pas être supérieur à la valeur pondérée de la garantie (' . number_format($garantie->valeur_reelle, 0, ',', ' ') . ' FCFA).');
        }

        // Vérifier que la garantie n'est pas déjà liée à ce contrat
        if ($garantie->contratsPret()->where('contrats_prets.id', $contratPret->id)->exists()) {
            return redirect()->back()
                ->with('error', 'Cette garantie est déjà liée à ce contrat de prêt.');
        }

        // Lier la garantie au contrat
        DB::transaction(function() use ($garantie, $contratPret, $pourcentage, $montantUtilise) {
            $garantie->contratsPret()->attach($contratPret->id, [
                'pourcentage_utilisation' => $pourcentage,
                'montant_utilise' => $montantUtilise,
            ]);
        });

        return redirect()->route('garanties.liaison', ['garantie_id' => $garantie->id])
            ->with('success', 'Contrat de prêt lié à la garantie avec succès !');
    }

    /**
     * Délie un contrat de prêt d'une garantie
     */
    public function delierContratPret(Garantie $garantie, ContratPret $contratPret)
    {
        // Vérifier que la garantie est bien liée au contrat
        if (!$garantie->contratsPret()->where('contrats_prets.id', $contratPret->id)->exists()) {
            return redirect()->back()
                ->with('error', 'Cette garantie n\'est pas liée à ce contrat.');
        }

        // Délier la garantie
        $garantie->contratsPret()->detach($contratPret->id);

        return redirect()->route('garanties.liaison', ['garantie_id' => $garantie->id])
            ->with('success', 'Contrat de prêt délié de la garantie avec succès !');
    }
}