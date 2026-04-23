@extends('layouts.masterVertical')

@push('content')
    <x-breadcrumb :items="$breadcrumbs" />

    <div class="row">
        <div class="card" style="padding-inline: 0; border-radius: 18px 18px 9px 9px;">
            <form action="{{ route('newspaperPartner.store') }}" method="POST" class="card-body" style="padding: 0;">
                @csrf
                <div class="form-header flex w-full">
                    <h5 class="h5-reset-margin">New newspaper partner</h5>
                </div>

                <div class="form-padding">
                    <p class="text-muted small mx-5 mb-3">
                        Har newspaper ke liye jis ke active partners hon, un shares ka majmoo <strong>100%</strong> hona chahiye.
                        Pehle us newspaper ke liye <code>media_bank_details</code> mein alag accounts banayen (agar pehle se nahi).
                    </p>

                    <div class="row mb-3 mx-5">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="newspaper_id">Newspaper</label>
                            <select name="newspaper_id" id="newspaper_id" class="form-select" required>
                                <option value="">— Select —</option>
                                @foreach ($newspapers as $n)
                                    <option value="{{ $n->id }}" @selected(old('newspaper_id') == $n->id)>{{ $n->title }}</option>
                                @endforeach
                            </select>
                            @error('newspaper_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="partner_name">Partner name</label>
                            <input type="text" name="partner_name" id="partner_name" class="form-control" required
                                value="{{ old('partner_name') }}" placeholder="e.g. Main account, Partner B" />
                            @error('partner_name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3 mx-5">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="share_percentage">Share %</label>
                            <input type="number" name="share_percentage" id="share_percentage" class="form-control" required
                                step="0.01" min="0.01" max="100" value="{{ old('share_percentage') }}"
                                placeholder="e.g. 75" />
                            @error('share_percentage')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="media_bank_detail_id">Bank account</label>
                            <select name="media_bank_detail_id" id="media_bank_detail_id" class="form-select" required>
                                <option value="">— Pehle newspaper select karen —</option>
                            </select>
                            @error('media_bank_detail_id')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3 mx-5">
                        <div class="col-sm-6">
                            <label class="col-form-label text-sm-end" for="sort_order">Sort order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" min="0"
                                value="{{ old('sort_order', 0) }}" />
                        </div>
                        <div class="col-sm-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                    @checked(old('is_active', true))>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="buttons-div flex">
                    <button type="submit" class="custom-primary-button">Save</button>
                    <a href="{{ route('master.newspaperPartner.index') }}" class="custom-secondary-button">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const np = document.getElementById('newspaper_id');
            const bank = document.getElementById('media_bank_detail_id');

            function loadBanks(newspaperId) {
                bank.innerHTML = '<option value="">Loading...</option>';
                if (!newspaperId) {
                    bank.innerHTML = '<option value="">— Pehle newspaper select karen —</option>';
                    return;
                }
                fetch('{{ route('master.newspaperPartner.banks') }}?newspaper_id=' + encodeURIComponent(newspaperId), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        bank.innerHTML = '<option value="">— Select bank account —</option>';
                        (data.banks || []).forEach(function(b) {
                            const opt = document.createElement('option');
                            opt.value = b.id;
                            opt.textContent = (b.bank_name || '') + ' — ' + (b.account_number || '') + ' (' + (b
                                .account_title || '') + ')';
                            bank.appendChild(opt);
                        });
                    })
                    .catch(() => {
                        bank.innerHTML = '<option value="">Error loading banks</option>';
                    });
            }

            np.addEventListener('change', function() {
                loadBanks(this.value);
            });
            if (np.value) loadBanks(np.value);
        });
    </script>
@endpush
