@extends('layouts.dashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            کانال ها
        </li>
    </ol>

    <div class="container">

        <form class="form-group row" method="post" action="{{ url('admin/newChannel') }}">
            @csrf
            <input class="form-control col-md-6" type="text" placeholder="نام کانال" name="channelName">
            <button class="btn btn-primary" type="submit">اضافه کردن کانال جدید</button>
        </form>
        <div class="col-md-12">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>نام کانال</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($result['channels'] as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>
                            <a class="btn btn-danger" href="{{ url('admin/removeChannel') }}/{{ $item['name'] }}">حذف
                                کانال</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
