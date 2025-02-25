<?php

namespace App\Http\Controllers;

use App\Events\InvitedToProject;
use App\Events\NotificationEvent;
use App\Models\Account;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectInviteMail;

class InvitationController extends Controller
{
    /**
     * Accept an invitation.
     */
    public function accept(Invitation $invitation)
    {
        //check user authorization
        try {
            $this->authorize('accept', $invitation);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back()->with('error', 'Unauthorized action.');
        }
        if ($invitation->accepted) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Invitation has already been accepted.',
            ]);
            return redirect()->route('projects')->with('error', 'Invitation has already been accepted.');
        }
        $invitation->update(['accepted' => true]);

        //create and send notifications
        $notification = DB::table('notification')
            ->where('project', $invitation->project)
            ->where('type', 'Accepted_Invite')
            ->latest('id')
            ->first();
        $project = $invitation->getProject()->first();
        if ($project) {
            $members = $project->members;
            foreach ($members as $member) {
                if($member->id != Auth::user()->id) {
                    broadcast(new NotificationEvent($notification->id, $member->id));
                }
            }
        }

        $user = Account::find($invitation->account);
        Auth::login($user);

        session()->flash('message', [
            'type' => 'success',
            'text' => 'Invitation accepted.',
        ]);
        return redirect()->route('project.show', ['project' => $invitation->project])
        ->with('success', 'Invitation accepted. Welcome to the project!');
    }


    /**
     * Decline an invitation.
     */
    public function decline(Invitation $invitation)
    {
        try {
            $this->authorize('decline', $invitation);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }
        // Delete the invitation
        $invitation->delete();

        session()->flash('message', [
            'type' => 'success',
            'text' => 'Invitation declined.',
        ]);
        return redirect()->back()->with('success', 'Invitation declined.');
    }

    /**
    * Send an invitation.
    */
    public function invite(Request $request, Project $project)
    {
        //authorize action
        try {
            $this->authorize('sendInvite', $project);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return response()->json([
                'errors' => [
                    'username-email' => ['Unauthorized action.'],
                ]
            ], 422);
        }

        $request->validate([
            'username-email' => 'required|string',
        ]);

        $input = $request->input('username-email');
        $project_members = $project->members()->pluck('id')->toArray();

        //Get account from email or username
        $user = null;
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            // Input is an email
            $user = Account::where('email', $input)->first();
        } else {
            // Input is a username
            $user = Account::where('username', $input)->first();
        }
        if (!$user) {
            return response()->json([
                'errors' => [
                    'username-email' => ['No user found.'],
                ]
            ], 422);
        }

        //The user "unknown" cannot be invited or added to a project
        if ($user->email === 'unknown@example.com') {
            return response()->json([
                'errors' => [
                    'username-email' => ['No user found.'],
                ]
            ], 422);
        }

        if (in_array($user->id, $project_members)) {
            return response()->json([
                'errors' => [
                    'username-email' => ['This user is already a member.'],
                ]
            ], 422);
        }

        $existingInvitation = Invitation::where('project', $project->id)
            ->where('account', $user->id)
            ->first();

        if ($existingInvitation) {
            return response()->json([
                'errors' => [
                    'username-email' => ['User has already been invited.'],
                ]
            ], 422);
        }

        $newInvite = New Invitation();
        $newInvite -> project = $project->id;
        $newInvite -> account = $user-> id;
        $newInvite ->accepted = False;

        $newInvite -> save();
        // Dispatch the event after saving the invitation
        event(new InvitedToProject($newInvite->id));
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            Mail::to($user->email)->send(new ProjectInviteMail($newInvite));
        }
        return response()->json(['success' => true, 'message' => 'Invitation sent successfully.']);
    }
}
