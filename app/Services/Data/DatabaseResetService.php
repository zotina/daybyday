<?php

namespace App\Services\Data;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseResetService
{
    public function resetAllData()
    {
        try {
            DB::beginTransaction();
            
            $this->deleteAll();
            $this->deleteDepartmentUserWithoutRoles();
            $this->deleteUsersWithoutRoles();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Database reset failed: ' . $e->getMessage());
            throw $e;
        }
    }

    
    private function deleteAll()
    {
        $tables = [
            'password_resets',
            'notifications',
            'payments',
            'invoice_lines',
            'appointments',
            'comments',
            'documents',
            'mails',
            'absences',
            'tasks',
            'projects',
            'leads',
            'invoices',
            'offers',
            'contacts',
            'clients',
            'activities',
            'products',
            'subscriptions',
            'integrations',  
        ];

        foreach ($tables as $table) {
            DB::table($table)->delete(); 
        }
    }

    
    private function deleteUsersWithoutRoles()
    {
        DB::table('users')->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->whereRaw('role_user.user_id = users.id')
                ->whereIn('roles.name', ['administrator', 'manager']);
        })->delete();
    }

    
    private function deleteDepartmentUserWithoutRoles()
    {
        DB::table('department_user')->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->whereRaw('role_user.user_id = department_user.user_id')
                ->whereIn('roles.name', ['administrator', 'manager']);
        })->delete();
    }
}