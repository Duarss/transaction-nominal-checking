@extends('layouts.app', ['title' => $title])

@section('content')
    <div class="row">
        <div class="card px-1">
            @if($role === 'branch_admin' && isset($branchName) && $branchName)
                <div class="mb-2 mt-3">
                    <span class="badge bg-dark fs-6">
                        <strong>{{ $branchName }}</strong>
                    </span>
                </div>
            @endif
            <div class="card-body pt-2 pb-0 px-0">
                <div class="row mt-4">
                    <x-table use-datatable />
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    <x-modal id="creadit-modal" title="Tambah/Edit Toko">
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
    let dataStore

    $(function() {
        let scrollY = '74vh'
        const columns = [
            {
                title: "Toko",
                data: "store",
            },
            {
                title: "Alamat",
                data: "address",
            },
            {
                title: "Cabang",
                data: "branch",
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
            }
        ]

        table = dataTableInit({
            selector: "#table",
            title: "Daftar Master Toko",
            scrollY: scrollY,
            pageLength: 10,
            ajax: {
                url: "{{ route("datatables.master-store")}}",
                type: "POST",
                data: function(params) {

                },
            },
            columns: columns,
            btnDetails: false,
            btnActions: true,
            btnApprove: false,
            btnCheckList: false,
        })

        $(document).on('click', '.btn-edit', function() {
            let url = "{{ route("masterStore.show", ["store" => "-code-"]) }}".replace("-code-", $(this).data("code"))

            Swal.fire({
                title: "Lanjutkan?",
                text: "Ingin ubah data toko ini?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Ya, lanjutkan",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (!result.isConfirmed) return

                ajaxGet({
                    url: url,
                    loading: false,
                    successCallback: function(response) {
                        if (!response.success) return

                        dataStore = response.data

                        clearForm("#form-creadit")

                        if (dataStore) {
                            $("#form-creadit").attr("data-code", dataStore.code)

                            $("#store_name").val(dataStore.name)
                            $("#address").val(dataStore.address)
                        }

                        $("#creadit-modal").modal("show")
                    }
                })
            })

        })
    })
</script>
@endpush