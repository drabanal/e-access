@extends('mails.master')
@section('content')
<p>Hi,</p>
<p>
    You have added @if($leaveType->id == \App\Models\LeaveType::UNDERTIME) an UNDERTIME @else a {{ strtoupper($leaveType->name) }} @endif
    request on {{ \Carbon\Carbon::parse($leaveRequest->date_time_from)->format('m/d/Y') }} for {{ $employee->getFullNameAttribute() }}.
</p>
@endsection