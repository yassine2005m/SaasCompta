@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="container-fluid px-0">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Liste des clients</h6>
            <div class="d-flex gap-2">
                <form action="{{ route('export.direct.sync') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-sync-alt me-1"></i> Synchroniser tout vers Sage
                    </button>
                </form>
                <form method="GET" action="{{ route('clients.index') }}" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Nom, email, CIN, ICE..." value="{{ request('search') }}">
                    <button class="btn btn-sm btn-primary" type="submit">Rechercher</button>
                </form>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Email</th>
                        <th>Telephone</th>
                        <th>Entreprise</th>
                        <th>Contrats</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $client->first_name }} {{ $client->last_name }}</div>
                                <small class="text-muted">CIN: {{ $client->cin }}</small>
                            </td>
                            <td>{{ $client->email }}</td>
                            <td>{{ $client->phone }}</td>
                            <td>{{ $client->company->company_name ?? '-' }}</td>
                            <td>{{ $client->contracts->count() }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('clients.show', $client->id) }}" class="btn btn-sm btn-outline-primary">Voir details</a>
                                    <form action="{{ route('clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client et toutes ses données associées (contrats, factures, etc.) ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun client trouve.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $clients->links() }}
        </div>
    </div>
</div>
@endsection
