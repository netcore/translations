@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-md-18">
        <div class="panel panel-colourable">

            <div class="panel-heading">
                <span class="panel-title">{{ array_get($uiTranslations, 'create_language') }}</span>
                <div class="panel-heading-controls">
                    <a href="{{ route('admin.languages.index') }}" class="btn btn-xs btn-success">{{ array_get($uiTranslations, 'back_to_list') }}</a>
                </div> <!-- / .panel-heading-controls -->
            </div>

            <div class="panel-body">

                @include($viewNamespace.'::partials.messages')

                {!! Form::open(['route' => [ 'admin.languages.store'], 'class' => 'form-horizontal']) !!}

                    @include($viewNamespace.'::languages.form')

                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            <button type="submit" class="btn btn-primary m-r-5 pull-right">{{ array_get($uiTranslations, 'save') }}</button>
                        </div>
                    </div>

                {!! Form::close()!!}

            </div>
        </div>
        <!-- end panel -->
    </div>
    <!-- end col-12 -->
</div>
<!-- end row -->
@stop
