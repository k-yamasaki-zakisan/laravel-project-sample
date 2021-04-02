{{-- Modal表示トリガー --}}
<button
	type="button"
	class="btn btn-sm {{ $button_class ?? 'btn-primary' }} js-trigger--attendance_request"
	{{-- 対象社員のID --}}
	@isset($client_employee_id)
		data-client_employee_id="{{ $client_employee_id }}"
	@endisset
	{{-- 勤怠・有給ヘッダどちらかのID --}}
	@isset($target_id)
		data-target_id="{{ $target_id }}"
	@endisset
	{{-- 申請種別（勤怠or有給) --}}
	@isset($target_type)
		data-target_type="{{ $target_type }}"
	@endisset
	{{-- 申請対象日 --}}
	@isset($target_date)
		data-target_date="{{ $target_date }}"
	@endisset
	{{-- 申請か承認か --}}
	@isset($action)
		data-action="{{ $action }}"
	@endisset
	data-toggle="modal"
	data-target="#modal-{{ $id ?? 'attendance_request' }}">{{ __($button_value ?? '申請') }}</button>