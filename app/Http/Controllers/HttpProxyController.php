<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HttpProxyController extends Controller
{
    public function index()
    {
        $accounts = \App\Models\HttpProxyAccount::where('user_id', auth()->id())->get();
        return view('http_proxy.index', compact('accounts'));
    }

    public function create()
    {
        return view('http_proxy.create');
    }

    public function store(Request $request)
    {
        // Placeholder for proxy creation
        return back()->withErrors(['msg' => 'Fitur pembuatan HTTP Proxy sedang dalam pengembangan.']);
    }
}
