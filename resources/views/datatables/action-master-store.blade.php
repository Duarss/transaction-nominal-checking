<div class="d-flex justify-content-center">
    @can('update', $model)
        <x-button data-id="{{ $model->id }}" data-code="{{ $model->code }}" title="Edit" icon="bx-edit" class="btn-outline-warning btn-sm btn-edit" data-bs-toggle="tooltip" data-bs-placement="bottom"></x-button>
    @endcan
</div>