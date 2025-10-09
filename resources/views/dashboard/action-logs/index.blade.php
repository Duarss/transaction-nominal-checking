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
@endsection

@push('js')
<script>
    const title = "{{ $title }}"
    let dataActionLog

    $(function () {
        let scrollY = '74vh'
        const columns = [
            {
                title: "Doc ID",
                data: "transaction_code",
            },
            {
                title: "Nominal Lama",
                data: "nominal_before",
                render: function(data, type, row) {
                    if (!data) return '-';
                    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(data);
                }
            },
            {
                title: "Nominal Sekarang",
                data: "nominal_after",
                render: function(data, type, row) {
                    if (!data) return '-';
                    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(data);
                }
            },
            {
                title: "Status",
                data: "status",
                render : function(data, type, row) {
                    if (data === 'approved') {
                        return '<span class="badge bg-success">Approved</span>';
                    } else if (data === 'updated') {
                        return '<span class="badge bg-warning">Updated</span>';
                    } else {
                        return '<span class="badge bg-secondary">' + data + '</span>';
                    }
                }
            },
            {
                title: "Perubahan Oleh",
                data: "done_by",
            },
            {
                title: "Perubahan Pada",
                data: "updated_at",
                render: function(data, type, row) {
                    return data ? timeAgo(data) : '-';
                }
            }
        ]

        table = dataTableInit({
            selector: "#table",
            title: "List Nominal Action Logs",
            scrollY: scrollY,
            pageLength: 10,
            ajax: {
                url: "{{ route("datatables.main-action-log") }}",
                type: "POST",
                data: function (params) {

                },
            },
            order: [[5, "desc"]],
            columns: columns,
            btnDetails: false,
            btnActions: false,
            btnApprove: false,
            btnCheckList: false,
        })
    })
</script>
@endpush
