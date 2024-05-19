@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="card mb-4">
                <h5 class="card-header">
                    تنظیمات سامانه
                </h5>
                <div class="card-body">
                    <form action="{{ route('setting.update', $setting) }}" method="post">
                        @csrf
                        @method('put')
                        <div class="row">
                            @if (Auth::id() == 40)
                                <div class="mb-3 col-md-6">
                                    <label for="tel" class="form-label">تلفن</label>
                                    <select id="tel" name="tel" class="select2 form-select">
                                        <option value="02128420609" @if($setting->tel == '02128420609') selected @endif>02128420609</option>
                                        <option value="02191097220" @if($setting->tel == '02191097220') selected @endif>02191097220</option>
                                        <option value="09184696188" @if($setting->tel == '09184696188') selected @endif>09184696188</option>
                                        <option value="08338390328" @if($setting->tel == '08338390328') selected @endif>08338390328</option>
                                    </select>
                                </div>
                            @endif
                            <div class="mb-3 col-md-6">
                                <label for="sms_panel" class="form-label">پنل اس ام اس</label>
                                <select id="sms_panel" name="sms_panel" class="select2 form-select">
                                    <option value="SMSIR" @if($setting->sms_panel == 'SMSIR') selected @endif>SMSIR</option>
                                    <option value="Faraz" @if($setting->sms_panel == 'Faraz') selected @endif>Faraz SMS</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2">ذخیره</button>
                        </div>
                    </form>
                </div>
                <!-- /Account -->
            </div>
        </div>
    </div>
@endsection
