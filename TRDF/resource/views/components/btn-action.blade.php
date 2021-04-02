{{-- ボタン --}}
<button
	type="button"
	class="btn btn-sm {{ $button_class }}"
	data-toggle="modal"
	data-target="#{{ $modal_id }}">{{ __($button_value) }}</button>

{{-- モーダルウィンドウ --}}
<div
	class="modal fade"
	id="{{ $modal_id }}"
	tabindex="-1"
	role="dialog"
	aria-labelledby="{{ $modal_id }}_label"
	aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="{{ $modal_id }}_label">{{ __($modal_title) }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<p>{{ __($confirmation_message) }}</p>
				<p><strong>{{ $confirmation_item }}</strong></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __($confirmation_cancel_button_value) }}</button>

				{{-- アクションを実行するフォーム --}}
				<form
					action="{{ $form_action }}"
					method="post"
					style="display:inline;"
				>
					@csrf
					@method($form_method)
					<button type="submit" class="btn btn-danger">{{ __($form_submit_button_value) }}</button>
				</form>
			</div>

		</div>
	</div>
</div>