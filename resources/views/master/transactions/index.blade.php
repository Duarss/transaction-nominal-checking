@extends('layouts.app', ['title' => $title])

@php
    $isBranchAdmin = auth()->user()->role === 'branch_admin';
    $isCompanyAdmin = auth()->user()->role === 'company_admin';
@endphp

@section('content')
    <div class="row">
        <div class="card px-1">
            <div class="card-body pt-2 pb-0 px-0">
                <div class="row mt-2 mb-2">
                    <div class="col-md-4">
                        @if($isBranchAdmin)
                            <x-select select2
                                id="filter-approval"
                                name="filter-approval"
                                label="Filter Status Approval"
                                :options="[]"
                            />
                        @elseif($isCompanyAdmin)
                            <x-select select2
                                id="filter-recheck"
                                name="filter-recheck"
                                label="Filter Status Pengecekan Ulang"
                                :options="[]"
                            />
                        @endif
                    </div>
                    <div class="col d-flex justify-content-end align-items-end">
                        <x-button id="btn-refresh"
                            class="btn-outline-secondary btn-icon btn-sm"
                            icon="bx bx-refresh"
                            label=""
                            title="Refresh"
                            aria-label="Refresh"
                            data-bs-toggle="tooltip" />
                    </div>
                </div>
                <div class="row mt-4">
                    <x-table use-datatable />
                </div>
            </div>
        </div>
    </div>
@endsection

@section('modal')
    <x-modal id="modal-approve-nominal" title="Validasi Nominal Transaksi">
        <x-form id="form-approve-nominal">
            <div class="mb-3">
                <x-input type="text" id="actual_nominal" name="actual_nominal" label="Masukkan nominal fisik yang Anda pegang" min="0" formId="form-approve-nominal" required />
                <x-input type="hidden" id="approve_code" name="approve_code" />
            </div>
        </x-form>

        @slot('footer')
            <x-button class="btn-warning btn-sm" id="btn-save-edit" label="Simpan" />
            <x-button class="btn-primary btn-sm" id="btn-validate" label="Validasi" />
        @endslot
    </x-modal>
@endsection

