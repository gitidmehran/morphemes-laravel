<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * @OA\Get(
     *    path="/api/version-info",
     *    operationId="versionInfo",
     *    tags={"Server Info"},
     *    summary="Get Server Version Info",
     *    description="Get Server Version Info",
     *    @OA\Response(
     *          response=200, description="Success",
     *          @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example="true"),
     *             @OA\Property(property="message", type="string", example="Server Version Information Retrieved Successfully"),
     *             @OA\Property(property="serverVersion", type="string", example="0.0.1"),
     *             @OA\Property(property="lastReleaseDate", type="date", example="2023-08-22"),
     *          )
     *       ),
     *     @OA\Response(
     *       response=500, description="Error",
     *       @OA\JsonContent(
     *          @OA\Property(property="success", type="boolean", example="false"),
     *          @OA\Property(property="message", type="string", example="Something Went Wrong."),
     *       )
     *     ) 
     *  )
     */

    public function index(Request $request){
        try {
            $changelogPath = base_path('CHANGELOG.md');
            $changelogContent = file_get_contents($changelogPath);
            // Version Extraction
            preg_match('/\d+\.\d+\.\d+/', $changelogContent, $matches);
            $version = @$matches[0]?? '';

            // Date Extraction
            preg_match('/\d{4}-\d{2}-\d{2}/', $changelogContent, $matches);
            $date = @$matches[0] ?? '';
            $response = [
                'success' => true,
                'message' => 'Server Version Information Retrieved Successfully',
                'serverVersion' => $version,
                'lastReleaseDate' => $date
            ];
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
        return response()->json($response, 200);
    }
}
