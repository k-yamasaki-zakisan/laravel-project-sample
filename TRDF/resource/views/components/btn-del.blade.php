@if(!isset($id_attr))
	@php
			$id_attr = 'modal-delete-' . $controller . '-' . $id;
	@endphp
@endif
{{-- 削除ボタン --}}
<button type="button" class="btn btn-sm {{ $btn_class ?? 'btn-warning'}}" data-toggle="modal" data-target="#{{ $id_attr }}">
	{{ __('削除') }}
</button>

{{-- モーダルウィンドウ --}}
<div class="modal fade" id="{{ $id_attr }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id_attr }}-label" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="{{ $id_attr }}-label">
					{{ __('削除の確認') }}
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p>{{ __('本当に削除してよろしいですか？') }}</p>
				<p><strong>{{ $name }}</strong></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					{{ __('キャンセル') }}
				</button>
				{{-- 削除用のアクションを実行させるフォーム --}}
				<form
					@if(empty($endpoint))
					{{-- TRCD管理画面用に$prefixを付与 --}}
					action="@if( isset($prefix) ){{ url($prefix . $controller .'/'.$action.'/'. $id) }}@else{{ url($controller .'/'.$action.'/'. $id) }}@endif"
					@else
					action="{{$endpoint}}"
					@endif
					method="post"
					style="display:inline;"
				>
					@csrf
					@method('DELETE')
					<button type="submit" class="btn btn-danger">
						{{ __('削除') }}
					</button>
				</form>
			</div>
		</div>
	</div>
</div>