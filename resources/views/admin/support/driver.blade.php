@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            تماس های پشتیبانی
        </h5>
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>راننده</th>
                        <th>اپراتور</th>
                        <th>نتیجه</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody class="small text-right">
                    <?php $i = 1; ?>
                    @forelse ($supports as $key => $support)
                        <tr class="text-center">
                            <td>{{ ($supports->currentPage() - 1) * $supports->perPage() + ($key + 1) }}</td>
                            <td>
                                @if ($support->driver)
                                    <a href="{{ route('driver.detail', $support->driver_id) }}">
                                        {{ $support->driver ? $support->driver->name . ' ' . $support->driver->lastName . ' ( ' . $support->driver->mobileNumber . ' )' : '-' }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                {{ $support->user ? $support->user->name . ' ' . $support->user->lastName : '-' }}
                            </td>

                            <td>
                                {{ $support->result ?? '-' }}
                            </td>
                            @php
                                $pieces = explode(' ', $support->created_at);
                            @endphp
                            <td>
                                {{ gregorianDateToPersian($support->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary mb-3 btn-sm text-nowrap"

                                        data-bs-toggle="modal"
                                        data-bs-target="#adminMessageForm_{{ $support->id }}">
                                    ثبت نتیحه
                                </button>
                                <div id="adminMessageForm_{{ $support->id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <form action="{{ route('admin.indexDriver.update', $support) }}"
                                            method="post"
                                            class="modal-content">
                                            @csrf
                                            @method('put')
                                            <div class="modal-header">
                                                <h4 class="modal-title">نتیجه</h4>
                                            </div>
                                            <div class="modal-body text-right">

                                                <div>
                                                    راننده :
                                                    {{ $support->driver->name . ' ' . $support->driver->lastName }}
                                                </div>

                                                <div class="form-group">
                                                    <label>نتیجه :</label>
                                                    <textarea class="form-control" name="result" id="result"
                                                              placeholder="پاسخ"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer text-left">
                                                <button type="submit" class="btn btn-primary mr-1">ثبت پاسخ</button>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                    انصراف
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="text-center">
                            <td colspan="10">
                                دیتا مورد نظر یافت نشد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
                <div class="mt-3">
                    {{ $supports }}
                </div>
        </div>
    </div>

@endsection
