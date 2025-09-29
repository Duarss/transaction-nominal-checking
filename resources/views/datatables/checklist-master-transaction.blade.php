<div class="d-flex justify-content-center align-items-center">
  @can('recheck', $model)
    <x-input
      type="checkbox"
      id="checklist-{{ $model->doc_id }}"
      name="checklist"
      :label="null"
      containerClass="mb-0 p-0"
      class="form-check-input checklist-toggle cursor-pointer btn-recheck"
      data-id="{{ $model->id }}"
      data-code="{{ $model->doc_id }}"
      title="Checklist transaksi"
      :checked="!!$model->is_rechecked"
      :disabled="$model->is_rechecked"
    />
  @endcan
</div>
