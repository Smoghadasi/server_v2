@extends('layouts.dashboard')
@section('css')
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

    <style>
        #emoji-picker-container {
            position: fixed;
            z-index: 2000;
            /* بالاتر از modal */
            display: none;
        }
    </style>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    ارسال اعلان شخصی سازی شده
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
                                    <h5 class="modal-title" id="modalCenterTitle">اعلان</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form method="POST" action="{{ route('personalizedNotification.store') }}">
                                    @csrf

                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <select name="type" class="form-control form-select" id="">
                                                    <option value="driver">راننده</option>
                                                    <option value="owner">صاحب بار</option>
                                                </select>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <input type="number" id="version" name="version" class="form-control"
                                                    placeholder="ورژن..." />
                                            </div>
                                            <div class="col-12 mb-3">
                                                <input type="text" id="title" name="title" class="form-control"
                                                    placeholder="عنوان..." />
                                            </div>



                                            <div class="col-10 mb-3 position-relative">
                                                <textarea id="desc-textarea" class="form-control" name="body" rows="5" placeholder="متن توضیحات"></textarea>

                                                <!-- container for emoji picker (داخل همون div تا کنار textarea قرار بگیره) -->
                                                <div id="emoji-picker-container"
                                                    style="display: none; position: absolute; top: 100%; right: 0; z-index: 1000;">
                                                </div>
                                            </div>
                                            <div class="col-2 mb-3 d-flex align-items-start">
                                                <span id="emoji-btn" class="btn btn-outline-secondary w-100">😊</span>
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
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ردیف</th>
                            <th>نوع</th>
                            <th>ورژن</th>
                            <th>عنوان</th>
                            <th>توضیحات</th>
                            <th>اپراتور</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 1; ?>
                        @forelse ($personalizedNotifications as $key => $personalizedNotification)
                            <tr>
                                <td>
                                    {{ ($personalizedNotifications->currentPage() - 1) * $personalizedNotifications->perPage() + ($key + 1) }}
                                </td>
                                <td>{{ $personalizedNotification->type == 'driver' ? 'راننده' : 'صاحب بار' }}</td>
                                <td>{{ $personalizedNotification->version }}</td>
                                <td>{{ $personalizedNotification->title ?? '-' }}</td>
                                <td>{{ $personalizedNotification->body }}</td>
                                <td>
                                    {{ $personalizedNotification->user?->name }}
                                    {{ $personalizedNotification->user?->lastName }}
                                </td>
                                <td>{{ $personalizedNotification->status ? 'فعال' : 'غیر فعال' }}</td>
                                <td>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#modalCenter_{{ $personalizedNotification->id }}">
                                        ویرایش
                                    </button>
                                    <form class="d-inline" action="{{ route('personalizedNotification.destroy', $personalizedNotification) }}" method="post">
                                        @method('delete')
                                        @csrf
                                        <button class="btn btn-danger">حذف</button>
                                    </form>
                                    <!-- Modal -->
                                    <div class="modal fade" id="modalCenter_{{ $personalizedNotification->id }}"
                                        tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalCenterTitle">ویرایش اعلان</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form method="POST"
                                                    action="{{ route('personalizedNotification.update', $personalizedNotification) }}">
                                                    @csrf
                                                    @method('put')

                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-6 mb-3">
                                                                <select name="type" class="form-control form-select"
                                                                    id="">
                                                                    <option
                                                                        @if ($personalizedNotification->type == 'driver') selected @endif
                                                                        value="driver">راننده</option>
                                                                    <option
                                                                        @if ($personalizedNotification->type == 'owner') selected @endif
                                                                        value="owner">صاحب بار</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-6 mb-3">
                                                                <input type="number" id="version" name="version"
                                                                    class="form-control"
                                                                    value="{{ $personalizedNotification->version }}"
                                                                    placeholder="ورژن..." />
                                                            </div>
                                                            <div class="col-12 mb-3">
                                                                <input type="text"
                                                                    value="{{ $personalizedNotification->title }}"
                                                                    id="title" name="title" class="form-control"
                                                                    placeholder="عنوان..." />
                                                            </div>

                                                            <div class="col-12">
                                                                <textarea class="form-control" name="body" id="" cols="15" rows="5"
                                                                    placeholder="متن توضیحات">{{ $personalizedNotification->body }}</textarea>
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
                {{ $personalizedNotifications->appends($_GET)->links() }}

            </div>

        </div>
    </div>
@endsection
@section('script')
    <script>
        const emojiBtn = document.getElementById("emoji-btn");
        const emojiContainer = document.getElementById("emoji-picker-container");
        const textarea = document.getElementById("desc-textarea");

        // ایجاد picker فقط یک بار
        const picker = document.createElement("emoji-picker");
        picker.style.width = "300px"; // یا هر اندازه‌ای که خواستی
        picker.addEventListener("emoji-click", (event) => {
            textarea.value += event.detail.unicode;
            emojiContainer.style.display = "none";
        });

        emojiContainer.appendChild(picker);

        emojiBtn.addEventListener("click", () => {
            emojiContainer.style.display = emojiContainer.style.display === "none" ? "block" : "none";
        });

        document.addEventListener("click", (e) => {
            if (!emojiContainer.contains(e.target) && e.target !== emojiBtn) {
                emojiContainer.style.display = "none";
            }
        });
    </script>
@endsection
