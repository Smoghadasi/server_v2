@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    شماره های محدود شده ({{ LIMIT_OWNER_CALL }} تماس)
                </div>
                <div class="col text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCenter">
                        جدید
                    </button>
                    <!-- Modal -->
                    <div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalCenterTitle">شماره های محدود شده</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('limitCall.store') }}">
                                    @csrf

                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <input type="text" id="mobileNumber" name="mobileNumber"
                                                    class="form-control" placeholder="شماره موبایل..." />
                                            </div>
                                            <div class="col-6 mb-3">
                                                <select class="form-control form-select" name="type" id="type">
                                                    <option value="limitCall">محدودیت با 2 تماس </option>
                                                    <option value="time">زمان</option>
                                                </select>
                                            </div>
                                            <div class="col-6 mb-3" id="toggleTime" style="display: none;">
                                                <input class="form-control" type="number"
                                                    placeholder="تایم به دقیقه را وارد کنید" name="value">
                                            </div>
                                        </div>


                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            بستن
                                        </button>
                                        <button type="submit" class="btn btn-primary">ذخیره</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Search --}}
            <form method="get" action="{{ route('limitCall.index') }}">
                <div class="form-group row">
                    <div class="col-md-3">
                        <input type="text" placeholder="شماره موبایل..." class="form-control" name="mobileNumber"
                            id="mobileNumber" value="{{ request('mobileNumber') }}" />
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary">جستجو</button>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">شماره موبایل</th>
                            <th scope="col">اپراتور</th>
                            <th scope="col">نوع</th>
                            <th scope="col">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>

                        @forelse ($limitCalls as $key => $limitCall)
                            <tr>
                                <th scope="row">
                                    {{ ($limitCalls->currentPage() - 1) * $limitCalls->perPage() + ($key + 1) }}
                                </th>
                                <td>{{ $limitCall->mobileNumber }}</td>
                                <td>{{ $limitCall->operator?->name }} {{ $limitCall->operator?->lastName }}</td>
                                <td>
                                    @if ($limitCall->type == 'limitCall')
                                        محدودیت تماس
                                    @else
                                        محدود به زمان ({{ $limitCall->value }} دقیقه)
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#modal_{{ $limitCall->id }}">
                                        ویرایش
                                    </button>
                                    <form action="{{ route('limitCall.destroy', $limitCall) }}" method="post">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-danger">
                                            حذف
                                        </button>
                                    </form>

                                    <!-- Modal -->
                                    <div class="modal fade" id="modal_{{ $limitCall->id }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalCenterTitle">شماره های محدود شده</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="{{ route('limitCall.update', $limitCall) }}">
                                                    @csrf
                                                    @method('put')

                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-12 mb-3">
                                                                <input type="text" id="mobileNumber"
                                                                    value="{{ $limitCall->mobileNumber }}"
                                                                    name="mobileNumber" class="form-control"
                                                                    placeholder="شماره موبایل..." />
                                                            </div>
                                                            <div class="col-6 mb-3">
                                                                <select class="form-control form-select type-select"
                                                                    data-index="{{ $key }}" name="type">
                                                                    <option value="limitCall"
                                                                        @if ($limitCall->type == 'limitCall') selected @endif>
                                                                        محدودیت با 2 تماس</option>
                                                                    <option value="time"
                                                                        @if ($limitCall->type == 'time') selected @endif>
                                                                        زمان</option>
                                                                </select>
                                                            </div>

                                                            <div class="col-6 mb-3 toggle-time"
                                                                data-index="{{ $key }}" style="display: none;">
                                                                <input value="{{ $limitCall->value }}"
                                                                    class="form-control" type="number"
                                                                    placeholder="تایم به دقیقه را وارد کنید"
                                                                    name="value">
                                                            </div>
                                                        </div>


                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            data-bs-dismiss="modal">
                                                            بستن
                                                        </button>
                                                        <button type="submit" class="btn btn-primary">ذخیره</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse

                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $limitCalls->appends($_GET)->links() }}
            </div>

        </div>
    </div>
@endsection


@section('script')
    <script>
        $(document).ready(function() {
            function toggleTimeInput() {
                if ($('#type').val() === 'time') {
                    $('#toggleTime').show();
                } else {
                    $('#toggleTime').hide();
                }
            }

            // Run once on page load
            toggleTimeInput();

            // Run when the select changes
            $('#type').on('change', toggleTimeInput);




            function updateToggleTime() {
                $('.type-select').each(function() {
                    let index = $(this).data('index');
                    let value = $(this).val();
                    let toggleDiv = $('.toggle-time[data-index="' + index + '"]');

                    if (value === 'time') {
                        toggleDiv.show();
                    } else {
                        toggleDiv.hide();
                    }
                });
            }

            // در شروع صفحه بررسی شود
            updateToggleTime();

            // هنگام تغییر select
            $('.type-select').on('change', function() {
                let index = $(this).data('index');
                let value = $(this).val();
                let toggleDiv = $('.toggle-time[data-index="' + index + '"]');

                if (value === 'time') {
                    toggleDiv.show();
                } else {
                    toggleDiv.hide();
                }
            });
        });
    </script>
@endsection
