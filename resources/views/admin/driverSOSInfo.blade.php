@extends('layouts.dashboard')
@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            اطلاعات درخواست امداد
        </li>
    </ol>
    @if(isset($message))
        <div class="alert alert-primary">{{ $message }}</div>
    @endif
    <div class="container">
        <table class="table table-bordered" cellspacing="0">
            <thead>
            <tr>
                <th>نام راننده</th>
                <th>شماره تلفن همراه</th>
                <th>تاریخ درخواست</th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-right">{{ $sosInfo->driver->name }} {{ $sosInfo->driver->lastName }}</td>
                    <td class="text-right">{{ $sosInfo->driver->mobileNumber }}</td>
                    <td class="text-right">{{ $sosInfo->requestDate }}</td>
                </tr>
            </tbody>
        </table>
        <div id="map" style="width:900px; height:580px;">
        </div>
    </div>
@stop