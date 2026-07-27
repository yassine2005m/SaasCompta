<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class AdminClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $clients = Client::with(['company', 'contracts'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('cin', 'like', '%' . $search . '%')
                        ->orWhereHas('company', function ($companyQuery) use ($search) {
                            $companyQuery->where('company_name', 'like', '%' . $search . '%')
                                ->orWhere('ice', 'like', '%' . $search . '%')
                                ->orWhere('rc', 'like', '%' . $search . '%')
                                ->orWhere('rce', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.clients.index', compact('clients'));
    }

    public function show($id)
    {
        $client = Client::with(['company', 'contracts.invoice'])->findOrFail($id);

        return view('pages.clients.show', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        
        $validated = $request->validate([
            'sage_custom_id' => 'nullable|string|max:50',
        ]);

        $client->update($validated);

        return redirect()->back()->with('success', 'ID Sage mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client supprimé avec succès.');
    }
}
