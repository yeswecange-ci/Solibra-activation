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
}
