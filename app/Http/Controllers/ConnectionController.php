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
        $connections = Connection::with(['client', 'supplier'])
            ->when(Auth::user()->isClient(), function ($query) {
                return $query->where('client_id', Auth::id());
            })
            ->when(Auth::user()->isSupplier(), function ($query) {
                return $query->where('supplier_id', Auth::id());
            })
            ->paginate(10);

        return view('connections.index', compact('connections'));
    }

    public function create()
    {
        $this->authorize('create', Connection::class);

        $users = User::when(Auth::user()->isClient(), function ($query) {
            return $query->where('role', 'supplier');
        })->when(Auth::user()->isSupplier(), function ($query) {
            return $query->where('role', 'client');
        })->get();

        return view('connections.create', compact('users'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Connection::class);

        $validated = $request->validate([
            'connect_id' => 'required|exists:users,connect_id'
        ]);

        $user = User::where('connect_id', $validated['connect_id'])->firstOrFail();

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
            Connection::disconnect(Auth::id(), $connection->supplier_id);
        } else {
            Connection::disconnect($connection->client_id, Auth::id());
        }

        return redirect()->route('connections.index')
            ->with('success', 'Conexiunea a fost ștearsă cu succes.');
    }

    public function updateStatus(Request $request, Connection $connection)
    {
        $this->authorize('update', $connection);

        if (!Auth::user()->isSupplier()) {
            return redirect()->route('connections.index')
                ->with('error', 'Doar furnizorii pot actualiza statusul conexiunilor.');
        }

        $validated = $request->validate([
            'is_active' => 'required|boolean'
        ]);

        if ($validated['is_active']) {
            $connection->activate();
            $message = 'Conexiunea a fost activată cu succes.';
        } else {
            $connection->deactivate();
            $message = 'Conexiunea a fost dezactivată cu succes.';
        }

        return redirect()->route('connections.index')
            ->with('success', $message);
    }
}
