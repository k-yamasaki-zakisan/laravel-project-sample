<style type="text/css">
    .modal-window {
        display: none;
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 1050;
        display: none;
        overflow: hidden;
        -webkit-overflow-scrolling: touch;
        outline: 0;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-dialog {
        width: 600px;
        margin: 30px auto;
    }

    .modal-content {
        background-color: white;
        font-size: medium;
        border-radius: 3px;
    }

    .modal-header {
        border-bottom: 1px solid silver;
        padding: 15px;
    }

    .modal-body {
        padding: 15px;
    }

    .modal-body>p {
        margin: 0 0 10px;
    }

    .modal-footer {
        padding: 15px;
        text-align: right;
        border-top: 1px solid silver;
    }

    .btn {
        display: inline-block;
        padding: 6px 12px;
        margin-bottom: 0;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.42857143;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 4px;
        border-radius: 3px;
        -webkit-box-shadow: none;
        box-shadow: none;
        border: 1px solid transparent;
    }

    .btn-danger {
        color: #fff;
        background-color: #dd4b39;
        border-color: #d73925;
    }

    .btn-modal-cancel {
        margin-right: 0.5em;
    }
</style>

<div class="modal-window" tabindex="-1" role="dialog" id="delete-modal-window">
    <form role="form" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">削除の確認</div>
                </div>
                <div class="modal-body">
                    <p>本当に削除しますか？</p>
                    <p><strong></strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel">キャンセル</button>
                    <button type="submit" class="btn btn-danger">削除</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(function() {
        var modal = $('#delete-modal-window');
    
        $('.js-deleteButton').on('click', function() {
            var $trigger = $(this);
            var title = $trigger.data('title');
            var url = $trigger.data('url');
            modal.find('.modal-body p strong').eq(0).text(title);
            modal.find('form').attr('action', url);
            modal.find('button[type="submit"]').attr('disabled', false);
            modal.fadeIn(300);
        });
        $('.modal-window, .btn-modal-cancel').on('click', function() {
            modal.fadeOut(300);
        });
        $('.modal-content').on('click', function(event) {
            event.stopPropagation();
        });
        modal.find('button[type="submit"]').on('click', function() {
            $(this).attr('disabled', true);
            $(this).closest('form').submit();
        });
    });
</script>