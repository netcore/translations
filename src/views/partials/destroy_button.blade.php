<a

        href="{{ route($route, $row) }}"
        data-method="DELETE"
        data-id="{{ (isset($data_id) ? $data_id : (( is_array($row) ) ? $row[0]->id : $row->id) ) }}"

        data-title="{{ $title }}"
        data-text="{{ $text }}"
        data-confirm-button-text="{{ $confirm }}"

        data-success-title="{{ $success_title }}"
        data-success-text="{{ $success_text }}"
        class="btn {{ isset($class) ? $class : 'btn-xs' }} btn-danger translations-in-database-confirm-action"
>
    <i class="fa fa-trash-o"></i> {{ array_get($uiTranslations, 'delete') }}
</a>