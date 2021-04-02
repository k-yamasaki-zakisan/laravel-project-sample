<div class="card card-info">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>出展製品・サービス名</label>
                    <input type="text" class="form-control" name="product_name" value="{{ old('product_name') }}">
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <label>出展製品・サービス説明</label>
                    <textarea class="form-control" rows="6" name="product_description"></textarea>
                </div>
            </div>
            <!-- アクティブフラグ -->
            <div class="col-sm-4">
                <div class="form-group">
                    <label>展示の有効化</label><br>
                    <input type="checkbox" name="product_view_flag" data-bootstrap-switch="" kl_vkbd_parsed="true" @if(
                        !empty(old()) ) @if( old('product_view_flag')==='on' ) ) checked="" @endif @else checked=""
                        @endif>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<!-- bootstrap-switch -->
<script src="{{ asset('/vendor/adminlte/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}"></script>
<script>
    $(function () {
      //bootstrap-switch
      $("input[data-bootstrap-switch]").each(function(){
        $(this).bootstrapSwitch('state', $(this).prop('checked'));
      });
    });
</script>
@append