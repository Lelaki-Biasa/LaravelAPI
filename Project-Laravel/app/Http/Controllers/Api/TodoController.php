<?php

namespace App\Http\Controllers\Api;

use App\Exports\TodosExport;
use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class TodoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'assignee' => 'nullable|string',
            'due_date' => 'required|date|after_or_equal:today',
            'time_tracked' => 'nullable|numeric|min:0',
            'status' => [
                'nullable',
                Rule::in(['pending', 'open', 'in_progress', 'completed'])
            ],
            'priority' => [
                'required',
                Rule::in(['low', 'medium', 'high'])
            ],
        ]);

        $validated['status'] = $validated['status'] ?? 'pending';

        $validated['time_tracked'] = $validated['time_tracked'] ?? 0;

        $todo = Todo::create($validated);

        return response()->json([
            'message' => 'Todo created successfully',
            'data' => $todo
        ], 201);
    }

    public function exportExcel(Request $request)
    {
        $query = Todo::query();

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('assignee')) {
            $assignees = explode(',', $request->assignee);
            $query->whereIn('assignee', $assignees);
        }

        if ($request->filled('status')) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('priority')) {
            $priorities = explode(',', $request->priority);
            $query->whereIn('priority', $priorities);
        }

        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('due_date', [$request->start, $request->end]);
        }

        if ($request->filled('min') && $request->filled('max')) {
            $query->whereBetween('time_tracked', [$request->min, $request->max]);
        }

        $todos = $query->get();

        return Excel::download(new TodosExport($todos), 'todo_report.xlsx');
    }
}
