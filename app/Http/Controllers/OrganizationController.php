<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organizations = Organization::active()->paginate(10);
        return view('organizations.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('organizations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'teccom_id' => 'nullable|string|unique:organizations,teccom_id',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:2',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'vat_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'type' => 'required|in:supplier,customer',
        ]);

        $organization = Organization::create($validated);

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Organizația a fost creată cu succes.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        return view('organizations.show', compact('organization'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Organization $organization)
    {
        return view('organizations.edit', compact('organization'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'teccom_id' => 'nullable|string|unique:organizations,teccom_id,' . $organization->id,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:2',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'vat_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'type' => 'required|in:supplier,customer',
        ]);

        $organization->update($validated);

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Organizația a fost actualizată cu succes.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        // Verificăm dacă organizația are comenzi sau produse
        if ($organization->customerOrders()->exists() || 
            $organization->supplierOrders()->exists() || 
            $organization->products()->exists()) {
            return redirect()
                ->route('organizations.index')
                ->with('error', 'Organizația nu poate fi ștearsă deoarece are comenzi sau produse asociate.');
        }

        $organization->delete();

        return redirect()
            ->route('organizations.index')
            ->with('success', 'Organizația a fost ștearsă cu succes.');
    }

    public function suppliers()
    {
        $suppliers = Organization::suppliers()->active()->paginate(10);
        return view('organizations.suppliers', compact('suppliers'));
    }

    public function customers()
    {
        $customers = Organization::customers()->active()->paginate(10);
        return view('organizations.customers', compact('customers'));
    }
}
