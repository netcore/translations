@extends('admin.layout')

@section('content')

    @include($viewNamespace . '::languages.scripts')
    @include($viewNamespace . '::languages.styles')

    <div class="page-header">
        <div class="row">
            <h1 class="col-xs-12 col-sm-4 text-center text-left-sm">
                <i class="fa fa-language page-header-icon"></i>
                &nbsp;&nbsp;{{ array_get($uiTranslations, 'languages') }}
            </h1>

            <div class="pull-right col-xs-12 col-sm-auto">
                <a href="{{ route('admin.languages.create')  }}" class="btn btn-primary btn-labeled" style="width: 100%;">
                    <span class="btn-label icon fa fa-plus"></span>
                    {{ array_get($uiTranslations, 'create') }}
                </a>
            </div>
        </div>
    </div> <!-- / .page-header -->

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
                <td width="10%" class="text-center">
                    @include( $viewNamespace . '::partials.edit_button', [
                        'route' => 'admin.languages.edit',
                        'row'   => $language
                    ])

                    @include($viewNamespace . '::partials.destroy_button', [
                        'route'         => 'admin.languages.destroy',
                        'row'           => $language,
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

@stop
