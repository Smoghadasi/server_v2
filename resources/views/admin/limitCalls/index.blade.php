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
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">شماره موبایل</th>
                            <th scope="col">اپراتور</th>
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
                {{ $limitCalls }}
            </div>

        </div>
    </div>
@endsection
