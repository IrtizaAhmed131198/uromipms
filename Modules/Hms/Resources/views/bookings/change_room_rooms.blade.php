<option value="">{{ __('messages.please_select') }}</option>
@foreach($rooms as $id => $number)
    <option value="{{ $id }}">{{ $number }}</option>
@endforeach
