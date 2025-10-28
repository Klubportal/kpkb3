<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class CentralHomeController extends Controller
{
    public function index()
    {
        // Use KB template for central home
        return view('templates.kb.home');
    }

    public function register()
    {
        return view('templates.kb.register');
    }
}
