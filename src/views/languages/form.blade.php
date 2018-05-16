<div class="form-group{{ $errors->has('iso_code') ? ' has-error' : '' }}">
    <label class="col-md-4 control-label">{{ array_get($uiTranslations, 'iso_code') }}</label>
    <div class="col-md-4">
        {!! Form::text('iso_code', null, ['class' => 'form-control', 'readonly' => isset($language)]) !!}
    </div>
</div>

<div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
    <label class="col-md-4 control-label">{{ array_get($uiTranslations, 'title') }}</label>
    <div class="col-md-4">
        {!! Form::text('title', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group{{ $errors->has('title_localized') ? ' has-error' : '' }}">
    <label class="col-md-4 control-label">{{ array_get($uiTranslations, 'title_localized') }}</label>
    <div class="col-md-4">
        {!! Form::text('title_localized', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group{{ $errors->has('is_fallback') ? ' has-error' : '' }}">
    <label class="col-md-4 control-label">{{ array_get($uiTranslations, 'is_fallback') }}</label>
    <div class="col-md-4">
        {!! Form::select('is_fallback', [
            0 => array_get($uiTranslations, 'no'),
            1 => array_get($uiTranslations, 'yes')
        ], null , ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group{{ $errors->has('is_visible') ? ' has-error' : '' }}">
    <label class="col-md-4 control-label">{{ array_get($uiTranslations, 'is_visible') }}</label>
    <div class="col-md-4">
        {!! Form::select('is_visible', [
            1 => array_get($uiTranslations, 'yes'),
            0 => array_get($uiTranslations, 'no')
        ], null , ['class' => 'form-control']) !!}
    </div>
</div>