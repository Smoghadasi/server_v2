@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            لیست امدادها
        </h5>
        <div class="card-body">

            <form action="{{ url('admin/removeSOS') }}" method="post">
                @csrf
                <table class="table">
                    <thead>
                    <tr>
                        <th>انتخاب</th>
                        <th>#</th>
                        <th>نام راننده</th>
                        <th>شماره تلفن همراه</th>
                        <th>تاریخ درخواست</th>
                        <th>مکان درخواست امداد</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 0;?>
                    @foreach($sosLists as $sosItem)
                        <tr>
                            <td><input type="checkbox" name="sos_id[]" id="sos_id[]" value="{{ $sosItem->id }}">
                            </td>
                            <td>{{ ++$i }}</td>
                            <td>{{ $sosItem->name }} {{ $sosItem->lastName }}</td>
                            <td>{{ $sosItem->mobileNumber }}</td>
                            <td>{{ $sosItem->requestDate }}</td>
                            <td><a href="{{ url('admin/driverSOSInfo') }}/{{ $sosItem->id }}">نمایش جزئیات</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <button type="submit" class="btn mt-3 btn-danger">حذف دسته ای گزینه های انتخاب شده</button>
            </form>
            <div class="mt-3">
                {{ $sosLists }}
            </div>

        </div>
    </div>



    <script>
        var t = setInterval(function () {
            location.reload();
        }, 60000)
    </script>
@stop
