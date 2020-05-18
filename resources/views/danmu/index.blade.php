@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <sapn>当前工作任务</sapn>
                        <a href=""  data-toggle="modal" data-target="#createRoomModal"  type="button" class="btn btn-primary">添加</a>
                        <a href=""  data-toggle="modal" data-target="#createRoomModal"  type="button" class="btn btn-primary">停止</a>

                    </div>

                    <div class="card-body">
                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col">PID</th>
                                <th scope="col">标题</th>
                                <th scope="col">房间号</th>
                                <th scope="col">状态</th>
                                <th scope="col">操作</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($list as $item)
                                <tr>
                                    <th scope="row">{{ $item[0] }}</th>
                                    <td>{{ $item[3] }}</td>
                                    <td>{{ $item[2] }}</td>
                                    <td> </td>
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
