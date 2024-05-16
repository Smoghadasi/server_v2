@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-pills flex-column flex-md-row mb-3">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('user.edit') }}"><i class="bx bx-user me-1"></i> حساب کاربری</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('login.history') }}"><i class="bx bx-history me-1"></i> تاریخچه ورود و
                        خروج</a>
                </li>
            </ul>
            <div class="card">
                <h5 class="card-header">
                    <div class="row justify-content-between">
                        <div class="col-4">
                            تاریخچه ورود و خروج
                        </div>
                    </div>
                </h5>
                <div class="card-body">
                    <div class="table-responsive mt-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">ردیف</th>
                                    <th class="text-center">IP Address</th>
                                    <th class="text-center">عملیات</th>
                                    <th class="text-center">تاریخ</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <?php $i = 0; ?>
                                @forelse ($loginHistories as $loginHistory)
                                    <tr>
                                        <td class="text-center">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="text-center">
                                            {{ $loginHistory->ip_address ?? '-' }}
                                        </td>
                                        @switch($loginHistory->action)
                                            @case(0)
                                                <td class="text-danger text-center">خروج</td>
                                            @break

                                            @default
                                                <td class="text-success text-center">ورود</td>
                                        @endswitch
                                        @php
                                            $pieces = explode(' ', $loginHistory->created_at);
                                        @endphp
                                        <td class="text-center" dir="ltr">
                                            {{ gregorianDateToPersian($loginHistory->created_at, '-', true) . ' | ' . $pieces[1] }}
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                موردی ثبت نشده است.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mt-2">
                                {{ $loginHistories }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
