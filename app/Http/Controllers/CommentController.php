<?php

namespace App\Http\Controllers;

use App\Events\PostedTaskComment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested task could not be found.',
            ]);
            return response()->json(['error' => 'The requested task could not be found.'], 404);
        }

        try {
            $this->authorize('postComment', $task);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back()->with('error', 'Unauthorized action.');
        }
        $request->validate([
            'messageContent' => 'required|string|max:255'
        ]);
        $comment = Comment::create([
            'account' => Auth::user()->id,
            'content' => $request->messageContent,
            'create_date' => now(),
            'task' => $task->id,
        ]);
        // Trigger the PostedTaskComment event
        event(new PostedTaskComment($comment->id));
        return response()->json([
            'success' => 'Comment posted successfully',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'create_date' => $comment->create_date,
                'image_path' => Auth::user()->getAccountImage(),
                'get_account' => [
                    'name' => Auth::user()->name,
                ],
            ],
        ], 200);
    }
}
