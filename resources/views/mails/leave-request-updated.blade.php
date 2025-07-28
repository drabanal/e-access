@extends('mails.master')
@section('content')
<p>Hi,</p>
<p>
    {{ $employeeName }} has requested @if($leaveType->id == \App\Models\LeaveType::UNDERTIME) an undertime @else a {{ strtolower($leaveType->name) }} @endif
    on {{ \Carbon\Carbon::parse($leaveRequest->date_time_from)->format('m/d/Y') }}.
</p>
@endsection