@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            لیست بارهای
            {{ \App\Http\Controllers\BearingController::getBearingTitle($bearing_id) }}
        </h5>
        <div class="card-body">
            <table class="table">
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

@stop
