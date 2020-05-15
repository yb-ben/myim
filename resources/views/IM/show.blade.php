@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <sapn></sapn>
                    </div>
                    <input id="token" name="token" type="hidden" value="{{ $token }}">
                    <input id="roomId" name="roomId" type="hidden" value="{{ $room->id }}">
                    <div class="card-body" >

                        <div style="display: flex;width: 100%;height: 600px">
                            <div style="flex-grow: 3;background-color: #1d643b;display:flex;flex-direction: column">
                                <div style="flex-grow: 3"></div>
                                <div style="flex-grow: 1;background-color: #4dc0b5"></div>
                            </div>
                            <div style="flex-grow: 1;background-color: #1f6fb2">
                                <ul>
                                @foreach($roomUsers as $user)
                                    <li>{{ $user->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>



                </div>
            </div>
        </div>
        <script src="{{ asset('js/reconnecting-websocket.min.js') }}"></script>
        <script>
            $(function(){
                let token = $('#token').val();

                let roomId = $('#roomId').val();

                token && roomId && token !== '' && roomId !== '' && ws(roomId,token);
            });


            function ws(roomId,token){

                let ws = new ReconnectingWebSocket('ws://120.78.76.101:9502/?roomId='+roomId+'&token='+token);
                ws.onopen = function() {
                    console.log('open');
                    ws.send('test');
                };

                ws.onmessage = function(e) {
                    console.log('message', e.data);
                    ws.close();
                };

                ws.onclose = function() {
                    console.log('close');
                };
                ws.send('hello');
            }

        </script>
@endsection
