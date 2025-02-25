<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\Account;
use App\Models\TaskTable;
use App\Models\ProjectEvent;
use App\Models\AccountTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Show the Task Panel
     */
    public function show($id)
    {
        try {
            $task = Task::with(['accounts.accountImage'])->findOrFail($id);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Unable to find task'], 404);
            }
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested task could not be found.',
            ]);
            return redirect()->back();
        }
        //check action authorization
        try {
            $this->authorize('view', $task);
        }
        catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Unauthorized action'], 403);
            }
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }

        //Handle the case when the account doesn't have an image
        $task->accounts->each(function ($account) {
            $account->image_path = $account->getAccountImage();
        });
        $projectMembers = $task->getProject()->members()->get();
        $projectMembers->each(function ($member) {
            $member->image_path = $member->getAccountImage();
        });
        $comments = $task->comments()->with('getAccount')->get();
        $comments->each(function ($comment) {
            $comment->image_path = $comment->getAccount->getAccountImage();
        });
        $projectEvents = $task->projectEvents()
            ->orderBy('time', 'desc') // Sort by 'time' in ascending order
            ->get()
            ->map(function($projectEvent) {
                return [
                    'account_in_description' => $projectEvent->accountInDescription(),
                    'description' => $projectEvent->description(),
                    'time' => $projectEvent->time,
                    'type' => $projectEvent->type(),
                    'route' => $projectEvent->accountRoute(),
                ];
            });
        return response()->json([
            'task' => $task,
            'project_members' => $projectMembers,
            'comments' => $comments,
            'project_events' => $projectEvents,
            'project_id' => $task->getProject()->id,
            'is_deleted' => $task->taskTable->position === 0,
        ]);
    }

    /**
     * Update the task in the database.
     */
    public function update(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested task could not be found.',
            ]);
            return redirect()->back();
        }
        // check action authorization
        try {
            $this->authorize('update', $task);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }
        //validate input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'deadline_date' => 'nullable|date',
            'finish_date' => 'nullable|date',
            'priority' => 'required|in:High,Medium,Low',
        ]);
        //check if the deadline date can be updated by user and if it is valid
        if ($request->has('deadline_date') && $request->deadline_date) {
            if (!$this->authorize('addDeadline', $task)) {
                return response()->json([
                    'errors' => [
                        'dealivery_date' => ['Only the project coordinator can update the deadline.']
                    ]
                ], 403);
            }
            $deadlineDate = strtotime($request->deadline_date);
            $startDate = strtotime($task->start_date);
            $formattedStartDate = date('Y-m-d', $startDate);
            if ($deadlineDate < $startDate) {
                return response()->json([
                    'errors' => [
                        'deadline_date' => ['The deadline date must be after or equal to the start date: ' . $formattedStartDate,],
                    ]
                ], 422);
            }
        }
        //transaction to update the task
        try {
            DB::transaction(function () use ($validatedData, $task) {
                // Detect priority change
                if ($task->priority->value != $validatedData['priority']) {
                    ProjectEvent::create([
                        'account' => Auth::user()->id,
                        'task' => $task->id,
                        'date' => now(),
                        'event_type' => 'Task_Priority_Changed',
                    ]);
                }
                // Update the task
                $task->update($validatedData);

            });

            session()->flash('message', [
                'type' => 'success',
                'text' => 'Task updated successfully.',
            ]);

            return response()->json(['success' => true, 'message' => 'Task updated successfully.']);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Failed to update task.',
            ]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    /**
     * Delete the task (deactivate it).
     */
    public function delete($id)
    {
        try {
            $task = Task::findOrFail($id);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested task could not be found.',
            ]);
            return redirect()->back();
        }
        //check action authorization
        try {
            $this->authorize('delete', $task);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }
        //transaction ensures task deletion and the position of the other tasks is updated
        DB::transaction(function () use ($task) {
            $oldTaskTableId = $task->task_table;
            $oldPosition = $task->position;
            $deletedTasksTable = TaskTable::where('project', $task->taskTable->project)
                ->where('name', 'Deleted tasks')
                ->firstOrFail();

            $task->update(['position' => $deletedTasksTable->tasks()->count() + 1,'task_table' => $deletedTasksTable->id]);

            $tasksOld = DB::table('task')
                ->where('task_table', $oldTaskTableId)
                ->where('position', '>', $oldPosition)
                ->orderBy('position')
                ->get();

            foreach ($tasksOld as $taskUpdate) {
                $tempTask = Task::findOrFail($taskUpdate->id);
                $tempTask->update(['position' => $taskUpdate->position - 1]);
            }

            ProjectEvent::create([
                'account' => auth()->id(),
                'task' => $task->id,
                'date' => now(),
                'event_type' => 'Task_Deactivated',
            ]);
        });
        try {
            $task = Task::findOrFail($id);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested task could not be found.',
            ]);
            return redirect()->back();
        }

        session()->flash('message', [
            'type' => 'success',
            'text' => 'Task deactivated successfully.',
        ]);
        return redirect()->route('project.show', $task->taskTable->project)->with('success', 'Task deactivated successfully.');
    }

    /**
     * Mark a task as completed.
     */
    public function completed($id)
    {
        try {
            $task = Task::findOrFail($id);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested task could not be found.',
            ]);
            return redirect()->back();
        }

        try {
            $this->authorize('completed', $task);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }
        //transaction ensures task completion and the creation of the corresponding project event and notifications
        DB::transaction(function () use($id) {

            $user = Auth::user();
            $project_event = new ProjectEvent();
            $project_event->account = $user->id;
            $project_event->task = $id;
            $project_event->event_type = 'Task_Completed';
            $project_event->save();

            $accountTask = AccountTask::where('task', $id)->get();
            foreach ($accountTask as $account){
                $notification =new Notification();
                $notification->type= 'Task_Completed';
                $notification->emitted_to= $account->account;
                $notification->project_event= $project_event->id;
                $notification->save();
            }
        });
        $task->update(['finish_date' => now()]);
        // Brodacast notifications
        $notification = DB::table('notification')
            ->where('type', 'Task_Completed')
            ->latest('id')
            ->first();
        $assignees = $task->accounts()->get();
        if ($assignees) {
            foreach ($assignees as $assignee) {
                broadcast(new NotificationEvent($notification->id, $assignee->id));
            }
        }
        session()->flash('message', [
            'type' => 'success',
            'text' => 'Task completed successfully.',
        ]);
        return redirect()->route('project.show', $task->taskTable->project)->with('success', 'Task completed successfully.');
    }

    /**
     * Mark a task as uncompleted.
     */
    public function uncompleted($id)
    {
        try {
            $task = Task::findOrFail($id);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested task could not be found.',
            ]);
            return redirect()->back();
        }
        try {
            $this->authorize('uncompleted', $task);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }
        $task->update(['finish_date' => null]);

        session()->flash('message', [
            'type' => 'success',
            'text' => 'Task changed to uncompleted successfully.',
        ]);
        return redirect()->route('project.show', $task->taskTable->project)->with('success', 'Task changed to uncompleted successfully.');
    }

    /**
     * Stores new task
     */
    public function store(Request $request, TaskTable $taskTable)
    {
        //check action authorization
        try {
            $this->authorize('createTask', $taskTable->getProject);
        }
        catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Unauthorized action'], 401);
            }
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }
        //validate input
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'deadline_date'=> 'nullable|date|after_or_equal:today',
            'priority' => 'required|in:High,Medium,Low',
        ]);
        //transaction ensures task creation and the creation of the corresponding project event
        try {
            DB::transaction(function () use ($validatedData, $taskTable) {
                // Determine the new position within a serialized transaction
                $pos = $taskTable->tasks()->max('position') + 1;
                $task = new Task();
                $task->fill($validatedData);
                $task->task_table = $taskTable->id;
                $task->position = $pos;
                $task->save();
                // Create a corresponding project event
                ProjectEvent::create([
                    'account' => Auth::user()->id,
                    'task' => $task->id,
                    'date' => now(),
                    'event_type' => 'Task_Created',
                ]);
            });
            session()->flash('message', [
                'type' => 'success',
                'text' => 'Task created successfully.',
            ]);
            return response()->json(['success' => true, 'message' => 'Task created successfully.']);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Failed to create task'], 500);
            }
            // Rollback is automatic if an exception is thrown within the transaction
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Failed to create task.',
            ]);
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Unassign user from task
     */
    public function removeAccount($taskId, Account $user)
    {
        try {
            $task = Task::findOrFail($taskId);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested task could not be found.',
            ]);
            return redirect()->back();
        }
        try {
            $this->authorize('changeAccountAssignment', $task);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }
        $task->accounts()->detach($user->id);
        // Create a corresponding project event
        ProjectEvent::create([
            'account' => $user->id,
            'task' => $task->id,
            'date' => now(),
            'event_type' => 'Task_Unassigned',
        ]);
        return response()->json(['success' => true, 'message' => 'Account removed from the task successfully.']);
    }

    /**
     * Change the position of a task
     */
    public function changeTaskPosition($taskId, $posDest, $tableDest)
    {
        try {
            $newPosition = $posDest;
            $newTaskTableName = $tableDest;
            //transaction ensures task position update and the position of the other tasks is updated
            DB::transaction(function () use ($taskId, $newPosition, $newTaskTableName) {
                // Retrieve the old task table and position
                try {
                    $task = Task::findOrFail($taskId);
                } catch (\Exception $e) {
                    session()->flash('message', [
                        'type' => 'error',
                        'text' => 'The requested task could not be found.',
                    ]);
                    return response()->json(['error' => 'Failed to update task position.'], 500);
                }
                //check action authorization
                try {
                    $this->authorize('changePosition', $task);
                }
                catch (\Exception $e) {
                    session()->flash('message', [
                        'type' => 'error',
                        'text' => 'Unauthorized action.',
                    ]);
                    return response()->json(['error' => 'Failed to update task position.'], 500);
                }
                $oldTaskTableId = $task->task_table;
                $oldPosition = $task->position;
                // Retrieve the new task table
                try {
                    $taskTable = TaskTable::findOrFail($task->task_table);
                } catch (\Exception $e) {
                    session()->flash('message', [
                        'type' => 'error',
                        'text' => 'The requested task table could not be found.',
                    ]);
                    return response()->json(['error' => 'Failed to update task position.'], 500);
                }
                try {
                    $newTaskTableId = TaskTable::where('name', $newTaskTableName)
                        ->where('project', $taskTable->project)
                        ->first()->id;
                } catch (\Exception $e) {
                    session()->flash('message', [
                        'type' => 'error',
                        'text' => 'The requested task table could not be found.',
                    ]);
                    return response()->json(['error' => 'Failed to update task position.'], 500);
                }
                // Check if the task is moved to the same table
                if ($oldTaskTableId === $newTaskTableId) {
                    if ($oldPosition < $newPosition) $newPosition = $newPosition - 1;
                    if ($oldPosition === $newPosition) return;

                    $tempPosition = DB::table('task')
                        ->where('task_table', $newTaskTableId)
                        ->select(DB::raw('COALESCE(MAX(position), 0) + 1 as next_position'))
                        ->value('next_position');
                    $task->update(['position' => $tempPosition]);

                    $tasksNew = DB::table('task')
                        ->where('task_table', $newTaskTableId)
                        ->where('position', '>=', $newPosition)
                        ->where('position', '<', $oldPosition)
                        ->orderByDesc('position')
                        ->get();

                    foreach ($tasksNew as $taskUpdate) {
                        $tempTask = Task::findOrFail($taskUpdate->id);
                        $tempTask->update(['position' => $taskUpdate->position + 1]);
                    }

                    $tasksOld = DB::table('task')
                        ->where('task_table', $oldTaskTableId)
                        ->where('position', '<=', $newPosition)
                        ->where('position', '>', $oldPosition)
                        ->orderBy('position')
                        ->get();

                    foreach ($tasksOld as $taskUpdate) {
                        $tempTask = Task::findOrFail($taskUpdate->id);
                        $tempTask->update(['position' => $taskUpdate->position - 1]);
                    }

                    $task->update(['position' => $newPosition]);
                }
                else {
                    //Handle moving to another table
                    $tasksNew = DB::table('task')
                        ->where('task_table', $newTaskTableId)
                        ->where('position', '>=', $newPosition)
                        ->orderByDesc('position')
                        ->get();

                    foreach ($tasksNew as $taskUpdate) {
                        $tempTask = Task::findOrFail($taskUpdate->id);
                        $tempTask->update(['position' => $taskUpdate->position + 1]);
                    }

                    $task->update(['position' => $newPosition, 'task_table' => $newTaskTableId]);

                    $tasksOld = DB::table('task')
                        ->where('task_table', $oldTaskTableId)
                        ->where('position', '>', $oldPosition)
                        ->orderBy('position')
                        ->get();

                    foreach ($tasksOld as $taskUpdate) {
                        $tempTask = Task::findOrFail($taskUpdate->id);
                        $tempTask->update(['position' => $taskUpdate->position - 1]);
                    }
                }
            });
        } catch (\Exception $e) {
            // Handle exceptions and rollback
            report($e);
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Failed to update task position.',
            ]);
            return response()->json(['error' => 'Failed to update task position.'], 500);
        }
        return response()->json(['success' => 'Task position updated successfully.']);
    }

    /**
     * Assign user to task
     */
    public function assignUser(Request $request,$taskId){

        try {
            $task = Task::findOrFail($taskId);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested task could not be found.',
            ]);
            return redirect()->back();
        }
        //check action authorization
        try {
            $this->authorize('assign', $task);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }
        try {
            $user = Account::findOrFail($request->userId);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested account could not be found.',
            ]);
            return redirect()->back();
        }

        if ($user->email === 'unknown@example.com') {
            return response()->json([
                'error' => [
                    'text' => ['The user "unknown" cannot be assigned to a task.'],
                ]
            ], 422);
        }
        
        $taskTable = $task->taskTable;
        $project=$taskTable->getProject;
        $project_members= $project->members()->pluck('id')->toArray();

        if (!in_array($user->id, $project_members)) {
            return response()->json([
                'error' => [
                    'text' => ['This user is not a project member.'],
                ]
            ], 422);
        }

        $accountTask = AccountTask::where('account', $user->id)->where( 'task', $task->id)->first();
        if($accountTask){
            return response()->json([
                'error' => [
                    'text' => ['This user is already assigned.'],
                ]
            ], 422);
        }
        // create a new account_task record
        DB::table('account_task')->insert([
            'account' => $user->id,
            'task' => $task->id,
        ]);
        //broadcast notification
        $notification = DB::table('notification')
            ->where('type', 'Assigned_Task')
            ->latest('id')
            ->first();
        broadcast(new NotificationEvent($notification->id));
        session()->flash('message', [
            'type' => 'success',
            'text' => 'User assigned to task successfully.',
        ]);
        // Return user details for frontend updates
        return response()->json([
            'success' => true,
            'message' => 'Account assigned to the task successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'image_path' => $user->getAccountImage(),
            ]
        ]);

    }
}
