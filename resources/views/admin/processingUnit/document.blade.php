@extends('layouts.dashboard')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <div class="col-md-12">

        <div class="card">
            <h5 class="card-header">مستندات هوش مصنوعی
                <button type="button" class="btn btn-outline-success mb-2 float-right btn-sm" id="copyBtn">
                    کپی (Copy)
                </button>
            </h5>
            <div class="card-body">
                <p>لیست افرادی که این متن امروز کپی کرده اند:</p>
                @if (Auth::user()->role == 'admin')
                    <div class="m-2">
                        @foreach ($users as $promp)
                            <span class="table-bordered border-info rounded bg-white p-1 m-1">
                                <span class="text-primary">{{ $promp->user->name }} {{ $promp->user->lastName }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif

                <form id="myForm" onsubmit="return false;">
                    <div class="form-group text-right small">

                        <div dir="ltr" id="editor" style="height: 200px;">
                            {!! $setting->document_smart_cargo !!}
                        </div>

                        <!-- Hidden input to store editor content (optional) -->
                        <input type="hidden" name="document_smart_cargo" id="document_smart_cargo">
                    </div>

                    @if (Auth::user()->role == 'admin')
                        <div class="row form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    ذخیره
                                </button>
                            </div>
                        </div>
                    @endif

                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#copyBtn').on('click', function() {
            // گرفتن متن از ویرایشگر
            var text = $('#editor').text(); // اگر HTML می‌خوای، از .html() استفاده کن

            // ایجاد یک عنصر موقت برای کپی
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();

            // اجرای دستور کپی
            document.execCommand('copy');

            // حذف عنصر موقت
            $temp.remove();

            $.ajax({
                url: '/admin/prompAi',
                type: 'get',
                success: function(response) {
                    alert('متن با موفقیت کپی شد!');
                    location.reload();
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let errorText = Object.values(errors).flat().join('\n');
                        alert('خطا:\n' + errorText);
                    } else {
                        alert('خطای نامشخص رخ داد.');
                    }
                    console.error(xhr.responseJSON);
                },
            });
            // نمایش پیام موفقیت (اختیاری)

        });

        const quill = new Quill('#editor', {
            theme: 'snow'
        });



        $('#myForm').submit(function(e) {
            e.preventDefault();

            const document_smart_cargo = quill.root.innerHTML;



            $('#submitBtn').attr('disabled', true).text('در حال ارسال...');

            $.ajax({
                url: '/admin/documentSmartCargo/1',
                type: 'POST',
                data: {
                    _method: 'PATCH', // Laravel expects this for PUT method spoofing
                    document_smart_cargo: document_smart_cargo
                },
                success: function(response) {
                    console.log(response);
                    alert('با موفقیت ذخیره شد');
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let errorText = Object.values(errors).flat().join('\n');
                        alert('خطا:\n' + errorText);
                    } else {
                        alert('خطای نامشخص رخ داد.');
                    }
                    console.error(xhr.responseJSON);
                },
                complete: function() {
                    $('#submitBtn').attr('disabled', false).text('ذخیره');
                }
            });
        });
    </script>
@endsection
