@extends($extends)

@section($section)

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
                        </div>

                        <div class="col-md-10">

                            <p>
                                {{ array_get($uiTranslations, 'here_you_can_add_new_keys', 'Here you can manually add new keys:') }}
                            </p>

                            <form action="{{ route('admin.translations.store' ) }}" method="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <input
                                    type="text"
                                    value=""
                                    placeholder="group"
                                    class="form-control"
                                    name="group"
                                    required
                                    style="width:150px;display:inline-block;"
                                >

                                <input
                                    type="text"
                                    value=""
                                    placeholder="new_key"
                                    class="form-control"
                                    name="key"
                                    required
                                    style="width:150px;display:inline-block;"
                                >

                                <input
                                    type="submit"
                                    value="{{ array_get($uiTranslations, 'add_new_key', 'Add') }}"
                                    class="btn btn-primary"
                                    style="display:inline-block;"
                                >
                            </form>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
