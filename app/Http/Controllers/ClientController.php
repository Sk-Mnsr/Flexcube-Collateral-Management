<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ContratPret;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Liste tous les clients
     */
    public function index(Request $request)
    {
        $query = Client::query();

        // Filtre par recherche (matricule, nom, prénom, téléphone)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('matricule', 'like', "%{$search}%")
                  ->orWhere('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        // Compter les contrats de prêts pour chaque client
        $clients = $query->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Ajouter le nombre de contrats de prêts pour chaque client
        $clients->getCollection()->transform(function ($client) {
            $client->prets_count = ContratPret::where('matricule_client', $client->matricule)->count();
            return $client;
        });

        return Inertia::render('clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Affiche un client spécifique
     */
    public function show(Client $client)
    {
        // Récupérer les contrats de prêts associés au client via le matricule
        $contratsPrets = ContratPret::where('matricule_client', $client->matricule)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('clients/Show', [
            'client' => $client,
            'contratsPrets' => $contratsPrets,
        ]);
    }
}
