@extends('layouts.dashboard')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <div class="col-md-12">
        @include('admin.service.header_partial')

        <div class="card">
            <h5 class="card-header">قوانین و مقررات</h5>
            <div class="card-body">
                <form id="myForm" onsubmit="return false;">
                    <div class="form-group text-right small">

                        <div dir="ltr" id="editor" style="height: 200px;">
                            {!! $ruleRegulation->description !!}
                        </div>

                        <!-- Hidden input to store editor content (optional) -->
                        <input type="hidden" name="description" id="description">
                    </div>

                    <div class="row form-group row mb-0">
                        <div class="col-md-6 offset-md-4">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                ذخیره
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const quill = new Quill('#editor', {
            theme: 'snow'
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#myForm').submit(function(e) {
            e.preventDefault();

            const description = quill.root.innerHTML;



            $('#submitBtn').attr('disabled', true).text('در حال ارسال...');

            $.ajax({
                url: '/admin/ruleRegulation/{{ $ruleRegulation->id }}',
                type: 'POST',
                data: {
                    _method: 'PUT', // Laravel expects this for PUT method spoofing
                    description: description
                },
                success: function(response) {
                    alert(response.message);
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
