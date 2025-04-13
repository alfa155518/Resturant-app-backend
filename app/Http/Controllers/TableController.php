<?php

namespace App\Http\Controllers;

use App\Helpers\ValidateId;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TableController extends Controller
{
    // all Tables
    public function tables()
    {
        try {
            // Get all tables with optimized query
            $allTable = Table::query()
                ->orderBy('table_number')
                ->paginate(10);

            // Validate pagination parameters to prevent tampering
            $page = request()->input('page', 1);
            $notValidId = ValidateId::validateNumeric($page);
            if ($notValidId) {
                return $notValidId;
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'current_page' => $allTable->currentPage(),
                    'total' => $allTable->total(),
                    'tables' => $allTable->items(),
                    'per_page' => $allTable->perPage(),
                    'last_page' => $allTable->lastPage(),
                ]
            ], 200)->header('Cache-Control', 'public, max-age=60')
                ->header('X-Content-Type-Options', 'nosniff')
                ->header('X-XSS-Protection', '1; mode=block')
                ->header('X-Frame-Options', 'DENY');

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Tables retrieval error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'failed to retrieve tables'
            ], 500);
        }
    }

    // Single table
    public function singleTable($id)
    {
        try {
            // Validate the ID parameter
            $notValidId = ValidateId::validateNumeric($id);
            if ($notValidId) {
                return $notValidId;
            }
            
            $table = Table::find($id);
            // Check if the table exists
            if (!$table) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Table not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $table
            ], 200)
                ->header('Cache-Control', 'public, max-age=300')
                ->header('X-Content-Type-Options', 'nosniff')
                ->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
                ->header('Content-Security-Policy', "default-src 'self'");
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Table retrieval error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'failed to retrieve table'
            ], 500);
        }
    }
}
