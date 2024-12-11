@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    لیست رادیو
                </div>
                <div class="col text-end">
                    <a href="{{ route('radio.create') }}" class="btn btn-primary">جدید</a>
                </div>
        </h5>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام</th>
                        <th>نام رادیو</th>
                        <th>وضعیت</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; ?>
                    @forelse ($radios as $radio)
                        <tr>
                            <td>{{ ($radios->currentPage() - 1) * $radios->perPage() + ++$i }}</td>
                            <td>{{ $radio->name }}</td>
                            <td>{{ $radio->artist }}</td>
                            @switch($radio->status)
                                @case(0)
                                    <td class="text-danger">
                                        غیر فعال
                                    </td>
                                @break

                                @case(1)
                                    <td class="text-success">
                                        فعال
                                    </td>
                                @break
                            @endswitch
                            <td>
                                {{ $radio->persianDate }}
                            </td>
                            <td>
                                <a class="btn btn-info btn-sm" href="{{ route('radio.show', $radio) }}">جزئیات</a>
                            </td>
                        </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="10">هیچ دیتایی وجود ندارد</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2 mb-2">
                {{ $radios }}
            </div>
        </div>

    @stop
