@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    لیست اسلایدر
                </div>
                <div class="col text-end">
                    <a href="{{ route('slider.create') }}" class="btn btn-primary">جدید</a>
                </div>
        </h5>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام</th>
                        {{-- <th>اولویت</th> --}}
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; ?>
                    @forelse ($sliders as $slider)
                        <tr>
                            <td>{{ ($sliders->currentPage() - 1) * $sliders->perPage() + ++$i }}</td>
                            <td>{{ $slider->name }}</td>
                            @switch($slider->status)
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
                                <a class="btn btn-info btn-sm" href="{{ route('slider.edit', $slider) }}">جزئیات</a>
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
                {{ $sliders }}
            </div>
        </div>
    @endsection
