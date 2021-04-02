@extends('unsoul.persons.form')

@section('form_action', $form_action)

@section('person_id')
	@if ( isset($is_confirm) && $is_confirm )
	*****
	@else
	<input type="text" name="person_id" value="{{ old('person_id') }}" readonly>
	@endif
@endsection

@section('corporation_name')
	@if ( isset($is_confirm) && $is_confirm )
	<span>{{ $corporation_name }}</span>
	@else
	<input type="text" name="corporation_name" value="{{ $corporation_name ?? old('corporation_name') }}" readonly>
	<span><a href="{{ route('unsoul.persons.register.search_corporation') }}">検索</a></span>
	@endif
@endsection

@section('last_name')
        @if ( isset($is_confirm) && $is_confirm )
	<span>{{ $last_name }}</span>
	@else
	性<input type="text" name="last_name" size="10" value="{{ old('last_name')}}" required>
	@endif
@endsection

@section('first_name')
        @if ( isset($is_confirm) && $is_confirm )
	<span>{{ $first_name }}</span>
        @else
	名<input type="text" name="first_name" size="10" value="{{ old('first_name')}}" required>
	@endif
@endsection

@section('last_name_kana')
        @if ( isset($is_confirm) && $is_confirm )
	<span>{{ $last_name_kana }}</span>
        @else
	セイ<input type="text" name="last_name_kana" size="10" value="{{ old('last_name_kana')}}" required>
	@endif
@endsection

@section('first_name_kana')
        @if ( isset($is_confirm) && $is_confirm )
	<span>{{ $first_name_kana }}</span>
        @else
	メイ<input type="text" name="first_name_kana" size="10" value="{{ old('first_name_kana')}}" required>
	@endif
@endsection

@section('birthday')
        @if ( isset($is_confirm) && $is_confirm )
	<span>{{ $birthday }}</span>
        @else
	<input type="date" name="birthday" value="{{ old('birthday')}}">
	@endif
@endsection

@section('gender_id')
        @if ( isset($is_confirm) && $is_confirm )
	<span>{{ $gender_name }}</span>
        @else
		@foreach( $genders as $gender_id => $gender_name )
			<input type="radio" name="gender_id"
				id="{{ $gender_id }}" value="{{ $gender_id }}"
				@if( old('gender_id') == $gender_id ) checked @endif
			>
			<label for="{{ $gender_id }}">{{ $gender_name }}</label>
		@endforeach
	@endif
@endsection

@section('login_id')
        @if ( isset($is_confirm) && $is_confirm )
	<span>{{ $login_id }}</span>
        @else
	<input type="text" name="login_id" value="{{ old('login_id')}}" required>
	@endif
@endsection

@section('password')
        @if ( isset($is_confirm) && $is_confirm )
	<span>{{ $password }}</span>
        @else
	<input type="password" name="password" value="{{ old('password')}}" required>
	@endif
@endsection

@section('password_confirm')
	@if ( !isset($is_confirm) )
	<tr>
        <th colspan="2">パスワード確認</th>
          <td colspan="3">
          <input type="password" name="password_confirmation" value="{{ old('password_confirm')}}" required>
        </td>
    </tr>
	@endif
@endsection

@section('submit', empty($is_confirm) ? '確認' : '登録')
@section('cancel', empty($is_confirm) ? 'キャンセル' : '戻る')

@section('script')
	$('.reset_btn').on('click', function(){
		location.href = "{{ $back_to }}";
	});
@endsection