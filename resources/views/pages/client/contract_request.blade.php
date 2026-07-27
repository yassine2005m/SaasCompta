@extends('layouts.app')

@section('title', 'Demande de Contrat')

@section('content')
<div class="container-fluid px-0">
    <div class="card">
        <div class="card-header">Soumettre une demande de contrat</div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">Veuillez corriger les champs en erreur.</div>
            @endif
            <form method="POST" action="{{ route('client.contract.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Type de client</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input client-type-toggle" type="radio" name="client_type" value="individual" id="individual_type" {{ old('client_type', $company ? 'company' : 'individual') === 'individual' ? 'checked' : '' }}>
                                <label class="form-check-label" for="individual_type">Particulier</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input client-type-toggle" type="radio" name="client_type" value="company" id="company_type" {{ old('client_type', $company ? 'company' : 'individual') === 'company' ? 'checked' : '' }}>
                                <label class="form-check-label" for="company_type">Entreprise</label>
                            </div>
                        </div>
                        @error('client_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Prenom</label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name', $client->first_name) }}" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom</label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name', $client->last_name) }}" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de naissance</label>
                        <input type="date" class="form-control @error('birth_date') is-invalid @enderror" name="birth_date" value="{{ old('birth_date', optional($client->birth_date)->format('Y-m-d')) }}" required>
                        @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Telephone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $client->phone) }}" required>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input class="form-control" value="{{ $client->email }}" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Adresse</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="2" required>{{ old('address', $client->address) }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mt-2" id="company_fields">
                    <div class="col-12"><h6 class="fw-bold mb-0">Informations entreprise</h6></div>
                    <div class="col-md-6">
                        <label class="form-label">Nom de l'entreprise</label>
                        <input type="text" class="form-control company-field @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name', $company->company_name ?? '') }}">
                        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">RC</label>
                        <input type="text" class="form-control company-field @error('rc') is-invalid @enderror" name="rc" value="{{ old('rc', $company->rc ?? '') }}">
                        @error('rc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ICE</label>
                        <input type="text" class="form-control company-field @error('ice') is-invalid @enderror" name="ice" value="{{ old('ice', $company->ice ?? '') }}">
                        @error('ice')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">IF</label>
                        <input type="text" class="form-control company-field @error('if') is-invalid @enderror" name="if" value="{{ old('if', $company->if ?? '') }}">
                        @error('if')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Activite</label>
                        <input type="text" class="form-control @error('activity') is-invalid @enderror" name="activity" value="{{ old('activity', $company->activity ?? '') }}">
                        @error('activity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Adresse siege social</label>
                        <input type="text" class="form-control @error('headquarters_address') is-invalid @enderror" name="headquarters_address" value="{{ old('headquarters_address', $company->headquarters_address ?? '') }}">
                        @error('headquarters_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">Type de contrat</label>
                        <select class="form-select @error('contract_type') is-invalid @enderror" name="contract_type" required>
                            <option value="">Selectionnez...</option>
                            <option value="Domiciliation" {{ old('contract_type') === 'Domiciliation' ? 'selected' : '' }}>Domiciliation</option>
                        </select>
                        @error('contract_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date de debut</label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" value="{{ old('start_date') }}" required id="startDate">
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date de fin</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" value="{{ old('end_date') }}" required id="endDate">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Copie CIN</label>
                        <input type="file" class="form-control @error('cin_file') is-invalid @enderror" name="cin_file">
                        @error('cin_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Certificat Negatif</label>
                        <input type="file" class="form-control @error('certificat_file') is-invalid @enderror" name="certificat_file">
                        @error('certificat_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button class="btn btn-primary btn-lg shadow-sm" type="submit">
                        <i class="fa-solid fa-paper-plane me-2"></i>Envoyer la demande de contrat
                    </button>
                    <p class="text-center text-muted small mt-2">
                        <i class="fa-solid fa-shield-halved me-1"></i> Vos données sont sécurisées et traitées conformément à la réglementation.
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleCompanyFields() {
        const isCompany = document.querySelector('input[name="client_type"]:checked')?.value === 'company';
        const companyFields = document.querySelectorAll('.company-field');
        document.getElementById('company_fields').style.display = isCompany ? 'flex' : 'none';

        companyFields.forEach((field) => {
            if (isCompany) {
                field.setAttribute('required', 'required');
            } else {
                field.removeAttribute('required');
            }
        });
    }

    document.querySelectorAll('.client-type-toggle').forEach((radio) => {
        radio.addEventListener('change', toggleCompanyFields);
    });
    function calculatePrice() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const preview = document.getElementById('pricePreview');

        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            
            if (endDate > startDate) {
                const months = (endDate.getFullYear() - startDate.getFullYear()) * 12 + (endDate.getMonth() - startDate.getMonth());
                const price = (months / 12) * 800;
                preview.textContent = price.toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' DH';
            } else {
                preview.textContent = '0.00 DH';
            }
        } else {
            preview.textContent = '0.00 DH';
        }
    }

    document.getElementById('startDate')?.addEventListener('change', calculatePrice);
    document.getElementById('endDate')?.addEventListener('change', calculatePrice);
    
    toggleCompanyFields();
</script>
@endpush
