{{-- 勤怠用モーダル --}}
<div
	style="display: none;"
	class="modal fade"
	id="modal-{{ $id ?? 'attendance_request' }}"
	tabindex="-1"
	role="dialog"
	aria-labelledby="modal-{{ $id ?? 'attendance_request' }}-label"
	aria-hidden="true"
>
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h5 class="modal-title" id="modal-{{ $id ?? 'attendance_request' }}-label">{{ $modal_title }}</h5>
			</div>
			<div class="modal-body">
			</div>
		</div>
	</div>
</div>