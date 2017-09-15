@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
				<div class="panel-heading"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> Діалоги з користувачами Telegram: <b>{{ $count = $dialogs->count() }}</b></div>

                <div class="panel-body">
                <div class="list-group">
					@if ($count == 0)
							<p>Записів немає.</p>
					@endif


                     @foreach ($dialogs as $dialog)

					<a href="{{ url('telegram_dialogs/open', [$dialog->id]) }}" class="list-group-item"> <b><big> {{ $dialog->name . ' ' . $dialog->last_name }}  @ {{ $dialog->username }}</b></big>
					@if ($dialog->unread > 0)
							<font color="red"><b> (New + {{ $dialog->unread }})</b></font>
					@endif

					@if ($dialog->closed == 1)
							<font color="red">Діалог закрито!</font>
					@endif
                    </a>


                    @endforeach
                   </div>

{{ $dialogs->render() }}


                </div>
            </div>
        </div>
    </div>
</div>
@endsection