@push('js')
<script>
    const title = '{{ $title }}'
    let dataTransaction

    @if($isBranchAdmin)
    select2Init({
        selector: "#filter-approval",
        url: "{{ route("select2.filter-transactions-branch-admin") }}",
        method: "POST",
        placeholder: "--Pilih status approval--",
        allowClear: true,
    })

    // call after select2 is initialized
    setDefault()

    $(document).on('change select2:select select2:clear', '#filter-approval', function () {
        table.ajax.reload(null, true)
    })

    @elseif($isCompanyAdmin)
    select2Init({
        selector: "#filter-recheck",
        url: "{{ route("select2.filter-transactions-company-admin") }}",
        method: "POST",
        placeholder: "--Pilih status pengecekan ulang--",
        allowClear: true,
    })

    // call after select2 is initialized
    setDefault()

    $(document).on('change select2:select select2:clear', '#filter-recheck', function () {
        table.ajax.reload(null, true)
    })
    @endif

    @if($isBranchAdmin)
    function setDefault() {
        const $el = $('#filter-approval')
        if ($el.find('option[value="false"]').length === 0) {
            $el.append(new Option('Belum Disetujui', 'false', true, true))
        }
        $el.val('false').trigger('change')
    }
    @elseif($isCompanyAdmin)
    function setDefault() {
        const $el = $('#filter-recheck')
        if ($el.find('option[value="false"]').length === 0) {
            $el.append(new Option('Belum Dicek Ulang', 'false', true, true))
        }
        $el.val('false').trigger('change')
    }
    @endif

    $(function() {
        @if($isBranchAdmin)
        autoNumericInit({
            selector: "#actual_nominal",
            isCurrency: true,
            currencySymbol: 'Rp ',
        })
        @endif
        let scrollY = '74vh'
        const columns = [
            {
                title: "Doc ID",
                data: "doc_id",
            },
            {
                title: "Tanggal",
                data: "date",
                render: function(data, type, row) {
                    return data ? toShortDateTime(data) : '-';
                }
            },
            {
                title: "Kode Sales",
                data: "sales_code",
            },
            {
                title: "Kode Customer",
                data: "customer_code",
            },
            {
                title: "Total",
                data: "total",
                render: function(data, type, row) {
                    if (!data) return '-';
                    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(data);
                },
            },
            {
                title: "Nominal Dibayar",
                data: "paid_amount",
                render: function(data, type, row) {
                    if (!data) return '-';
                    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(data);
                },
            },
            {
                title: "Δ Status",
                data: null,
                className: "text-center",
                render: function(data, type, row) {
                    const total = Number(row.total || 0);
                    const paid = Number(row.paid_amount || 0);
                    let status = '';
                    let badge = '';
                    if (paid > total) {
                        status = 'OVERPAID';
                        badge = 'bg-danger';
                    } else if (paid < total) {
                        status = 'UNDERPAID';
                        badge = 'bg-warning';
                    } else {
                        status = 'SESUAI';
                        badge = 'bg-success';
                    }
                    return `<span class="badge ${badge}">${status}</span>`;
                }
            },
            {
                title: "Dibuat Oleh",
                data: "created_by",
            },
            {
                title: "Perubahan Oleh",
                data: "updated_by",
            },
        ]

        table = dataTableInit({
            selector: '#table',
            title: "Daftar Master Transaction",
            scrollY: scrollY,
            pageLength: 10,
            ajax: {
                url: "{{ route("datatables.master-transaction")}}",
                type: "POST",
                data: function(params) {
                    @if($isBranchAdmin)
                    const v = $('#filter-approval').val();
                    params.approval = (v === null || v === undefined || v === '') ? 'all' : v;
                    @elseif($isCompanyAdmin)
                    const v = $('#filter-recheck').val();
                    params.recheck = (v === null || v === undefined || v === '') ? 'all' : v;
                    @endif
                }
            },
            columns: columns,
            btnDetails: true,
            btnActions: false,
            btnApprove: true,
            btnCheckList: @if($isCompanyAdmin) true @else false @endif,
        })

        $(document).on('click', '.btn-details', function() {
            const code = $(this).data('code')

            if (code) {
                const routeTemplate = "{{ route("masterTransaction.details", ["transaction" => "-code-"]) }}"
                window.location.href = routeTemplate.replace('-code-', code)
            }
        })

        $(document).on('click', '.btn-approve', function() {
            const code = $(this).data('code')
            const total = table.row($(this).closest('tr')).data().total
            const paidAmount = table.row($(this).closest('tr')).data().paid_amount

            $("#approve_code").val(code) // Set when opening modal

            $("#modal-approve-nominal").modal('show')

            @if($isBranchAdmin)
            $("#modal-approve-nominal").on('shown.bs.modal', function () {
                autoNumericVal({
                    selector: "#actual_nominal",
                    value: paidAmount,
                })
                $("#actual_nominal").trigger('focus')
            })
            @endif

            // Remove previous handlers to avoid stacking
            $("#form-approve-nominal").off('submit')

            // "Simpan" button: update paid nominal, status "UPDATED"
            $("#btn-save-edit").off('click').on('click', function() {
                const actualNominal = AutoNumeric.getAutoNumericElement("#actual_nominal").getNumber()
                if (!actualNominal) {
                    Swal.fire("Gagal!", "Nominal fisik harus diisi.", "error")
                    return
                }
                let url = "{{ route("masterTransaction.update", ["transaction" => "-code-"]) }}".replace("-code-", code)
                
                Swal.fire({
                    title: "Lanjutkan?",
                    text: "Ingin ubah nominal transaksi " + code + " menjadi " + new Intl.NumberFormat('en-US', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(actualNominal) + "?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Ya, lanjutkan",
                    cancelButtonText: "Batalkan",
                }).then((result) => {
                    if (!result.isConfirmed) return

                    ajaxPost({
                        url: url,
                        formData: {
                            _method: "PUT",
                            action: "update",
                            actual_nominal: actualNominal,
                            doc_id: code
                        },
                        successCallback: function (response) {
                            if (response.success) {
                                Swal.fire("Berhasil!", "Transaksi " + code + " berhasil diupdate." || response.message, "success")
                                table.ajax.reload()
                            }
                            document.activeElement.blur(); // Remove focus from the close button or any focused element in the modal
                            $("#modal-approve-nominal").modal('hide')
                        },
                        errorCallback: function (response) {
                            Swal.fire("Gagal!", "Transaksi " + code + " gagal diupdate." || response.message, "error")
                            document.activeElement.blur(); // Remove focus from the close button or any focused element in the modal
                            $("#modal-approve-nominal").modal('hide')
                        }
                    })
                })
            })

            $("#btn-validate").off('click').on('click', function() {
                const actualNominal = AutoNumeric.getAutoNumericElement("#actual_nominal").getNumber()
                if (!actualNominal) {
                    Swal.fire("Gagal!", "Nominal fisik harus diisi.", "error")
                    return
                }
                let url = "{{ route("masterTransaction.approve", ["transaction" => "-code-"]) }}".replace("-code-", code)

                Swal.fire({
                    title: "Lanjutkan?",
                    text: "Ingin validasi transaksi " + code + " dengan nominal fisik " + new Intl.NumberFormat('en-US', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(actualNominal) + "?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Ya, lanjutkan",
                    cancelButtonText: "Batalkan",
                }).then((result) => {
                    if (!result.isConfirmed) return

                    ajaxPost({
                        url: url,
                        formData: {
                            action: "approve",
                            actual_nominal: actualNominal,
                            doc_id: code,
                        },
                        successCallback: function (response) {
                            if (response.success) {
                                Swal.fire("Berhasil!", "Transaksi " + code + " berhasil divalidasi." || response.message, "success")
                                table.ajax.reload()
                            }
                            $("#modal-approve-nominal").modal('hide')
                        },
                        errorCallback: function (response) {
                            Swal.fire("Gagal!", "Transaksi " + code + " gagal divalidasi." || response.message, "error")
                            $("#modal-approve-nominal").modal('hide')
                        }
                    })
                })
            })
        })

        $(document).on('click', '.btn-recheck', function () {
            const code  = $(this).data('code')
            const total = table.row($(this).closest('tr')).data().total
            const paidAmount = table.row($(this).closest('tr')).data().paid_amount
            const checkBoxState = $(this)

            if (!code) {
                Swal.fire("Gagal!", "Kode transaksi tidak ditemukan.", "error")
                return
            }

            // if (!checkBoxState.is(':checked')) {
            //     Swal.fire({
            //         title: "Batalkan Recheck?",
            //         text: `Yakin ingin membatalkan recheck pada transaksi ${code}?`,
            //         icon: "question",
            //         showCancelButton: true,
            //         confirmButtonText: "Ya, batalkan",
            //         cancelButtonText: "Jangan batalkan",
            //     }).then((result) => {
            //         if (!result.isConfirmed) {
            //             checkBoxState.prop('checked', true) // revert checkbox state
            //             return
            //         }

            //         let url = "{{ route("masterTransaction.unrecheck", ["transaction" => "-code-"]) }}".replace("-code-", code)

            //         ajaxPost({
            //             url: url,
            //             formData: {
            //                 action: "unrecheck",
            //                 actual_nominal: paidAmount,
            //                 doc_id: code,
            //             },
            //             successCallback: function (response) {
            //                 if (response.success) {
            //                     Swal.fire("Berhasil!", "Recheck pada transaksi " + code + " berhasil dibatalkan." || response.message, "success")
            //                     table.ajax.reload()
            //                 }
            //             },
            //             errorCallback: function (response) {
            //                 Swal.fire("Gagal!", "Recheck pada transaksi " + code + " gagal dibatalkan." || response.message, "error")
            //                 checkBoxState.prop('checked', true) // revert checkbox state
            //             }
            //         })
            //     })
            // } else {
            Swal.fire({
                title: "Recheck Transaksi?",
                text: `Yakin ingin menandai transaksi ${code} sebagai sudah dicek ulang?`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Ya, recheck",
                cancelButtonText: "Batalkan",
            }).then((result) => {
                if (!result.isConfirmed) {
                    checkBoxState.prop('checked', false) // revert checkbox state
                    return
                }

                let url = "{{ route("masterTransaction.recheck", ["transaction" => "-code-"]) }}".replace("-code-", code)

                ajaxPost({
                    url: url,
                    formData: {
                        action: "recheck",
                        actual_nominal: paidAmount,
                        doc_id: code,
                    },
                    successCallback: function (response) {
                        if (response.success) {
                            Swal.fire("Berhasil!", "Transaksi " + code + " berhasil ditandai sudah dicek ulang." || response.message, "success")
                            table.ajax.reload()
                            checkBoxState.prop('disabled', true) // disable checkbox after successful recheck
                        }
                    },
                    errorCallback: function (response) {
                        Swal.fire("Gagal!", "Transaksi " + code + " gagal ditandai sudah dicek ulang." || response.message, "error")
                        checkBoxState.prop('checked', false) // revert checkbox state
                    }
                })
            })
            // }
        })

        $(document).on('click', '#btn-refresh', function() {
            if (typeof table !== 'undefined') {
                table.ajax.reload()
                swalToast.fire({ title: 'Berhasil refresh tabel!', icon: 'success' })
            }
        })
    })
</script>
@endpush