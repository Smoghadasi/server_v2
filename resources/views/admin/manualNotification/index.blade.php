@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    گروه: {{ $groupNotification->title }}
                </div>
                <div class="col-6" style="text-align: left">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCenter">
                        جدید
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#notification">
                        ارسال اعلان
                    </button>
                    <div class="modal fade" id="notification" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="notificationTitle">ارسال اعلان</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="{{ route('manualNotification.sendNotification') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="type" value="driver">
                                    <div class="modal-body">
                                        <div class="row g-2">
                                            <div class="col-12 mb-0">
                                                <label for="mobileNumber" class="form-label">Title</label>
                                                <input type="text" id="mobileNumber" required name="title"
                                                    class="form-control" />
                                            </div>
                                            <div class="col-12 mb-0">
                                                <label for="type" class="form-label">Body</label>
                                                <textarea class="form-control" required name="body" id=""></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            انصراف
                                        </button>
                                        <button type="submit" class="btn btn-primary">ارسال</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalCenterTitle">جدید</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="{{ route('manualNotification.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="group_id" value="{{ $groupNotification->id }}">
                                    <div class="modal-body">
                                        <div class="row g-2">
                                            <div class="col mb-0">
                                                <label for="mobileNumber" class="form-label">شماره موبایل</label>
                                                <input type="text" id="mobileNumber" name="mobileNumber"
                                                    class="form-control" placeholder="شماره موبایل..." />
                                            </div>
                                            <div class="col mb-0">
                                                <label for="type" class="form-label">نوع</label>
                                                <select name="type" class="form-control form-select">
                                                    <option value="driver">راننده</option>
                                                    <option value="owner">صاحب بار</option>
                                                </select>
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
                        <th scope="col">کاربر</th>
                        <th scope="col">موبایل</th>
                        <th scope="col">نوع</th>
                        <th scope="col">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($manualNotifications as $manualNotification)
                        <tr>
                            <th scope="row">1</th>
                            <td>
                                {{ $manualNotification->userable->name }}
                                {{ $manualNotification->userable->lastName }}
                            </td>
                            <td>{{ $manualNotification->userable->mobileNumber }}</td>
                            <td>
                                {{ $manualNotification->userable instanceof App\Models\Driver ? 'راننده' : 'صاحب بار' }}
                            </td>
                            <td>
                                <form
                                    action="{{ route('manualNotification.destroy', $manualNotification) }}"
                                    method="POST">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr class="text-center">
                            <th colspan="10">اطلاعات مورد نظر یافت نشد</th>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $manualNotifications }}
        </div>
    @endsection
