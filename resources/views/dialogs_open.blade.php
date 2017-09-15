@extends('layouts.app')

@section('content')
<script>
lastID = {{ $chatID }};
dialogID = {{ $id }};

</script>

<script type="text/javascript" src="/js/telegram.js"></script>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
				<div class="panel-heading"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> Telegram діалог з користувачем {{ $toUser }} @ {{ $chatUserName }}
		<form method="GET" action="{{ url('telegram_dialogs/all', [$chatID]) }}">
        <button class="btn btn-default" type="submit">Показати всі діалоги з ним</button>
        </form>
				</div>



				@if($isClosed == 0)
<br>
                   <div class="input-group">
                <form method="POST" enctype="multipart/form-data" id="formx" action="javascript:void(null);" onsubmit="call()">
      <input type="text" id="msg" name="message" class="form-control" placeholder="Ваше повідомлення" required>
      <input type="hidden" name="dialog_id" value="{{ $id }}">
      <input type="hidden" name="chat_id" value="{{ $chatID }}">
      {{ csrf_field() }}

        <button class="btn btn-default" type="submit">Надіслати!</button>

        </form>
					   <hr>
    </div>

	 <form method="POST" action="{{ url('telegram_dialogs/close') }}">
      <input type="hidden" name="dialog_id" value="{{ $id }}">
      {{ csrf_field() }}
        <button type="submit">Закрити діалог</button>

        </form>

@else
				<center><b>Діалог закрито!</b></center><hr>
@endif

	 <form method="POST" action="{{ url('telegram_dialogs/delete') }}">
      <input type="hidden" name="dialog_id" value="{{ $id }}">
      {{ csrf_field() }}
        <button type="submit">Видалити діалог</button>

        </form>
      <br>


        <div id="response" class="list l1">
        </div>

        <script type="text/javascript" language="javascript">
 	function call() {
 	  var msg  = $('#formx').serialize();
 	  $('#msg').val('');
        $.ajax({
          type: 'POST',
          cache: false,
          contentType: 'application/x-www-form-urlencoded',
          url: '/telegram_dialogs/send',
          data: msg,
          success: function(data) {
            //$('#response').html(data);
            //alert(data);
          },
          error:  function(xhr, str){
	    alert('Помилка сервера: ' + xhr.responseCode);
          }
        });

    }
</script>

@foreach($messages as $message)
<div class="alert success" role="alert">
<b>{{ $message->user_id == Auth::user()->id ? "Я" : $toUser }}</b>:
{{ $message->message }} ({{$message->created_at}})

</div>
@endforeach

{{ $messages->render() }}

</div>



                </div>
            </div>
        </div>
    </div>
</div>
@endsection