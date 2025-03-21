<?php

namespace App\Http\Controllers;

use App\Services\Data\DatabaseResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DataController extends Controller
{
    protected $databaseResetService;

    public function __construct(DatabaseResetService $databaseResetService)
    {
        $this->databaseResetService = $databaseResetService;
    }

    public function resetDataView()
    {
        Log::info('Accès à la vue de réinitialisation des données', ['user_id' => Auth::id()]);
        return view('data.reset');
    }

    public function resetData(Request $request)
    {
        Log::info('Tentative de réinitialisation des données', ['user_id' => Auth::id()]);

        $request->validate([
            'password' => 'required',
        ]);

        $user = Auth::user();

        if (!$user) {
            Log::warning('Utilisateur non authentifié');
            return redirect()->back()->with('error', __('You must be logged in to perform this action.'));
        }

        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Échec de réinitialisation : mot de passe incorrect', ['user_id' => $user->id]);
            return redirect()->back()->with('error', __('Password is incorrect'));
        }

        if (!$user->hasRole('administrator')) {
            Log::warning('Échec de réinitialisation : permission refusée', ['user_id' => $user->id]);
            return redirect()->back()->with('error', __('You do not have permission to reset data'));
        }

        try {
            Log::info('Réinitialisation en cours par l\'administrateur', ['user_id' => $user->id]);

            $this->databaseResetService->resetAllData();

            activity()
                ->causedBy($user)
                ->withProperties(['action' => 'data_reset'])
                ->log('All application data has been reset');

            Log::info('Réinitialisation des données réussie', ['user_id' => $user->id]);
            
            return redirect()->back()->with('success', __('All data has been successfully reset'));

        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation des données', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('Data reset failed: ') . $e->getMessage());
        }
    }
}
