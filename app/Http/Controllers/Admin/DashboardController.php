<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        $apiKey = ApiKey::where('is_active', true)->first();
        
        // Get available tables from MSSQL
        $tables = $this->getAvailableTables();
        
        return view('admin.dashboard', [
            'apiKey' => $apiKey,
            'tables' => $tables,
            'baseUrl' => url('/api/v1'),
        ]);
    }

    /**
     * Generate a new API key
     */
    public function generateApiKey()
    {
        // Deactivate all existing keys
        ApiKey::query()->update(['is_active' => false]);

        // Generate new key
        $result = ApiKey::generate();

        return redirect()->route('admin.dashboard')
            ->with('success', 'API key generated successfully')
            ->with('plain_key', $result['plain_key']);
    }

    /**
     * Regenerate the API key
     */
    public function regenerateApiKey()
    {
        return $this->generateApiKey();
    }

    /**
     * Disable the current API key
     */
    public function disableApiKey()
    {
        ApiKey::where('is_active', true)->update(['is_active' => false]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'API key disabled successfully');
    }

    /**
     * Get available tables from MSSQL
     */
    private function getAvailableTables()
    {
        try {
            // For now, return the hardcoded view
            return [
                [
                    'name' => 'reporting.vw_clients_public',
                    'endpoint' => '/clients',
                    'description' => 'Read-only access to client data with masked PII',
                ]
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get fields for a specific table
     */
    public function getTableFields(Request $request)
    {
        try {
            $fields = DB::connection('sqlsrv')
                ->select("
                    SELECT 
                        COLUMN_NAME as name,
                        DATA_TYPE as type,
                        IS_NULLABLE as nullable
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = 'reporting' 
                    AND TABLE_NAME = 'vw_clients_public'
                    ORDER BY ORDINAL_POSITION
                ");

            return response()->json(['fields' => $fields]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
