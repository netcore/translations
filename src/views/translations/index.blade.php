@extends($extends)

@section($section)

    <script>
        var csrf_token = '{{ csrf_token() }}';
        var translations_index_route = '{{ route('admin.translations.index') }}';
    </script>
    @include($viewNamespace . '::translations.scripts')
    @include($viewNamespace . '::translations.styles')

    <div class="row">
        <div class="col-md-12">
            <!-- begin panel -->
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        {{  array_get($uiTranslations, 'translations') }}
                    </h4>
                </div>
                <div class="panel-body">

                    @include($viewNamespace.'::partials.messages')

                    <div class="row">
                        <div class="col-md-2">

                            <form role="form">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <select name="group" id="group" class="form-control group-select">
                                        @foreach( $groups as $key=>$value )
                                            <option value="{{ $key }}"{{ $key == $group ? ' selected':'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <a href="{{ route('admin.translations.export') }}" download class="btn btn-success btn-xs">
                                    {{ array_get($uiTranslations, 'export_excel') }}
                                </a>
                            </form>

                        </div>

                        <div class="col-md-10">

                            <div class="row">
                                <div class="col-md-6">
                                    <form action="" class="col-xs-12 trans-in-db-inline trans-in-db-zero-padding"">
                                    <div class="input-group trans-in-db-zero-margin">

                                                <span class="input-group-addon" id="trans-in-db-search-span">
                                                    <i class="fa fa-search"></i>
                                                </span>

                                        <input
                                                type="text"
                                                placeholder="{{ array_get($uiTranslations, 'search') }}"
                                                class="form-control no-padding-hr resource-search"
                                                id="trans-in-db-search-input"
                                        >
                                    </div>
                                    </form>
                                </div>
                                <div class="col-md-6 trans-in-db-text-align-right">

                                    <form action="{{ route('admin.translations.import') }}" class="form-horizontal" method="POST" enctype="multipart/form-data">

                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                        <input type="file" name="excel" class="trans-in-db-inline-block" required>

                                        <input
                                                type="submit"
                                                value="{{ array_get($uiTranslations, 'import_excel') }}"
                                                class="btn btn-xs btn-info trans-in-db-inline-block"
                                        >
                                    </form>
                                </div>
                            </div>

                            <br/>
                            @include($viewNamespace.'::translations.table')
                        </div>
                    </div>

                    <div>
                        @if ($group)
                            <hr/>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection