@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <sapn>添加房间</sapn>

                    </div>

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                        <div class="card-body">
                            <form  method="POST" action="{{ url('/IM') }}">
                                {{csrf_field()}}
                                <div class="form-group">
                                    <label for="exampleFormControlInput1">房间标题</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="请输入房间标题">
                                </div>
                                <div class="form-group">
                                    <label for="exampleFormControlInput1">房间最大人数</label>
                                    <input type="text" class="form-control" id="max" name="max" value="2" placeholder="房间最大人数">
                                </div>

                                <div class="form-group">
                                    <label for="exampleFormControlInput1">房间密码</label>
                                    <input type="text" class="form-control" id="pwd" name="pwd" placeholder="房间密码">
                                </div>

                                <button   type="submit" class="btn btn-primary">立即创建</button>

                                <a  href="{{ route('home') }}"  type="button" class="btn btn-primary">返回</a>
                            </form>
                        </div>



                </div>
            </div>
        </div>

@endsection
