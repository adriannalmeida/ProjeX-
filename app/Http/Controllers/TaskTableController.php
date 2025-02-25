<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TaskTable;
use App\Models\Project;
use App\Models\Account;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;



class TaskTableController extends Controller{

    /**
     * Search tasks
     */
    public function searchAjax(Project $project, Request $request)
    {
        try {
            $this->authorize('view', $project);
        }
        catch (\Exception $e) {
            return redirect()->route('projects');
        }
        $search = $request->input('search', '');
        $priority = $request->input('filter', null); // Fetch the priority filter
        $tasks = $project->taskTables()->with(['tasks' => function ($query) use ($search, $priority) {
            if ($search) {
                $query->selectraw(
                    "*, ts_rank(tsvectors, to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')) as rank",
                    [$search]
                )->whereRaw(
                    "tsvectors @@ to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')",
                    [$search]
                );
            }
            if ($priority) {
                $query->where('priority', $priority); // Apply priority filter
            }
            $query->orderByDesc('rank');
        }])->where('position', '>', 0)
            ->orderBy('position')
            ->limit(10)
            ->get();

        return response()->json(['taskTables' => $tasks]);
    }


    /**
     * Store a newly created task table.
     * */
    public function store(Project $project, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $this->authorize('createTaskTable', $project);
        }
        catch (\Exception $e) {
            return redirect()->route('projects');
        }
        // Check if the task table name already exists within the given project
        $existingTaskTable = $project->taskTables()->where('name', $request->name)->first();

        // If the name exists, return an error response
        if ($existingTaskTable) {
            return response()->json([
                'errors' => [
                    'name' => ['The task table name already exists in this project.'],
                ]
            ], 422); // 422 Unprocessable Entity (validation error)
        }

        //default tasktable goes to the last position
        $pos = $project->taskTables()->count() + 1;

        $taskTable = new TaskTable();
        $taskTable->name = $request->name;
        $taskTable->project = $project->id;
        $taskTable->position = $pos;

        $taskTable->save();

        session()->flash('message', [
            'type' => 'success',
            'text' => 'Task Table created successfully.',
        ]);
        return response()->json(['success' => true, 'message' => 'Task Table created successfully.']);
    }

    /**
     * Delete Task Table
     */
    public function destroy( $id)
    {
        
        $taskTable = TaskTable::findOrFail($id);
        $projectId = $taskTable->project;
        $project = Project::find($projectId);


        //check if the user is authorized to delete the task table
        try {
            $this->authorize('createTaskTable', $project);
            
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }

        if ($taskTable->tasks()->exists()) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Cannot delete a task table with associated tasks.',
            ]);
            return response()->json(['error' => 'Cannot delete a task table with associated tasks.'], 400);
        }
    
        // trasaction to delete the task table and update the positions
        DB::transaction(function () use ($taskTable, $projectId) {
            $taskTable->delete();
    
            //Update the positions of the remaining task tables
            TaskTable::where('project', $projectId)
                ->orderBy('position')
                ->get()
                ->each(function ($taskTable, $index) {
                    $taskTable->update(['position' => $index]);
                });
        });
        session()->flash('message', [
            'type' => 'success',
            'text' => 'Task Table deleted successfully.',
        ]);
        return response()->json(['message' => 'Task table deleted successfully.'], 200);

    }

}