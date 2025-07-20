<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Services\LeaveService;
use Inertia\Inertia;

class CalendarController extends Controller
{
    protected $leaveService;

    public function __construct()
    {
        $this->leaveService = new LeaveService();
    }

    public function showCalendarPage()
    {
        $leaveTypes = [
            [
                'name' => 'All Leaves',
                'id' => 0
            ]
        ];

        foreach (LeaveType::all() as $leaveType) {
            array_push($leaveTypes, [
                'name' => $leaveType->name,
                'id' => $leaveType->id
            ]);
        }

        $items = $this->leaveService->getAllApprovedRequests();

        $leaveTypeColors = ['#fb2c37','#00c950','#fe6900','#2c7fff','#2c7fff','#6a7281','#ff8a00',];

        $items->transform(function ($item) use ($leaveTypeColors) {
            return (object) [
                'title' => $item->title,
                'start' => $item->start,
                'color' => $leaveTypeColors[$item->leave_type_id - 1]
            ];
        });

        return Inertia::render('Calendar', ['leaveTypes' => $leaveTypes, 'items' => $items]);
    }
}