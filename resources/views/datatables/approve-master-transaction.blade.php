<div class="d-flex justify-content-center">
    @can('approve', $model)
        <x-button data-id="{{ $model->id }}" data-code="{{ $model->doc_id }}" title="Approve" icon="bx-check" align="text-center" class="btn-outline-success btn-sm btn-approve" data-bs-toggle="tooltip" data-bs-placement="bottom"></x-button>
    @endcan
</div>