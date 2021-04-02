{{-- ボタン --}}
<button class="btn btn-sm {{ $button_classs }}"
	data-toggle="modal"
	data-target="#{{ $modal_id }}"
>
	{{ $label ?? '表示' }}
</button>

{{-- 表示用モーダル --}}
<div
	class="modal fade"
	id="{{ $modal_id }}"
	tabindex="-1"
	role="dialog"
	aria-labelledby="{{ $modal_id }}_label"
	aria-hidden="true"
>
	<div class="vertical-alignment-helper">
		<div class="modal-dialog vertical-align-center modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<img src="{{ asset('storage/' . $img_path) }}">
				</div>
			</div>
		</div>
	</div>
</div>

{{-- CSS --}}
<style type="text/css">
.vertical-alignment-helper {
	display:table;
	height: 100%;
	width: 100%;
	pointer-events:none; /* This makes sure that we can still click outside of the modal to close it */
}
.vertical-align-center {
	/* To center vertically */
	display: table-cell;
	vertical-align: middle;
	pointer-events:none;
}
.modal-content {
	/* Bootstrap sets the size of the modal in the modal-dialog class, we need to inherit it */
	width:inherit;
	max-width:inherit; /* For Bootstrap 4 - to avoid the modal window stretching full width */
	height:inherit;
	/* To center horizontally */
	margin: 0 auto;
	pointer-events: all;
}
</style>