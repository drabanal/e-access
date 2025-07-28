@extends('mails.master')
@section('content')
<p>Hi {{ $leaveRequest->user->employee->empgname }},</p>
@if($leaveRequest->leave_status_id == \App\Models\LeaveStatus::TL_APPROVED)
<p>
    Your {{ strtoupper($leaveType->name) }} request on {{ \Carbon\Carbon::parse($leaveRequest->date_time_from)->format('m/d/Y') }}
    is now waiting for Admin's approval.
</p>
@else
<p>
    Your {{ strtoupper($leaveType->name) }} request on {{ \Carbon\Carbon::parse($leaveRequest->date_time_from)->format('m/d/Y') }}
    has been {{ strtoupper($leaveRequest->leaveStatus->name) }}.
</p>
<p>
    <strong>Reason: </strong> {{ $latestLeaveStatusLog->reason }}
</p>
@endif
@endsection