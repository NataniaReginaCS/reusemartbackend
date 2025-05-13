<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role; 

class RoleController extends Controller
{
    public function fetchRoles()
    {
        try {
            $roles = Role::all();
            return response()->json([
                'status' => 'success',
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch roles: ' . $e->getMessage()
            ], 500);
        }
    }
}
