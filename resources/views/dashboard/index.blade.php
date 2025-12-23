@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    {{-- Header + tools --}}
    <div class="d-flex align-items-center justify-content-between px-2 pt-3">
        <div>
            <h4 class="mb-0">Transaction Nominal Check</h4>
            <small class="text-muted">Monitor Pembayaran Sistem vs. Manual</small>
        </div>
        <div class="d-flex gap-2">
            <x-button id="btn-refresh"
                    class="btn-outline-secondary btn-icon btn-sm"
                    icon="bx bx-refresh"
                    label=""
                    title="Refresh"
                    aria-label="Refresh"
                    data-bs-toggle="tooltip" />
        </div>
    </div>

    @if($role === 'branch_admin' && isset($branchName) && $branchName)
        <div class="mb-2 mt-3">
            <span class="badge bg-dark fs-6">
                <strong>{{ $branchName }}</strong>
            </span>
        </div>
    @endif

    {{-- Summary cards --}}
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-3 g-3 mt-3">
        <div class="col">
            <div id="sum-total" class="h-100 shadow-sm card p-3 d-flex flex-column align-items-start justify-content-center">
                <div class="d-flex align-items-center mb-2">
                    <i class="bx bx-wallet text-primary fs-2 me-2"></i>
                    <div>
                        <div class="text-primary fw-bold">Total Nominal Transaksi</div>
                        <div class="text-muted small">Sepanjang Waktu</div>
                    </div>
                </div>
                <span class="h4">0</span>
                <span class="text-muted">IDR</span>
            </div>
        </div>
        <div class="col">
            <div id="sum-disc" class="h-100 shadow-sm card p-3 d-flex flex-column align-items-start justify-content-center">
                <div class="d-flex align-items-center mb-2">
                    <i class="bx bx-error-circle text-danger fs-2 me-2"></i>
                    <div>
                        <div class="text-danger fw-bold">Data Transaksi Selisih</div>
                        <div class="text-muted small">Dokumen Transaksi Dengan Δ≠0</div>
                    </div>
                </div>
                <span class="h4">0</span>
                <span class="text-muted">Tx</span>
            </div>
        </div>
        <div class="col">
            <div id="sum-today" class="h-100 shadow-sm card p-3 d-flex flex-column align-items-start justify-content-center">
                <div class="d-flex align-items-center mb-2">
                    <i class="bx bx-calendar text-info fs-2 me-2"></i>
                    <div>
                        <div class="text-info fw-bold">Transaksi Hari Ini</div>
                        <div class="text-muted small">Jumlah</div>
                    </div>
                </div>
                <span class="h4">0</span>
                <span class="text-muted">Tx</span>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            {{-- Use the table component with use-datatable --}}
            <x-table use-datatable id="trx-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Doc ID</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Sales</th>
                        <th>Total</th>
                        <th>Terbayar</th>
                        <th>Δ</th>
                        <th>Metode Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Data will be loaded via AJAX --}}
                </tbody>
            </x-table>
        </div>
    </div> 
@endsection

@section('modal')
    {{-- Detail modal (shows detail_transactions rows) --}}
    <x-modal id="modal-detail" title="Transaction Detail">
        <div class="mb-2 d-flex justify-content-between">
            <div>
                <strong id="d-doc">#</strong><br>
                <small class="text-muted" id="d-date">-</small>
            </div>
            <span class="badge bg-secondary" id="d-status">-</span>
        </div>

        <div class="row g-2 mb-2">
            <div class="col">
                <small class="text-muted d-block">Cabang</small>
                <div id="d-branch">-</div>
            </div>
            <div class="col">
                <small class="text-muted d-block">Sales</small>
                <div id="d-sales">-</div>
            </div>
            <div class="col">
                <small class="text-muted d-block">Pelanggan</small>
                <div id="d-customer">-</div>
            </div>
        </div>

        <div class="border rounded p-2 mb-2">
            <div class="d-flex justify-content-between"><span>Total</span> <strong id="d-total">IDR 0</strong></div>
            <div class="d-flex justify-content-between"><span>Terbayar</span>  <strong id="d-paid">IDR 0</strong></div>
            <hr class="my-2">
            <div class="d-flex justify-content-between"><span>Selisih (Terbayar - Total)</span> <strong id="d-disc" class="text-danger">IDR 0</strong></div>
        </div>

        <div class="mb-2">
            <small class="text-muted d-block">Rincian</small>
        </div>

        {{-- Detail rows --}}
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tipe Pembayaran</th>
                        <th>Nominal</th>
                        <th>Bank</th>
                        <th>Bank Doc</th>
                        <th>Bank Due</th>
                    </tr>
                </thead>
                <tbody id="d-rows"><tr><td colspan="6" class="text-muted">Data Belum Ada</td></tr></tbody>
            </table>
        </div>
    </x-modal>
