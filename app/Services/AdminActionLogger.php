<?php

namespace App\Services;

use App\Models\AdminAction;
use Illuminate\Support\Facades\Auth;

class AdminActionLogger
{
    public static function log($actionType, $description, $targetStaffId = null)
    {
        AdminAction::create([
            'action_type' => $actionType,
            'description' => $description,
            'admin_id' => Auth::user()->admin->admin_id,
            'target_staff_id' => $targetStaffId,
        ]);
    }
}