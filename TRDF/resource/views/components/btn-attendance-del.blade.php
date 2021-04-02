@php
	$controller = 'client_employees/' . $attendance['client_employee_id'];

	$controller .= empty($attendance['is_attendance_paid_holiday'])
		? '/attendances'
		: '/attendance_paid_holidays';

	$attendances_type_name = 'attendances';

	if (!empty($attendance['is_attendance_paid_holiday'])) {
		$attendances_type_name = 'attendance_paid_holidays';
	} elseif (!empty($attendance['is_attendance_note'])) {
		$attendances_type_name = 'attendance_notes';
	}

	$id_attr = 'client_employees'
		. $attendance['client_employee_id']
		. $attendances_type_name
		. $attendance['id'];

	$endpoint = '';

	if (!empty($attendance['is_attendance_note'])) {
		$endpoint = route('trcd.attendance_notes.delete', ['client_employee_id' => $attendance['client_employee_id'],'attendance_note_id' => $attendance['id']]);
	}
@endphp
@component('components.btn-del')
	@slot('endpoint', $endpoint)
	@slot('prefix', 'trcd/')
	@slot('controller', $controller)
	@slot('action','delete')
	@slot('id', $attendance['id'])
	@slot('name', $attendance['formatted_date'])
	@slot('id_attr', $id_attr)
@endcomponent