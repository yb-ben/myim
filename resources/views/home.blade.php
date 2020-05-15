@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <sapn>房间列表</sapn>
                    <a href="{{ url('/IM/create') }}"  data-toggle="modal" data-target="#createRoomModal"  type="button" class="btn btn-primary">创建房间</a>
                </div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">房间标题</th>
                            <th scope="col">人数</th>
                            <th scope="col">房主</th>
                            <th scope="col">操作</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($rooms as $room)
                            <tr>
                                <th scope="row">{{ $room->id }}</th>
                                <td>{{ $room->name }}</td>
                                <td>{{ $room->current }}/{{$room->max}}</td>
                                <td>{{ $room->user->name }}</td>
                                <td><a href="{{url('/IM/'.$room->id)}}" class="btn btn-primary" type="button">进入房间</a></td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>
                </div>



            </div>
        </div>
    </div>
</div>

@endsection
