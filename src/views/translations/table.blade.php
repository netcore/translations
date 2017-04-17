
<table id="trans-in-db-table" cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered">

    <thead>

    <tr id="headertr">

        <th width="15%">{{ array_get($uiTranslations, 'group_key') }}</th>

        <th width="40%">
            <form action="" class="col-xs-12 locale_switching_form trans-in-db-inline trans-in-db-zero-padding">

                <div class="input-group trans-in-db-zero-margin trans-in-db-full-width">
                    <select name="from_locale" class="form-control no-padding-hr">
                        @foreach( $locales as $locale )
                            <option value="{{ $locale }}" {!! $locale==$fromLocale ? 'selected="selected"' : '' !!}>
                                {{ strtoupper($locale) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <input name="to_locale" type="hidden" value="{{ $toLocale }}">
            </form>
        </th>

        <th width="40%">
            <form action="" class="col-xs-12 locale_switching_form trans-in-db-inline trans-in-db-zero-padding">

                <div class="input-group trans-in-db-zero-margin trans-in-db-full-width">
                    <select name="to_locale" class="form-control no-padding-hr">
                        @foreach( $locales as $locale )
                            <option value="{{ $locale }}" {!! $locale==$toLocale ? 'selected="selected"' : '' !!}>
                                {{ strtoupper($locale) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <input name="from_locale" type="hidden" value="{{ $fromLocale }}">
            </form>
        </th>
    </tr>
    </thead>
    <tbody>

    @foreach( $translations as $translation )

        @if( !isset($previous_group) || ($translation->group != $previous_group) )
            <tr class="spacetr">
                <td class="spacetd">
                    <b>
                        {{ $translation->group }}
                    </b>

                    {{-- output all info here (hidden) so search picks up seperation lines as well --}}

                    @foreach( $translations as $sub_translation )
                        @if( $translation->group == $sub_translation->group  )
                            <span hidden>{{ $sub_translation->key }}</span>

                            @foreach( [ $fromLocale, $toLocale ] as $locale )
                                <?php
                                $t = object_get($sub_translation, $locale, null);
                                ?>
                                <span hidden>
                                    {{ $t ? $t->value : '' }}
                                </span>
                            @endforeach
                        @endif
                    @endforeach
                </td>

                <td class="spacetd">
                </td>
                <td class="spacetd">
                </td>
            </tr>
            <?php $previous_group = $translation->group; ?>
        @endif

        <tr id="{{ $translation->key }}">

            <td>
                {{ $translation->key }}
            </td>

            @foreach( [ $fromLocale, $toLocale ] as $locale )

                <?php
                    $t = object_get($translation, $locale, null);
                    $editUrl = route( 'admin.translations.edit', $translation->group );
                ?>

                <td>
                    <a href="#edit"
                       class="editable status-{{ $t ? $t->status : 0 }} locale-{{ $locale }}"
                       data-locale="{{ $locale }}"
                       data-name="{{ $locale . "|" . $translation->key }}"
                       id="username"
                       data-type="textarea"
                       data-pk="{{ $t ? $t->id : 0 }}"
                       data-url="{{ $editUrl }}"
                       data-title="{{ array_get($uiTranslations, 'provide_translation') }}">{{ $t ? htmlentities($t->value, ENT_QUOTES, 'UTF-8', false) : '' }}</a>
                </td>
            @endforeach

        </tr>
    @endforeach

    </tbody>
</table>