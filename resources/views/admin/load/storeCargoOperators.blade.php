@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            <div class="row">
                <div class="col-6">
                    اپراتورها
                </div>
            </div>
        </h5>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        {{-- <th>تصویر</th> --}}
                        <th>نام و نام خانوادگی</th>
                        <th>تعداد بار های ثبت شده (کل)</th>
                        <th>تعداد بار های ثبت شده (امروز)</th>
                        <th>عملیات</th>
                        {{-- <th>کد ملی</th>
                        <th>موبایل</th>
                        <th>ایمیل</th> --}}
                        {{-- <th>جنسیت</th> --}}
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; ?>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ ++$i }}</td>
                            {{-- <td><img class="img-thumbnail" width="64" height="64"
                                    src="{{ url('pictures/users') }}/{{ $user->pic }}"></td> --}}
                            <td>
                                {{ $user->name }} {{ $user->lastName }}

                            </td>
                            <td>{{ $user->total_count }}</td>
                            <td>{{ $user->today_count }}</td>
                            <td>
                                <a href="{{ route('storeCargoOperator.show', ['storeCargoOperator' => $user->id]) }}" class="btn btn-primary">جزئیات ثبت بار های اخیر</a>
                            </td>
                            {{-- <td>{{ $user->mobileNumber }}</td>
                            <td>{{ $user->email }}</td> --}}
                            {{-- <td>
                            @if ($user->sex == 0)
                                خانم
                            @else
                                آقا
                            @endif
                        </td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

    <script>
        var toggler = document.getElementsByClassName("caret");
        var i;

        for (i = 0; i < toggler.length; i++) {
            toggler[i].addEventListener("click", function() {
                this.parentElement.querySelector(".nested").classList.toggle("active");
                this.classList.toggle("caret-down");
            });
        }
    </script>
@stop
