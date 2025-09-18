<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Services\OnboardingProgressService;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    /**
     * Skip the current onboarding step
     */
    public function skipCurrentStep(Request $request)
    {
        try {
            $user = Admin::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if user is enterprise owner
            if (!OnboardingProgressService::isEnterpriseOwner($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only enterprise owners can manage onboarding'
                ], 403);
            }

            $skipped = OnboardingProgressService::skipCurrentStep($user);
            
            if ($skipped) {
                return response()->json([
                    'success' => true,
                    'message' => 'Step skipped successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot skip this step - it may be required or already completed'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a specific step as completed
     */
    public function markStepCompleted(Request $request)
    {
        try {
            $user = Admin::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Check if user is enterprise owner
            if (!OnboardingProgressService::isEnterpriseOwner($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only enterprise owners can manage onboarding'
                ], 403);
            }

            $stepKey = $request->input('step');
            
            if (!$stepKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Step key is required'
                ], 400);
            }

            $marked = OnboardingProgressService::markStepCompleted($stepKey, $user);
            
            if ($marked) {
                return response()->json([
                    'success' => true,
                    'message' => 'Step marked as completed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark step as completed'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}