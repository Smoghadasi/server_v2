@extends('layouts.dashboard')
@section('css')
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
@endsection
@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row">
                <div class="col-6">
                    استعلام کرایه حمل
                </div>
                <div class="col-6 text-end">
                    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd"
                        aria-controls="offcanvasEnd">
                        جدید
                    </button>
                    <form action="{{ route('freightInquiries.store') }}" method="post">
                        @csrf
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd"
                            aria-labelledby="offcanvasEndLabel">
                            <div class="offcanvas-header">
                                <h5 id="offcanvasEndLabel" class="offcanvas-title">کرایه حمل جدید</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                                    aria-label="Close"></button>
                            </div>

                            <div class="offcanvas-body my-auto mx-0 flex-grow-0">

                                <div class="mb-2">
                                    {{-- <label for="defaultFormControlInput" class="form-label">ناوگان</label> --}}
                                    <select class="form-control form-select" name="fleet_id" id="fleet-select-from">

                                        @foreach ($fleets as $fleet)
                                            <option value="{{ $fleet->id }}">{{ $fleet->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2">
                                    {{-- <label for="from_city_id" class="form-label">شهر مبدا</label> --}}

                                    <select class="form-control form-select" name="from_city_id" id="city-select-from">
                                        @foreach ($cities as $city)
                                            <option value="{{ $city->id }}">{{ $city->name }} ({{ $city->state }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    {{-- <label for="from_city_id" class="form-label">شهر مقصد</label> --}}
                                    <select class="form-control form-select" name="to_city_id" id="city-select-to">
                                        @foreach ($cities as $city)
                                            <option value="{{ $city->id }}">{{ $city->name }} ({{ $city->state }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <input type="text" class="form-control" name="price" placeholder="قیمت (تومان)"
                                        value="0">
                                </div>

                            </div>
                            <div class="offcanvas-footer">
                                <button type="submit" class="btn btn-primary mb-2 d-grid w-100">ثبت</button>
                                <button type="button" class="btn btn-outline-secondary d-grid w-100"
                                    data-bs-dismiss="offcanvas">
                                    انصراف
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </h5>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ناوگان</th>
                            <th>مبدا</th>
                            <th>مقصد</th>
                            <th>مبلغ</th>
                            <th>وضعیت</th>
                            <th>تاریخ ثبت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 1; ?>
                        @forelse ($freightInquiries as $key => $freightInquiry)
                            <tr class="">
                                <td>{{ ($freightInquiries->currentPage() - 1) * $freightInquiries->perPage() + ($key + 1) }}
                                </td>
                                <td>
                                    {{ $freightInquiry->fleet->title }}
                                </td>

                                <td>
                                    {{ $freightInquiry->fromCity->name }} ({{ $freightInquiry->fromCity->state }})
                                </td>
                                <td>
                                    {{ $freightInquiry->toCity->name }} ({{ $freightInquiry->toCity->state }})
                                </td>
                                <td>
                                    {{ number_format($freightInquiry->price) }} تومان
                                </td>
                                <td>
                                    @switch($freightInquiry->status)
                                        @case(1)
                                            <span class="text-success">ثبت موفق</span>
                                        @break

                                        @default
                                            <span class="text-warning">در حال بررسی</span>
                                    @endswitch
                                </td>
                                @php
                                    $pieces = explode(' ', $freightInquiry->created_at);
                                @endphp
                                <td>
                                    {{ gregorianDateToPersian($freightInquiry->created_at, '-', true) }}
                                </td>


                                <td class="d-flex">
                                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvasEnd_{{ $freightInquiry->id }}"
                                        aria-controls="offcanvasEnd">
                                        ویرایش
                                    </button>
                                    <form action="{{ route('freightInquiries.destroy', $freightInquiry) }}" method="POST">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger">حذف</button>
                                    </form>
                                    <form action="{{ route('freightInquiries.update', $freightInquiry) }}" method="post">
                                        @csrf
                                        @method('put')
                                        <div class="offcanvas offcanvas-end" tabindex="-1"
                                            id="offcanvasEnd_{{ $freightInquiry->id }}"
                                            aria-labelledby="offcanvasEndLabel">
                                            <div class="offcanvas-header">
                                                <h5 id="offcanvasEndLabel" class="offcanvas-title">ویرایش</h5>
                                                <button type="button" class="btn-close text-reset"
                                                    data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                            </div>

                                            <div class="offcanvas-body my-auto mx-0 flex-grow-0">

                                                <div class="mb-2">
                                                    <label for="defaultFormControlInput" class="form-label">ناوگان</label>
                                                    <select class="form-control form-select" name="fleet_id"
                                                        id="fleet-select-from">

                                                        @foreach ($fleets as $fleet)
                                                            <option value="{{ $fleet->id }}"
                                                                @if ($freightInquiry->fleet_id == $fleet->id) selected @endif>
                                                                {{ $fleet->title }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-2">
                                                    <label for="from_city_id" class="form-label">شهر مبدا</label>

                                                    <select class="form-control form-select" name="from_city_id"
                                                        id="city-select-from">
                                                        @foreach ($cities as $city)
                                                            <option value="{{ $city->id }}"
                                                                @if ($freightInquiry->from_city_id == $city->id) selected @endif>
                                                                {{ $city->name }}
                                                                ({{ $city->state }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label for="from_city_id" class="form-label">شهر مقصد</label>
                                                    <select class="form-control form-select" name="to_city_id"
                                                        id="city-select-to">
                                                        @foreach ($cities as $city)
                                                            <option value="{{ $city->id }}"
                                                                @if ($freightInquiry->to_city_id == $city->id) selected @endif>
                                                                {{ $city->name }}
                                                                ({{ $city->state }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label for="from_city_id" class="form-label">قیمت (تومان)</label>

                                                    <input type="text" class="form-control" name="price"
                                                        placeholder="قیمت (تومان)" value="{{ $freightInquiry->price }}">
                                                </div>
                                                <div class="mb-2">
                                                    <label for="status" class="form-label">وضعیت</label>

                                                    <select name="status" class="form-control form-select">
                                                        <option @if ($freightInquiry->status == 0) selected @endif
                                                            value="0">در حال بررسی</option>
                                                        <option value="1"
                                                            @if ($freightInquiry->status == 1) selected @endif>تایید</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="offcanvas-footer">
                                                <button type="submit"
                                                    class="btn btn-outline-primary mb-2 d-grid w-100">ویرایش</button>
                                            </div>
                                        </div>
                                    </form>
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
                </div>

                <div class="mt-3">
                    {{ $freightInquiries }}
                </div>
            </div>
        </div>
    @endsection
    @section('script')
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const element = document.getElementById('city-select-from');
                const choicesCityFrom = new Choices(element, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                    placeholderValue: 'انتخاب شهر مبدا',
                });

                const elementCityTo = document.getElementById('city-select-to');
                const choicesCityTo = new Choices(elementCityTo, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                    placeholderValue: 'انتخاب شهر مقصد',
                });

                const elementFleet = document.getElementById('fleet-select-from');
                const choicesFleet = new Choices(elementFleet, {
                    searchEnabled: true,
                    itemSelectText: '',
                    shouldSort: false,
                    placeholderValue: 'انتخاب ناوگان',
                });
            });
        </script>
    @endsection
