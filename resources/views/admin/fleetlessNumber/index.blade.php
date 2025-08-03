@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">شماره های بار بدون ناوگان</h5>
            <div>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalFleetless">
                    جدید
                </button>
                <!-- Modal -->
                <div class="modal fade" id="modalFleetless" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <form action="{{ route('fleetlessNumber.store') }}" method="POST" class="modal-content">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalCenterTitle">شماره موبایل</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="mobileNumber" class="form-label">شماره موبایل</label>
                                        <input type="text" id="mobileNumber" name="mobileNumber" class="form-control"
                                            required />
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    انصراف
                                </button>
                                <button type="submit" class="btn btn-primary">ذخیره</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>#</th>
                        <th>شماره موبایل</th>
                        <th>تاریخ</th>
                        <th>عملیات</th>
                    </tr>
                    <?php $i = 0; ?>

                    @forelse ($fleetlessNumbers as $fleetlessNumber)
                        <tr>
                            <td>{{ ($fleetlessNumbers->currentPage() - 1) * $fleetlessNumbers->perPage() + ++$i }}</td>

                            <td>{{ $fleetlessNumber->mobileNumber }}</td>
                            @php
                                $pieces = explode(' ', $fleetlessNumber->created_at);
                            @endphp
                            <td>{{ gregorianDateToPersian($fleetlessNumber->created_at, '-', true) . ' ' . $pieces[1] }}
                            </td>
                            <td class="d-flex">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalFleetless_{{ $fleetlessNumber->id }}">
                                    ویرایش
                                </button>
                                <form action="{{ route('fleetlessNumber.destroy', $fleetlessNumber) }}" method="POST">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">حذف</button>
                                </form>
                                <!-- Modal -->
                                <div class="modal fade" id="modalFleetless_{{ $fleetlessNumber->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <form action="{{ route('fleetlessNumber.update', $fleetlessNumber) }}" method="POST"
                                            class="modal-content">
                                            @csrf
                                            @method('put')
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalCenterTitle">شماره موبایل</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col mb-3">
                                                        <label for="mobileNumber" class="form-label">شماره موبایل</label>
                                                        <input type="text" id="mobileNumber" value="{{ $fleetlessNumber->mobileNumber }}" name="mobileNumber"
                                                            class="form-control" required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">
                                                    انصراف
                                                </button>
                                                <button type="submit" class="btn btn-primary">ذخیره</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="text-center">
                            <td colspan="10">دیتا مورد نظر یافت نشد</td>
                        </tr>
                    @endforelse
                </table>
                <div class="mt-2">
                    {{ $fleetlessNumbers->appends($_GET)->links() }}
                </div>
            </div>
        </div>

    </div>


@stop
