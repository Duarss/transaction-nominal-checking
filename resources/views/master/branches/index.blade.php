@extends('layouts.app', ['title' => $title])

@section('content')
    <div class="row">
        <div class="card px-1">
        <div class="card-body pt-2 pb-0 px-0">
            <div class="row mt-4">
                <x-table use-datatable />
            </div>
        </div>
        </div>
    </div>
@endsection

@section('modal')
    <x-modal id="creadit-modal" title="Tambah/Edit Cabang">
        <x-form id="form-creadit">
            <div class="mb-3">
                <x-input type="text" id="store_name" name="store_name" label="Nama Toko" formId="form-creadit" required />
                <x-textarea id="address" name="address" label="Alamat" formId="form-creadit" required />
            </div>
        </x-form>

        @slot('footer')
            <x-button class="btn-primary btn-sm" id="btn-save" label="Simpan" />
        @endslot
    </x-modal>
@endsection

@push('js')
<script>
    const title = "{{ $title }}"
    let dataBranch

    $(function () {
        let scrollY = '74vh'
        const columns = [
            {
                title: "Cabang",
                data: "branch",
            },
            {
                title: "Alamat",
                data: "address",
            },
            {
                title: "Jumlah Toko",
                data: "stores_count",
                className: "text-center",
            },
            {
                title: "Kepala Admin Cabang",
                data: "branch_admin",
            },
            {
                title: "Dibuat Pada",
                data: "created_at",
                render: function(data, type, row) {
                    return data ? toShortDateTime(data) : '-';
                }
            },
            {
                title: "Pembaruan Terakhir",
                data: "updated_at",
                render: function(data, type, row) {
                    return data ? timeAgo(data) : '-';
                }
            },
        ]
        
        table = dataTableInit({
            selector: "#table",
            title: "Daftar Master Cabang",
            scrollY: scrollY,
            pageLength: 10,
            ajax: {
                url: "{{ route("datatables.master-branch") }}",
                type: "POST",
                data: function(params) {
                    
                },
            },
            columns: columns,
            btnDetails: true,
            btnActions: false,
            btnApprove: false,
            btnCheckList: false,
        })

        $(document).on('click', '.btn-details', function () {
            // Open modal to view the details datatable
            const code = $(this).data('code')
            
            if (code) {
                const routeTemplate = "{{ route('masterBranch.details', ["branch" => "-code-"]) }}"
                window.location.href = routeTemplate.replace("-code-", code)
            }
        })
    })
</script>
@endpush