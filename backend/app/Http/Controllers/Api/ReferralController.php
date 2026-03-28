<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred:id,name,email')
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total' => $referrals->count(),
            'qualified' => $referrals->whereIn('status', ['qualified', 'rewarded'])->count(),
            'rewarded' => $referrals->where('status', 'rewarded')->count(),
            'total_reward_months' => $referrals->where('status', 'rewarded')->sum('reward_months'),
        ];

        return response()->json([
            'referral_code' => $user->referral_code,
            'referral_link' => url("/register?ref={$user->referral_code}"),
            'referrals' => $referrals,
            'stats' => $stats,
        ]);
    }

    public function applyCode(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|size:8',
        ]);

        $user = $request->user();

        // Can't refer yourself
        if ($user->referral_code === $request->referral_code) {
            return response()->json(['message' => 'Vous ne pouvez pas utiliser votre propre code.'], 422);
        }

        // Already referred
        if ($user->referred_by) {
            return response()->json(['message' => 'Vous avez déjà utilisé un code de parrainage.'], 422);
        }

        // Check existing referral record
        if (Referral::where('referred_id', $user->id)->exists()) {
            return response()->json(['message' => 'Vous avez déjà été parrainé.'], 422);
        }

        $referrer = User::where('referral_code', $request->referral_code)->first();
        if (!$referrer) {
            return response()->json(['message' => 'Code de parrainage invalide.'], 404);
        }

        $user->update(['referred_by' => $referrer->id]);

        Referral::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $user->id,
            'status' => 'pending',
            'reward_months' => 1,
        ]);

        return response()->json(['message' => 'Code de parrainage appliqué ! Vous et votre parrain recevrez 1 mois gratuit après votre premier abonnement.']);
    }

    public function qualify(Request $request)
    {
        // Called internally when a referred user subscribes for the first time
        $user = $request->user();

        $referral = Referral::where('referred_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$referral) {
            return response()->json(['message' => 'Aucun parrainage en attente.'], 404);
        }

        $referral->update([
            'status' => 'qualified',
            'qualified_at' => now(),
        ]);

        return response()->json(['message' => 'Parrainage qualifié.']);
    }
}
