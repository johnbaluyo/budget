<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function redirectToLogin()
    {
        return redirect('login');
    }
}
