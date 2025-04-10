<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function list()
    {
        $users = User::all();
        return view('admin.staff.list', compact('users'));
    }
}
