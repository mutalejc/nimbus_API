<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * List clients with filtering, sorting, and pagination
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clientNum' => 'nullable|integer',
            'payroll' => 'nullable|string|max:50',
            'active' => 'nullable|boolean',
            'nameContains' => 'nullable|string|max:100',
            'clientNameContains' => 'nullable|string|max:100',
            'dateAddedFrom' => 'nullable|date',
            'dateAddedTo' => 'nullable|date',
            'lastTxFrom' => 'nullable|date',
            'lastTxTo' => 'nullable|date',
            'sort' => 'nullable|string|in:clientNum,-clientNum,dateAdded,-dateAdded,lastTDate,-lastTDate',
            'page' => 'nullable|integer|min:1',
            'pageSize' => 'nullable|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid parameters',
                'messages' => $validator->errors()
            ], 400);
        }

        try {
            $query = DB::connection('sqlsrv_api')
                ->table('dbtclients');

            // Apply filters
            if ($request->has('clientNum')) {
                $query->where('clientNum', $request->clientNum);
            }

            if ($request->has('payroll')) {
                $query->where('payroll', $request->payroll);
            }

            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            if ($request->has('nameContains')) {
                $query->where('name', 'like', '%' . $request->nameContains . '%');
            }

            if ($request->has('clientNameContains')) {
                $query->where('clientName', 'like', '%' . $request->clientNameContains . '%');
            }

            if ($request->has('dateAddedFrom')) {
                $query->where('dateAdded', '>=', $request->dateAddedFrom);
            }

            if ($request->has('dateAddedTo')) {
                $query->where('dateAdded', '<=', $request->dateAddedTo);
            }

            if ($request->has('lastTxFrom')) {
                $query->where('lastTDate', '>=', $request->lastTxFrom);
            }

            if ($request->has('lastTxTo')) {
                $query->where('lastTDate', '<=', $request->lastTxTo);
            }

            // Apply sorting
            $sort = $request->get('sort', 'clientNum');
            $direction = 'asc';
            
            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $sort = substr($sort, 1);
            }

            $query->orderBy($sort, $direction);

            // Get total count before pagination
            $total = $query->count();

            // Apply pagination
            $page = $request->get('page', 1);
            $pageSize = $request->get('pageSize', 50);
            
            $data = $query
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();

            return response()->json([
                'data' => $data,
                'meta' => [
                    'page' => (int) $page,
                    'pageSize' => (int) $pageSize,
                    'total' => $total,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database error',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while fetching data'
            ], 500);
        }
    }

    /**
     * Get a single client by clientNum
     */
    public function show(Request $request, $clientNum)
    {
        try {
            $client = DB::connection('sqlsrv')
                ->table('reporting.vw_clients_public')
                ->where('clientNum', $clientNum)
                ->first();

            if (!$client) {
                return response()->json([
                    'error' => 'Not found',
                    'message' => 'Client not found'
                ], 404);
            }

            return response()->json($client);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Database error',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while fetching data'
            ], 500);
        }
    }
}
