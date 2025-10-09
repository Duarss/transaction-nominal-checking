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
    let dataBranchDetails

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
            title: title,
            scrollY: scrollY,
            pageLength: 10,
            ajax: {
                url: "{{ route("datatables.detail-master-branch") }}",
                type: "POST",
                data: function(data) {
                    data.code = @json($data->code);
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