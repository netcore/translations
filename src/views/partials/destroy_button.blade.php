<a
    @if( ! auth('admin')->user()->permissionsHelper()->isAllowed($route) )
        class="btn {{ isset($class) ? $class : 'btn-xs' }} btn-danger half-transparent cursor-not-allowed"
    @else
        href="{{ route($route, $row) }}"
        data-method="DELETE"
        data-id="{{ ( is_array($row) ) ? $row[0]->id : $row->id }}"

        data-title="{{ $title }}"
        data-text="{{ $text }}"
        data-confirm-button-text="{{ $confirm }}"

        data-success-title="{{ $success_title }}"
        data-success-text="{{ $success_text }}"
        class="btn {{ isset($class) ? $class : 'btn-xs' }} btn-danger translations-in-database-confirm-action"
    @endif
>
    <i class="fa fa-trash-o"></i> {{ array_get($uiTranslations, 'delete') }}
</a>