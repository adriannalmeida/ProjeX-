<?php

namespace App\Http\Controllers;

use App\Events\PostedMessage;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\ForumMessage;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Events\DeletedMessage;

class ForumMessageController extends Controller
{
    /**
     * Display a listing of ForumMessages.
     */
    public function getMessages(Project $project)
    {
        try {
            $this->authorize('view', $project);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }
        $messages = $project->forumMessages()->get();
        $messagesWithAccount = $messages->map(function ($message) {
            return [
                'id' => $message->id,
                'content' => $message->content,
                'create_date' => $message->create_date,
                'account_name' => $message->getAccount->name,
            ];
        });
        return response()->json($messagesWithAccount);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $projectId)
    {
        try {
            $project = Project::findOrFail($projectId);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested project could not be found.',
            ]);
            return response()->json(['error' => 'The requested project could not be found.'], 404);
        }
        try {
            $this->authorize('postMessage', $project);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return response()->json(['error' => 'Unauthorized action'], 401);
        }
        $request->validate([
            'message' => 'required|string|max:255'
        ]);
        $message = ForumMessage::create([
            'account' => Auth::user()->id,
            'project' => $project->id,
            'content' => $request->message,
            'create_date' => now(),
        ]);

        // Trigger the PostedMessage event
        event(new PostedMessage($message->id));
        if (request()->expectsJson()) {
            return response()->json(['success' => "Message posted successfully."]);
        }
        session()->flash('message', [
            'type' => 'success',
            'text' => 'Message posted successfully.',
        ]);
        return redirect()->back();
    }



    /**
     * Update the ForumMessages resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $account = Auth::user();

        try {
            $message = ForumMessage::findOrFail($id);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested message could not be found.',
            ]);
            return response()->json(['error' => 'The requested message could not be found.'], 404);
        }

        if($account->id !== $message->account) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return response()->json(['error' => 'Unauthorized action'], 401);
        }

        $request->validate([
            'message' => 'required|string|max:255'
        ]);

        $message->update(['content' => $request->message]);

        if (!$request->expectsJson()) {
            session()->flash('message', [
                'type' => 'success',
                'text' => 'Message updated successfully.',
            ]);
            return redirect()->back();
        }
        return response()->json(['success' => 'Message edited successfully']);
    }

    /**
     * Remove the ForumMessages from storage.
     */
    public function destroy(string $id)
    {
        $account = Auth::user();

        try {
            $message = ForumMessage::findOrFail($id);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested message could not be found.',
            ]);
            return response()->json(['error' => 'The requested message could not be found.'], 404);
        }

        if($account->id !== $message->account) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return response()->json(['error' => 'Unauthorized action'], 401);
        }

        $message->delete();

        session()->flash('message', [
            'type' => 'success',
            'text' => 'Message deleted successfully.',
        ]);

        // Trigger the DeletedMessage event
        event(new DeletedMessage($id));

        return redirect()->back();
    }

}
