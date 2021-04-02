@component('components.btn-action')
	@slot('modal_id', $modal_id)
	@slot('modal_title', '申請取消の確認')
	@slot('form_method', 'DELETE')
	@slot('form_action', $form_action)
	@slot('form_submit_button_value', '申請取消')
	@slot('confirmation_item', $confirmation_item)
	@slot('confirmation_message', '申請を取り消します、本当によろしいですか？')
	@slot('confirmation_cancel_button_value', 'キャンセル')
	@slot('button_value', '申請取消')
	@slot('button_class', 'btn-danger')
@endcomponent