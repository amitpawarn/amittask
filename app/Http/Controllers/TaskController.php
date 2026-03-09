<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::with('project','user')->get(); 
        return response()->json($tasks);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'      => ['required', 'integer', 'exists:users,id'],
            'task_name'    => ['required', 'string', 'max:255'],
            'project_id'   => ['required', 'integer', 'exists:projects,id'],
            'status'       => ['required', 'in:pending,on-going,testing,done'],
            'start_date'   => ['required', 'date'],
            'due_date'     => ['required', 'date'],
        ]);

        $project = Projects::find($data['project_id']);
        $data['project_name'] = $project->name;
        $task = Task::create($data);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $tasks = Task::with('project','user')->where('id',$task->id)->first();
        return response()->json($task);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'user_id'      => ['sometimes', 'integer', 'exists:users,id'],
            'task_name'    => ['sometimes', 'string', 'max:255'],
            'project_id'   => ['sometimes', 'integer', 'exists:projects,id'],
            'project_name' => ['sometimes', 'string', 'max:255'],
            'status'       => ['sometimes', 'in:pending,on-going,testing,done'],
            'start_date'   => ['sometimes', 'date'],
            'due_date'     => ['sometimes', 'date'],
        ]);

        $task->update($data);

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(null, 204);
    }
}
