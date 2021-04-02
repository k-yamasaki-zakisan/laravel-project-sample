@extends('unsoul.mypage.offices.form')

@section('link_history')
@if ( isset($is_register) && $is_register )
<a itemprop="item" href="{{ route('unsoul.mypage.offices.register') }}">
	<span itemprop="name">マイページ 事業所追加画面</span>
</a>
@else
<a itemprop="item" href="{{ route('unsoul.mypage.offices.edit', $office_id) }}">
	<span itemprop="name">マイページ 事業所情報編集画面</span>
</a>
@endif
@endsection

@section('form_method')
@if ( isset($is_edit) && $is_edit )
@method('PUT')
@endif
@endsection

@section('name')
<input type="text" name="name" value="{{ $name ?? old('name') }}" required>
@endsection

@section('phonetic')
<input type="text" name="phonetic" value="{{ $phonetic ??  old('phonetic') }}">
@endsection

@section('zip_code')
<input type="text" size="3" name="zip_code1" value="{{ $zip_code1 ??  old('zip_code1') }}">
-
<input type="text" size="4" name="zip_code2" value="{{ $zip_codes ?? old('zip_code2') }}">
@endsection

@section('prefecture')
<select name="prefecture_id">
	<option></option>
	@foreach( $prefectures as $prefecture_id_key => $prefecture_name )
	<option value="{{ $prefecture_id_key }}" @if (isset($is_edit) && $prefecture_id==$prefecture_id_key ) selected
		@endif @if( isset($is_register) && old('prefecture_id')==$prefecture_id_key ) selected @endif>
		{{ $prefecture_name }}
	</option>
	@endforeach
</select>
@endsection

@section('city')
<input type="text" name="city" value="{{ $city ??  old('city') }}">
@endsection

@section('town')
<input type="text" name="town" value="{{ $town ??  old('town') }}">
@endsection

@section('street')
<input type="text" name="street" value="{{ $street ??  old('street') }}">
@endsection

@section('building')
<input type="text" name="building" value="{{ $building ?? old('building') }}">
@endsection

@section('submit', !empty($is_register) ? '追加' : '確認')