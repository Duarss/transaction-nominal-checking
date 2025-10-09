@can('view-details', $model)
    <x-button data-id="{{ $model->id }}" data-code="{{ $model->code }}" title="Details" icon="bx-detail" align="text-center" class="btn-outline-secondary btn-sm btn-details" data-bs-toggle="tooltip" data-bs-placement="bottom"></x-button>
@endcan