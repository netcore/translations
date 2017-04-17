<a
    @if( ! auth('admin')->user()->permissionsHelper()->isAllowed($route) )
        class="btn btn-xs btn-info half-transparent cursor-not-allowed"
    @else
        href="{{ route($route, $row) }}"
        class="btn btn-xs btn-info"
    @endif
>
    <i class="fa fa-edit"></i> {{ array_get($uiTranslations, 'edit') }}
</a>