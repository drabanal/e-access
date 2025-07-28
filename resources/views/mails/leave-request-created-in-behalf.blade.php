@extends('mails.master')
@section('content')
<p>Hi,</p>
<p>
    @if($leaveType->id == \App\Models\LeaveType::UNDERTIME) An UNDERTIME @else A {{ strtoupper($leaveType->name) }} @endif
    request on {{ \Carbon\Carbon::parse($leaveRequest->date_time_from)->format('m/d/Y') }} has been added on your behalf by {{ $supervisor->getFullNameAttribute() }}.
</p>
@endsection