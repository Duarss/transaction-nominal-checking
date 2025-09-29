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
    let dataTrxDetails

    $(function() {
        let scrollY = '74vh'
        const columns = [
            {
                title: "Item Index",
                data: "item_index",
                className: "text-center",
            },
            {
                title: "Payment Type",
                data: "payment_type",
            },
            {
                title: "Amount",
                data: "amount",
                render: function(data, type, row) {
                    if (!data) return '-';
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(data);
                }
            },
            {
                title: "Bank",
                data: "bank",
            },
            {
                title: "Bank Doc",
                data: "bank_doc",
            },
            {
                title: "Bank Due",
                data: "bank_due",
            },
            {
                title: "Location",
                data: "location",
            }
        ]

        table = dataTableInit({
            selector: '#table',
            title: title,
            scrollY: scrollY,
            pageLength: 10,
            ajax: {
                url: "{{ route("datatables.detail-master-transaction")}}",
                type: "POST",
                data: function (data) {
                    data.doc_id = @json($data->doc_id);
                    return data;
                },
            },
            columns: columns,
            btnDetails: false,
            btnActions: false,
            btnApprove: false,
            btnCheckList: false,
        })
    })
</script>
@endpush