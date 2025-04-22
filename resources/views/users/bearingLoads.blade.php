@extends('layouts.dashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            لیست بارها
        </li>
        <li class="breadcrumb-item active">
            {{
            \App\Http\Controllers\BearingController::getBearingTitle($bearing_id)
            }}
        </li>
    </ol>
    @if(isset($message))
        <div class="alert alert-warning text-center">{{ $message }}</div>
    @else
    <div class="container">
        <div class="col-md-12">
            <div class="text-right">
                {{--<p><a class="btn btn-primary" href="{{ {{ route('operators.create') }} }}"> + افزودن اپراتور</a></p>--}}
                <div class="table-responsive">
                    <table class="table table-bordered" cellspacing="0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>عنوان بار</th>
                            <th>عرض</th>
                            <th>طول</th>
                            <th>ارتفاء</th>
                            <th>وزن</th>
                            <th>نمایش</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 0;?>
                        @foreach($loads as $load)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ $load->title }}</td>
                                <td>{{ $load->width }}</td>
                                <td>{{ $load->lenght }}</td>
                                <td>{{ $load->height }}</td>
                                <td>{{ $load->weight }}</td>
                                <td><a href="{{ url('admin/loadInfo') }}/{{ $load->id }}">نمایش جزئیات</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop
