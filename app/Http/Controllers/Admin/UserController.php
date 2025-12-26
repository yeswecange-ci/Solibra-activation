<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('village');

        // Filtres
        if ($request->has('village_id') && $request->village_id != '') {
            $query->where('village_id', $request->village_id);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Filtre par boisson (seulement si la colonne existe)
        if ($request->has('boisson_preferee') && $request->boisson_preferee != '') {
            try {
                $query->where('boisson_preferee', $request->boisson_preferee);
            } catch (\Exception $e) {
                // Ignorer si la colonne n'existe pas encore
            }
        }

        // Filtre par réponse quiz
        if ($request->has('quiz_answer') && $request->quiz_answer != '') {
            try {
                $query->where('quiz_answer', $request->quiz_answer);
            } catch (\Exception $e) {
                // Ignorer si la colonne n'existe pas encore
            }
        }

        // Filtre par acceptation des politiques
        if ($request->has('has_accepted_policies') && $request->has_accepted_policies != '') {
            try {
                if ($request->has_accepted_policies === 'yes') {
                    $query->whereNotNull('accepted_policies_at');
                } else {
                    $query->whereNull('accepted_policies_at');
                }
            } catch (\Exception $e) {
                // Ignorer si la colonne n'existe pas encore
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $villages = Village::where('is_active', true)->get();

        // Récupérer la liste des boissons préférées distinctes
        // Vérifier si la colonne existe avant de faire la requête
        try {
            $boissons = User::whereNotNull('boisson_preferee')
                ->distinct()
                ->pluck('boisson_preferee')
                ->sort();
        } catch (\Exception $e) {
            // Si la colonne n'existe pas encore (migration non exécutée), retourner une collection vide
            $boissons = collect([]);
        }

        // Récupérer les réponses quiz distinctes
        try {
            $quizAnswers = User::whereNotNull('quiz_answer')
                ->distinct()
                ->pluck('quiz_answer')
                ->sort();
        } catch (\Exception $e) {
            $quizAnswers = collect([]);
        }

        return view('admin.users.index', compact('users', 'villages', 'boissons', 'quizAnswers'));
    }

    public function show(User $user)
    {
        $user->load(['village', 'pronostics.match', 'prizes']);
        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Joueur supprimé avec succès !');
    }

    /**
     * Export CSV des utilisateurs avec filtres
     */
    public function export(Request $request)
    {
        $query = User::with('village');

        // Applique les mêmes filtres que la méthode index()
        if ($request->has('village_id') && $request->village_id != '') {
            $query->where('village_id', $request->village_id);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Filtre par boisson
        if ($request->has('boisson_preferee') && $request->boisson_preferee != '') {
            try {
                $query->where('boisson_preferee', $request->boisson_preferee);
            } catch (\Exception $e) {
                // Ignorer si la colonne n'existe pas encore
            }
        }

        // Filtre par réponse quiz
        if ($request->has('quiz_answer') && $request->quiz_answer != '') {
            try {
                $query->where('quiz_answer', $request->quiz_answer);
            } catch (\Exception $e) {
                // Ignorer si la colonne n'existe pas encore
            }
        }

        // Filtre par acceptation des politiques
        if ($request->has('has_accepted_policies') && $request->has_accepted_policies != '') {
            try {
                if ($request->has_accepted_policies === 'yes') {
                    $query->whereNotNull('accepted_policies_at');
                } else {
                    $query->whereNull('accepted_policies_at');
                }
            } catch (\Exception $e) {
                // Ignorer si la colonne n'existe pas encore
            }
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        $filename = 'users_filtered_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // En-têtes
            fputcsv($file, [
                'Nom',
                'Téléphone',
                'Village',
                'Boisson préférée',
                'Quiz FIF',
                'Politiques acceptées',
                'Date acceptation politiques',
                'Source Type',
                'Source Détail',
                'Statut inscription',
                'Date inscription',
                'Actif'
            ]);

            // Données
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->name,
                    $user->phone,
                    $user->village->name ?? '',
                    $user->boisson_preferee ?? '',
                    $user->quiz_answer ?? '',
                    $user->accepted_policies_at ? 'Oui' : 'Non',
                    $user->accepted_policies_at ? $user->accepted_policies_at->format('d/m/Y H:i') : '',
                    $user->source_type ?? '',
                    $user->source_detail ?? '',
                    $user->registration_status ?? '',
                    $user->created_at->format('d/m/Y H:i'),
                    $user->is_active ? 'Oui' : 'Non',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
