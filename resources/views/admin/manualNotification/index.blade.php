@extends('layouts.dashboard')
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tagify/4.17.9/tagify.css" />
@endsection

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    گروه {{ $groupNotification->title }}
                    ({{ $groupNotification->groupType == 'driver' ? 'رانندگان' : 'صاحبین بار' }})
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
                                            <input type="hidden" value="{{ $groupNotification->id }}" name="group_id">
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
                                        @if ($groupNotification->groupType == 'driver')
                                            <select class="form-control form-select mb-2" name="" id="changeType">
                                                <option value="single">تکی</option>
                                                <option value="multi">چندتایی</option>
                                            </select>
                                        @endif
                                        <div class="row g-2" id="single">
                                            <div class="col mb-0">
                                                <input id="mobileNumberGroup" name="mobiles" class="form-control"
                                                    placeholder="شماره موبایل..." />
                                            </div>
                                        </div>

                                        @if ($groupNotification->groupType == 'driver')
                                            <div class="row g-2" id="multi">
                                                <div class="col-12">
                                                    <select class="form-control form-select" name="provinces[]" multiple>
                                                        <option value="">استان مبدا</option>
                                                        @foreach ($provinces as $province)
                                                            <option value="{{ $province->id }}">
                                                                <?php echo str_replace('ك', 'ک', str_replace('ي', 'ی', $province->name)); ?>
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <select class="form-control col-md-4" name="fleets[]" id="fleets"
                                                        multiple>
                                                        <option value="" disabled>نوع ناوگان</option>
                                                        @foreach ($fleets as $fleet)
                                                            <option value="{{ $fleet->id }}">
                                                                {{ \App\Http\Controllers\FleetController::getFleetName($fleet->parent_id) }}
                                                                -
                                                                {{ $fleet->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                                <div class="col-12">
                                                    <input name="count" type="number" value="10"
                                                        class="form-control" placeholder="تعداد">
                                                </div>
                                            </div>
                                        @endif
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
                    <?php $i = 0; ?>

                    @forelse ($manualNotifications as $manualNotification)
                        <tr>
                            <td>{{ ($manualNotifications->currentPage() - 1) * $manualNotifications->perPage() + ++$i }}
                            </td>
                            <td>
                                {{ $manualNotification->userable->name }}
                                {{ $manualNotification->userable->lastName }}
                                ({{ $groupNotification->groupType === 'driver' ? $manualNotification->userable->fleetTitle : 'vvv' }})
                            </td>
                            <td>{{ $manualNotification->userable->mobileNumber }}</td>
                            <td>
                                {{ $manualNotification->userable instanceof App\Models\Driver ? 'راننده' : 'صاحب بار' }}
                            </td>
                            <td>
                                <form action="{{ route('manualNotification.destroy', $manualNotification) }}"
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

    @section('script')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tagify/4.17.9/tagify.min.js"></script>

        <script>
            const input = document.getElementById('mobileNumberGroup');

            // فعال کردن Tagify
            const tagify = new Tagify(input, {
                delimiters: ",|\n|\r|\t| ", // با کاما، خط جدید یا فاصله جدا کن
                // pattern: /^09\d{9}$/, // فقط شماره موبایل ایران
                placeholder: "شماره موبایل وارد کنید یا چندتا کپی‌پیست کنید...",
                trim: true
            });
        </script>


        <script>
            $(document).ready(function() {
                $('#multi').hide();
                $('#changeType').change(function() {
                    if ($(this).val() === 'multi') {
                        $('#single').hide();
                        $('#multi').show();
                        $('#mobileNumberGroup').val(''); // Reset input field
                    }
                    if ($(this).val() === 'single') {
                        $('#single').show();
                        $('#multi').hide();
                    }
                });
            });
        </script>
    @endsection
