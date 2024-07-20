<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type)
    {
        $reports = Report::with(['cargo' => function ($query) {
            $query->withTrashed();
        }, 'driver', 'owner'])
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('admin.report.index', compact('reports'));
    }

    public function update(Request $request, Report $report)
    {
        $report->adminMessage = $request->input('adminMessage');
        $report->save();
        return back()->with('success', 'پاسخ مورد نظر ثبت شد');
    }
}
