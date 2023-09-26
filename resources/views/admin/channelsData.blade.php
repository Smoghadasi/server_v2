@extends('layouts.dashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            پیام های کانال ها
        </li>
    </ol>

    <div class="container">


        <div class="col-md-12">
            <table class="table table-striped text-right">
                <thead>
                <tr>
                    <th>پیام ها</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>{{ $item->cargo }}</td>

                    </tr>
                @endforeach
                </tbody>
            </table>

            {{ $data }}
        </div>
    </div>
@stop
