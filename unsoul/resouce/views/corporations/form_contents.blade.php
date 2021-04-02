@extends('unsoul.corporations.form')
@section('corporation_id')
<input type="text" name="corporation_id" value="{{ old('corporation_id') }}" readonly>
@endsection

@section('corporation_type_id')
<select name="corporation_type_id">
	@foreach( $corporation_types as $corporation_type_id => $corporation_type_name )
	<option value="{{ $corporation_type_id }}" @if( old('corporation_type_id')==$corporation_type_id ) selected @elseif(
		!empty($is_confirm) ) disabled @endif>{{ $corporation_type_name }}</option>
	@endforeach
</select>
@endsection

@section('corporation_pos')
<input type="checkbox" class="checkbox_input" name="corporation_pos" value="1" @if( old('corporation_pos') ) checked
	@endif @isset( $is_confirm ) onclick="return false;" @endif>
@endsection

@section('name')
<input type="text" name="name" value="{{ old('name') }}" @if ( !empty($is_confirm) ) readonly @endif>
@endsection

@section('phonetic')
<input type="text" name="phonetic" value="{{ old('phonetic') }}" @if ( !empty($is_confirm) ) readonly @endif>
@endsection

@section('capital')
<input type="text" name="capital" value="{{ old('capital') }}" @if ( !empty($is_confirm) ) readonly @endif>
@endsection

@section('established_year')
<input type="text" size="5" name="established_year" value="{{ old('established_year') }}" @if ( !empty($is_confirm) )
	readonly @endif>
@endsection

@section('established_month')
<input type="text" size="5" name="established_month" value="{{ old('established_month') }}" @if ( !empty($is_confirm) )
	readonly @endif>
@endsection

@section('representative')
<input type="text" name="representative" value="{{ old('representative') }}" @if ( !empty($is_confirm) ) readonly
	@endif>
@endsection

@section('zip_code1')
<input type="text" size="3" name="zip_code1" value="{{ old('zip_code1') }}" @if ( !empty($is_confirm) ) readonly @endif>
@endsection

@section('zip_code2')
<input type="text" size="4" name="zip_code2" value="{{ old('zip_code2') }}" @if ( !empty($is_confirm) ) readonly @endif>
@endsection

@section('prefecture_id')
<select name="prefecture_id">
	<option></option>
	@foreach( $prefectures as $prefecture_id => $prefecture_name )
	<option value="{{ $prefecture_id }}" @if( old('prefecture_id')==$prefecture_id ) selected @elseif(
		!empty($is_confirm) ) disabled @endif>{{ $prefecture_name }}</option>
	@endforeach
</select>
@endsection

@section('city')
<input type="text" name="city" value="{{ old('city') }}" @if ( !empty($is_confirm) ) readonly @endif>
@endsection

@section('town')
<input type="text" name="town" value="{{ old('town') }}" @if ( !empty($is_confirm) ) readonly @endif>
@endsection

@section('street')
<input type="text" name="street" value="{{ old('street') }}" @if ( !empty($is_confirm) ) readonly @endif>
@endsection

@section('building')
<input type="text" name="building" value="{{ old('building') }}" @if ( !empty($is_confirm) ) readonly @endif>
@endsection

@section('tel')
<input type="text" name="tel" value="{{ old('tel') }}" @if ( !empty($is_confirm) ) readonly @endif>
@endsection

@section('fax')
<input type="text" name="fax" value="{{ old('fax') }}" @if ( !empty($is_confirm) ) readonly @endif>
@endsection

@section('submit', empty($is_confirm) ? '確認' : '登録')
@section('cancel', empty($is_confirm) ? 'キャンセル' : '戻る')