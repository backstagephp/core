<div {{ $attributes }}>
    <form method="POST" action="{{ route('backstage.forms.submit', $form->slug) }}">
        @csrf
        <input type="hidden" name="content_ulid" value="{{ $content->ulid }}">
        @foreach ($form->fields as $field)
            <div>

                <label for="{{ $field->slug }}">{{ $field->name }}</label>
                <input type="{{ $field->type }}" name="{{ $field->slug }}" id="{{ $field->slug }}">

                @error($field->slug)
                    <p>{{ $message }}</p>
                @enderror

            </div>
        @endforeach

        <button type="submit">{{ $form->submit_button ?? __('Submit') }}</button>
    </form>
</div>
