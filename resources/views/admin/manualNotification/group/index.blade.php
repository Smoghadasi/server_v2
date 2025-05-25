@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    کمپین اعلان
                </div>
                <div class="col-6" style="text-align: left">
                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalCenter">
                        جدید
                    </button>
                    <!-- Modal -->
                    <div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalCenterTitle">جدید</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="{{ route('groupNotification.store') }}" method="POST">
                                    @csrf

                                    <div class="modal-body">
                                        <div class="row g-2">
                                            <div class="col-6 mb-0">
                                                {{-- <label for="title" class="form-label">نام گروه</label> --}}
                                                <input type="text" id="title" name="title" class="form-control"
                                                    placeholder="نام گروه..." />
                                            </div>
                                            <div class="col-6 mb-0">
                                                {{-- <label for="title" class="form-label">نام گروه</label> --}}
                                                <select class="form-control form-select" name="groupType" id="">
                                                    <option value="driver">رانندگان</option>
                                                    <option value="owner">صاحبین بار</option>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-0">
                                                {{-- <label for="description" class="form-label">توضیحات</label> --}}
                                                <textarea class="form-control" name="description" placeholder="توضیحات..." id="" cols="30" rows="10"></textarea>
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
            </div>
        </h5>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">نام</th>
                        <th scope="col">نوع گروه</th>
                        <th scope="col">توضیحات</th>
                        <th scope="col">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $group)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>
                                <a href="{{ route('groupNotification.show', $group) }}">
                                    {{ $group->title }} ({{ $group->manual_notification_recipients_count }})
                                </a>
                            </td>
                            <td>
                                {{ $group->groupType == 'driver' ? 'رانندگان' : 'صاحبین بار' }}
                            </td>
                            <td>
                                {{ $group->description ?? '-' }}
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modalCenter_{{ $group->id }}">
                                    ویرایش
                                </button>
                                <!-- Modal -->
                                <div class="modal fade" id="modalCenter_{{ $group->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalCenterTitle">جدید</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('groupNotification.update', $group) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <div class="modal-body">
                                                    <div class="row g-2">
                                                        <div class="col-12 mb-0">
                                                            {{-- <label for="title" class="form-label">نام گروه</label> --}}
                                                            <input type="text" id="title" name="title"
                                                                class="form-control" value="{{ $group->title }}" placeholder="نام گروه..." />
                                                        </div>
                                                        <div class="col-12 mb-0">
                                                            {{-- <label for="description" class="form-label">توضیحات</label> --}}
                                                            <textarea class="form-control" name="description" id="" cols="30" rows="10">{{ $group->description }}</textarea>
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="text-center">
                            <th colspan="10">اطلاعات مورد نظر یافت نشد</th>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endsection
