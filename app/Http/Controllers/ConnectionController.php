<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $connections = Connection::with(['client', 'furnizor'])
            ->when(Auth::user()->isClient(), function ($query) {
                return $query->where('client_id', Auth::id());
            })
            ->when(Auth::user()->isSupplier(), function ($query) {
                return $query->where('furnizor_id', Auth::id());
            })
            ->paginate(10);

        return view('connections.index', compact('connections'));
    }

    public function create()
    {
        $this->authorize('create', Connection::class);

        $users = User::when(Auth::user()->isClient(), function ($query) {
            return $query->where('account_type', 'furnizor');
        })->when(Auth::user()->isSupplier(), function ($query) {
            return $query->where('account_type', 'client');
        })->get();

        return view('connections.create', compact('users'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Connection::class);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::findOrFail($validated['user_id']);

        if (Auth::user()->isClient()) {
            $connection = Connection::connect(Auth::id(), $user->id);
        } else {
            $connection = Connection::connect($user->id, Auth::id());
        }

        return redirect()->route('connections.index')
            ->with('success', 'Conexiunea a fost stabilită cu succes.');
    }

    public function destroy(Connection $connection)
    {
        $this->authorize('delete', $connection);

        if (Auth::user()->isClient()) {
            Connection::disconnect(Auth::id(), $connection->furnizor_id);
        } else {
            Connection::disconnect($connection->client_id, Auth::id());
        }

        return redirect()->route('connections.index')
            ->with('success', 'Conexiunea a fost ștearsă cu succes.');
    }
}
