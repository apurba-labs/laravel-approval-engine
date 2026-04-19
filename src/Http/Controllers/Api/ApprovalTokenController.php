<?php

namespace ApurbaLabs\ApprovalEngine\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ApurbaLabs\ApprovalEngine\Services\ApprovalTokenService;
use ApurbaLabs\ApprovalEngine\Services\WorkflowManager;
use ApurbaLabs\ApprovalEngine\Exceptions\UnauthorizedApprovalException;
use ApurbaLabs\ApprovalEngine\Exceptions\InvalidApprovalException;

class ApprovalTokenController extends Controller
{
    public function approve(Request $request)
    {
        try {
            $tokenService = app(ApprovalTokenService::class);
            $manager = app(WorkflowManager::class);

            $tokenValue = $request->input('token');

            if (!$tokenValue) {
                throw new InvalidApprovalException('Token is required');
            }

            $token = $tokenService->validate($tokenValue);

            $workflow = $token->workflow;

            $manager->approve($workflow->id, $token->user_id);

            $tokenService->markUsed($token);

            return response()->json([
                'message' => 'Approved successfully'
            ]);

        } catch (UnauthorizedApprovalException $e) {
            return response()->json(['error' => $e->getMessage()], 403);

        } catch (InvalidApprovalException $e) {
            return response()->json(['error' => $e->getMessage()], 422);

        } catch (\Throwable $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}