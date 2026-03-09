<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Projects::with('tasks','user')->get();
        return response()->json($projects);
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
            'user_id'    => ['required', 'integer', 'exists:users,id'],
            'name'       => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date'],
        ]);

        $project = Projects::create($data);

        return response()->json($project, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Projects $project)
    {
        //dd($project);
        $data = Projects::with('tasks','user')->where('id',$project->id)->first();
        return response()->json($data);
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
    public function update(Request $request, Projects $project)
    {
        $data = $request->validate([
            'user_id'    => ['sometimes', 'integer', 'exists:users,id'],
            'name'       => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date'   => ['sometimes', 'date'],
        ]);

        $project->update($data);

        return response()->json($project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Projects $project)
    {
        $project->delete();

        return response()->json(null, 204);
    }
}
