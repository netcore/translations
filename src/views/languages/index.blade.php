@extends($extends)

@section($section)

    @include($viewNamespace . '::languages.scripts')
    @include($viewNamespace . '::languages.styles')
    <div class="row">
        <div class="col-md-12">
            <!-- begin panel -->
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="{{ route('admin.languages.create')  }}" class="btn btn-xs btn-success btn-labeled">
                            <span class="btn-label icon fa fa-plus"></span>
                            {{ array_get($uiTranslations, 'create') }}
                        </a>
                    </div>

                    <h4 class="panel-title">
                        <i class="fa fa-language page-header-icon"></i> {{ array_get($uiTranslations, 'languages') }}
                    </h4>
                </div>
                <div class="panel-body">

                    @include($viewNamespace.'::partials.messages')

                    <table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" id="languages-index-datatable">
                        <thead>
                        <tr>
                            <th>{{ array_get($uiTranslations, 'title') }}</th>
                            <th>{{ array_get($uiTranslations, 'title_localized') }}</th>
                            <th>{{ array_get($uiTranslations, 'iso_code') }}</th>
                            <th>{{ array_get($uiTranslations, 'fallback') }}?</th>
                            <th>{{ array_get($uiTranslations, 'actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($languages as $language)
                            <tr>
                                <td>{{ $language->title }}</td>
                                <td>{{ $language->title_localized }}</td>
                                <td>{{ $language->iso_code }}</td>
                                <td>
                                    {{ $language->is_fallback ? array_get($uiTranslations, 'yes') : array_get($uiTranslations, 'no') }}
                                </td>
                                <td width="15%" class="text-center">
                                    @include( $viewNamespace . '::partials.edit_button', [
                                        'route' => 'admin.languages.edit',
                                        'row'   => $language
                                    ])

                                    @include($viewNamespace . '::partials.destroy_button', [
                                        'route'         => 'admin.languages.destroy',
                                        'row'           => $language,
                                        'data_id'       => $language->iso_code,
                                        'title'         => array_get($uiTranslations, 'are_you_sure'),
                                        'text'          => array_get($uiTranslations, 'language_will_be_deleted'),
                                        'confirm'       => array_get($uiTranslations, 'accept'),
                                        'success_title' => array_get($uiTranslations, 'deleted'),
                                        'success_text'  => array_get($uiTranslations, 'language_deleted'),
                                    ])
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
@endsection