@endsection

@push('js')
<script>
    $(function() {
        const fmtIDR = (n) => new Intl.NumberFormat('en-US',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(Number(n||0))

        function refreshSummary() {
            ajaxGet({
                url: '{{ route('dashboard.summary') }}',
                loading: true,
                successCallback: (s) => {
                    $('#sum-total .h4').text(fmtIDR(s.total_amount ?? 0))
                    $('#sum-disc .h4').text(s.discrepancies ?? 0)
                    $('#sum-today .h4').text(s.today_count ?? 0)
                }
            })
        }

        // Initialize DataTable - DataTables is already loaded by the table component
        let trxTable = dataTableInit({
            selector: '#trx-table',
            title: 'Transaction Nominal',
            pageLength: 10,
            btnTools: true, 
            btnExcel: false, 
            btnPdf: false,
            rowIndex: true,
            order: [[3, 'desc']], // Order by date (4th column, 0-indexed)
            columns: [
                { data: 'doc_id', title: 'Doc ID' },
                { data: 'customer_name', title: 'Pelanggan' },
                { 
                    data: 'date', title: 'Tanggal', 
                    render: function(d) {
                        if (!d) return '-'
                        const dateObj = new Date(d)
                        const year = dateObj.getFullYear()
                        const month = dateObj.toLocaleString('en-US', { month: 'short' })
                        const day = String(dateObj.getDate()).padStart(2, '0')
                        return `${day} ${month} ${year}`
                    }
                },
                { data: 'sales_name', title: 'Sales' },
                { 
                    data: 'total', title: 'Total', 
                    render: function(d) {
                        return d ? fmtIDR(d) : '-'
                    }
                },
                { 
                    data: 'paid_amount', title: 'Terbayar', 
                    render: function(d) {
                        return d ? fmtIDR(d) : '-'
                    }
                },
                { 
                    data: 'discrepancy', title: 'Δ', 
                    render: function(d) {
                        const v = Number(d||0)
                        const c = v===0?'text-success':(v>0?'text-danger':'text-warning')
                        return `<span class="${c}">${fmtIDR(v)}</span>`
                    }
                },
                { data: 'method', title: 'Metode Pembayaran' },
            ],
            ajax: {
                url: '{{ route('datatables.transactions.nominal.list') }}'
            },
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: 'transaksi-' + new Date().toISOString().slice(0, 10).replace(/-/g, '-'), // Dynamic date
                    className: 'btn-sm btn-outline-secondary',
                    text: "<i class='bx bx-spreadsheet' style='color: #1d6f42'></i>",
                    titleAttr: 'Export Excel',
                    exportOptions: {
                        columns: ':not(.no-export)'
                    },
                    // Customize filename
                    filename: function() {
                        const now = new Date();
                        const year = now.getFullYear();
                        const month = String(now.getMonth() + 1).padStart(2, '0');
                        const day = String(now.getDate()).padStart(2, '0');
                        const hours = String(now.getHours()).padStart(2, '0');
                        const minutes = String(now.getMinutes()).padStart(2, '0');
                        return `transaksi-${year}-${month}-${day}-${hours}-${minutes}`;
                    }
                }
            ],
            btnDetails: false,
            btnActions: false,
            btnApprove: false,
            btnCheckList: false,
        })

        // Open modal on row click
        $('#trx-table').on('click', 'tbody tr', function() {
            const rowData = trxTable.row(this).data()
            if (rowData) openDetail(rowData)
        })

        function computeStatus(total, paid) {
            if (paid > total) return 'OVERPAID'
            if (paid < total) return 'UNDERPAID'
            return 'SESUAI'
        }

        function openDetail(row) {
            $('#d-doc').text(row.doc_id || '-')
            $('#d-date').text(row.date ? toShortDateTime(row.date) : '-')
            $('#d-branch').text(row.branch_name || row.branch_code || '-')
            $('#d-sales').text(row.sales_name || row.sales_code || '-')
            $('#d-customer').text(row.customer_name || row.customer_code || '-')

            const total = Number(row.total || 0)
            const paid = Number(row.paid_amount || 0)
            const disc = Number(row.discrepancy ?? (paid - total))
            
            $('#d-total').text(fmtIDR(total))
            $('#d-paid').text(fmtIDR(paid))
            $('#d-disc').text(fmtIDR(disc))

            const status = computeStatus(total, paid)
            let badgeClass = 'bg-secondary'
            if (status === 'SESUAI') badgeClass = 'bg-success'
            else if (status === 'UNDERPAID') badgeClass = 'bg-warning'
            else if (status === 'OVERPAID') badgeClass = 'bg-danger'
            
            $('#d-status').text(status).removeClass().addClass('badge ' + badgeClass)

            const $tbody = $('#d-rows').empty()
            
            // If row has details, show them
            if (row.doc_id) {
                // Fetch details via AJAX
                const detailsUrl = "{{ url('api/transactions') }}/" + row.doc_id + "/details"
                $.getJSON(detailsUrl, function(resp) {
                    if (Array.isArray(resp.details) && resp.details.length) {
                        resp.details.forEach(it => {
                            $tbody.append(`
                                <tr>
                                    <td>${it.item_index ?? ''}</td>
                                    <td>${it.payment_type ?? ''}</td>
                                    <td>${fmtIDR(it.amount ?? 0)}</td>
                                    <td>${it.bank ?? ''}</td>
                                    <td>${it.bank_doc ?? ''}</td>
                                    <td>${it.bank_due ?? ''}</td>
                                </tr>
                            `)
                        })
                    } else {
                        $tbody.append('<tr><td colspan="6" class="text-muted">No details available</td></tr>')
                    }
                }).fail(function() {
                    $tbody.append('<tr><td colspan="6" class="text-danger">Failed to load details</td></tr>')
                })
            } else {
                $tbody.append('<tr><td colspan="6" class="text-muted">No details available</td></tr>')
            }

            new bootstrap.Modal(document.getElementById('modal-detail')).show()
        }

        // Initial load
        refreshSummary()

        $('#trx-table_wrapper .dt-buttons').append(`
            <button class="btn btn-sm btn-outline-secondary dt-button" title="Export PDF" id="custom-pdf-export">
                <i class="bx bxs-file-pdf" style="color: #f40f02"></i>
            </button>
        `);

        $('#custom-pdf-export').on('click', function() {
            // Get current filters
            const params = new URLSearchParams();
            
            // Add dashboard filters
            if ($('#filter-status').val()) {
                params.append('status', $('#filter-status').val());
            }
            if ($('#filter-method').val()) {
                params.append('method', $('#filter-method').val());
            }
            if ($('#filter-date-range').val()) {
                params.append('date_range', $('#filter-date-range').val());
            }
            
            // Open server-side PDF export
            const url = '{{ route("datatables.transactions.export.pdf") }}?' + params.toString();
            window.open(url, '_blank');
        });

        // Refresh button
        $('#btn-refresh').on('click', () => {
            trxTable.ajax.reload()
            refreshSummary()
            swalToast.fire({ title: 'Berhasil refresh tabel!', icon: 'success' })
        })
    })
</script>
@endpush