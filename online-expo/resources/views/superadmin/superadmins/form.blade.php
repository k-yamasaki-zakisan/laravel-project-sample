<!-- general form elements disabled -->
<div class="card card-success">
    <div class="card-header">
        <h3 class="card-title">入力フォーム</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- /.card-header -->
    <div class="card-body">
        <form action="{{ $action }}" method="post">
            @csrf
            @if( $method === 'put') @method('PUT') @endif
            <!-- スーパアドミン名 -->
            <div class="form-group">
                <label>SuperAdmin名前</label>
                <input type="text" class="form-control" name="superadmin_name"
                    value="{{ old('superadmin_name') ?? $superadmin['name'] ?? '' }}">
            </div>
            @if($errors->has('superadmin_name'))
            @foreach($errors->get('superadmin_name') as $message)
            <p style="color:red;">{{ $message }}</p>
            @endforeach
            @endif

            <!-- スーパアドミンメールアドレス -->
            <div class="form-group">
                <label>メールアドレス</label>
                <input type="text" class="form-control" name="superadmin_email"
                    value="{{ old('superadmin_email') ?? $superadmin['email'] ?? '' }}">
            </div>
            @if($errors->has('superadmin_email'))
            @foreach($errors->get('superadmin_email') as $message)
            <p style="color:red;">{{ $message }}</p>
            @endforeach
            @endif

            @if( !empty($is_create) )
            <!-- スーパアドミンパスワード -->
            <div class="form-group">
                <label>パスワード</label>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" id="superadminPassword" name="superadmin_password"
                        value="{{ old('superadmin_password') ?? $superadmin['password'] ?? '' }}">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-eye" id="passEye"></i></span>
                    </div>
                </div>
            </div>
            @if($errors->has('superadmin_password'))
            @foreach($errors->get('superadmin_password') as $message)
            <p style="color:red;">{{ $message }}</p>
            @endforeach
            @endif
            @endif

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ $submit }}</button>
            </div>
    </div>

    @section('adminlte_js')
    <script>
        $(function () {
      $('#passEye').click(function() {
        if ($(this).hasClass('fa-eye')) {
          $(this).removeClass('fa-eye').addClass('fa-eye-slash');
          $('#superadminPassword').attr('type', 'text');
        } else {
          $(this).removeClass('fa-eye-slash').addClass('fa-eye');
          $('#superadminPassword').attr('type', 'password');
        }
      });
    });
    </script>
    @append