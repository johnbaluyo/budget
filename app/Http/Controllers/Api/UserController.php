<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function redirectToLogin()
    {
        return redirect('login');
    }

    public function getUser(Request $request)
    {
        return auth()->user();
    }
}
