<?php

namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use App\Models\Account;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\Task;
use App\Models\City;
use App\Models\Country;
use App\Models\AccountImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;


class AccountController extends Controller
{
    /**
     * Show the account page with all related data
     */
    public function showAccountPage()
    {
        if(Auth::check()){
            $account = Auth::user();
            $invitations = $account->invitations()->where('accepted', false)->paginate(5, ['*'], "invitations");
            $notifications = $account->notifications()
                ->where('checked', false)
                ->orderBy('create_date', 'desc')
                ->paginate(5, ['*'], "notifications");
            $city = $account->city ? City::find($account->city) : null;
            $country = $city ? Country::find($city->country) : null;
            $accountImage = $account->accountImage ?? null;
            return view('pages.accountpage', compact('account', 'accountImage', 'city', 'country', 'invitations', 'notifications'));
        }

        session()->flash('message', [
            'type' => 'error',
            'text' => 'Not logged-in.',
        ]);
        return view('pages.mainPage');
    }
    /**
     * Show the page to edit an account
     */
    public function showEditAccountPage()
    {
        if (Auth::check()) {
            $countries = Country::orderBy('name', 'asc')->get();

            $account = Auth::user();
            $account_city = $account->city;
            $account_country = null;
            try {
                if ($account_city !== null) {
                    $account_country = City::findOrFail($account_city)->country;
                }
            } catch (\Exception $e) {
                session()->flash('message', [
                    'type' => 'error',
                    'text' => "The Account's country could not be found.",
                ]);
                return redirect()->back();
            }
            $cities = null;
            if ($account_country !== null) {
                $cities = City::where('country', $account_country)
                    ->orderBy('name', 'asc')
                    ->get();
            }
            $isAdminViewingFromAdmin = false;
            return view('pages.accountEdit', compact('account', 'countries', 'cities', 'isAdminViewingFromAdmin', "account_city", "account_country"));
        }

        session()->flash('message', [
            'type' => 'error',
            'text' => 'Not logged-in.',
        ]);
        return view('pages.mainPage');
    }

    /**
     * Show the page to edit an account from the admin view
     */
    public function manageAccount($id)
    {
        try {
            $account = Account::findOrFail($id);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested user could not be found.',
            ]);
            return redirect()->back();
        }

        if($id == 0){
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Access denied',
            ]);
            return redirect()->route('admin.accounts');
        }

        $countries = Country::all();
        $isAdminViewingFromAdmin = true;

        if (Auth::user()->admin) {

            $account_city = $account->city;
            $account_country = null;

            /**
             * retrieve country from city
             */
            try {
                if ($account_city !== null) {
                    $account_country = City::findOrFail($account_city)->country;
                }
            } catch (\Exception $e) {
                session()->flash('message', [
                    'type' => 'error',
                    'text' => "The Account's country could not be found.",
                ]);
                return redirect()->back();
            }
            $cities = null;
            if ($account_country !== null) {
                $cities = City::where('country', $account_country)->get();
            }
            return view('pages.accountEdit', compact('account', 'countries', 'cities', 'account_city', 'account_country','isAdminViewingFromAdmin'));
        }
        session()->flash('message', [
            'type' => 'error',
            'text' => 'Unauthorized action.',
        ]);
        return redirect()->back();
    }


    /**
     *  Update the account information
     */
    public function update(Request $request)
    {
        /**
         * Check if the authenticated user is an admin
         */
        if(Auth::check()){
            if (Auth::user()->admin) {
                $userId = $request->input('user_id');
                try {
                    $account = Account::findOrFail($userId);
                } catch (\Exception $e) {
                    session()->flash('message', [
                        'type' => 'error',
                        'text' => 'The requested user could not be found.',
                    ]);
                    return redirect()->back();
                }
            }
            else{
                $account = Auth::user();
            }

            /**
             * Validate input
             */
            $request->validate([
                'username' => [
                    'required',
                    'string',
                    'min:8',
                    'max:250',
                    Rule::unique('account', 'username')->ignore($account->id),
                ],
                'name' => 'required|string|max:250',
                'email' => [
                    'required',
                    'email',
                    'max:250',
                    Rule::unique('account', 'email')->ignore($account->id),
                ],
                'workfield' => 'nullable|string|max:250',
                'city' => 'nullable|integer|exists:city,id',
                'description' => 'nullable|string|max:255',
                'account_image' => 'nullable|mimes:png'
            ]);

            /**
             * Update account image
             */
            // Check if the request has an image file
            if ($request->hasFile('account_image')) {
                if ($account->accountImage) {
                    $oldImagePath = $account->accountImage->image;
                    if (file_exists(public_path($oldImagePath))) {
                        unlink(public_path($oldImagePath));
                    }
                    $accountImage = $account->accountImage;
                } else {
                    $accountImage = new AccountImage();
                }

                FileController::upload($request->file('account_image'), 'accountAsset', $account->id);

                // Retrieve uploaded file name from the storage
                $fileName = $request->file('account_image')->hashName();

                // Update image path in the database
                $accountImage->image =  $fileName;
                $accountImage->save();

                // Update the account's reference to the image
                $account->account_image_id = $accountImage->id;
                $account->save();
            }


            $account->update([
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'workfield' => $request->workfield,
                'city' => $request->city,
                'description' => $request->description,
            ]);

            if(Auth::user()->admin && (Auth::user()->id != $userId)){
                session()->flash('message', [
                    'type' => 'success',
                    'text' => 'Profile edited successfully.',
                ]);
                return redirect()->route('admin.accounts');
            }
            else{
                session()->flash('message', [
                    'type' => 'success',
                    'text' => 'Profile edited successfully.',
                ]);
                return redirect()->route('account.show')->with('success', 'Account updated successfully!');
            }
        }

        session()->flash('message', [
            'type' => 'error',
            'text' => 'Not logged-in.',
        ]);
        return view('pages.mainPage');
    }

    /**
     * Show the forgot password with all related data
     */
    public function showForgotPasswordForm(Request $request)
    {
        $email = $request->email;
        return ($email)? view('auth.forgotPassword', compact('email')): view('auth.forgotPassword');
    }

    /**
     * Show the reset password form
     */
    public function showResetPasswordForm(string $token)
    {
        return view('auth.resetPassword', ['token' => $token]);
    }

    /**
     * Send the recover password email
     */
    public function sendRecoverPasswordMail(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username-email' => ['required', 'string'],
        ]);

        $loginField = filter_var($credentials['username-email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = \App\Models\Account::where($loginField, $credentials['username-email'])->first();

        if ($user) {
            if ($user->blocked) {
                return back()->withErrors([
                    'username-email' => 'Your account has been blocked.',
                ])->onlyInput('username-email');
            }
            try {
                $status = Password::sendResetLink(
                    ['email' => $user->email]
                );
            } catch (\Exception $e) {
                session()->flash('message', [
                    'type' => 'error',
                    'text' => 'There was an issue sending the email. Please try again later.',
                ]);
                return redirect()->back();
            }
            if ($status === Password::RESET_LINK_SENT) {
                session()->flash('message', [
                    'type' => 'success',
                    'text' => 'Reset password email sent successfully.',
                ]);
                if (Auth::check()) return redirect()->route('account.show')->with('success', 'Reset password email sent successfully.');
                else return redirect()->route('login')->with('success', 'Reset password email sent successfully.');
            } else {
                session()->flash('message', [
                    'type' => 'error',
                    'text' => __($status),
                ]);
                return back()->withErrors(['username-email' => [__($status)]]);
            }
        }

        session()->flash('message', [
            'type' => 'success',
            'text' => 'Reset password email sent successfully.',
        ]);
        if (Auth::check()) return redirect()->route('account.show')->with('success', 'Reset password email sent successfully.');
        else return redirect()->route('login')->with('success', 'Reset password email sent successfully.');
    }

    /**
     * Reset the password from the recover password email
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Account $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('message', [
                'type' => 'success',
                'text' => 'Account password updated successfully.',
            ]);
            if (Auth::check()) return redirect()->route('account.show')->with('success', 'Account password updated successfully!');
            else return redirect()->route('login')->with('success', 'Account password updated successfully!');
        }
        else {
            session()->flash('message', [
                'type' => 'error',
                'text' => __($status),
            ]);
            return back()->withErrors(['email' => [__($status)]]);
        }
    }

    /**
     * Delete user account
     */
    public function delete(Request $request, $id)
    {
        $user = Account::findOrFail($id);

        if($user!=Auth::user()){
        try {
                $this->authorize('isAdmin', auth::user());
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }}

        if ($user->admin) {
            return redirect()->back()->with('error', 'Cannot delete an administrator.');
        }

        if ($user->email === 'unknown@example.com') {
            return redirect()->back()->with('error', 'Cannot delete the Unknown user.');
        }
        $unknownUser = Account::where('email', 'unknown@example.com')->first();

        if (!$unknownUser) {
            throw new \Exception('Unknown user not found. Please create it first.');
        }

        DB::transaction(function () use ($user, $unknownUser, $request) {
            // Desabilitar triggers
            DB::statement('ALTER TABLE project_member DISABLE TRIGGER delete_project_member;');
            DB::statement('ALTER TABLE account_task DISABLE TRIGGER check_account_membership_in_project;');
            DB::statement('ALTER TABLE account_task DISABLE TRIGGER notify_task_assigned;');
            DB::statement('ALTER TABLE account_task DISABLE TRIGGER check_account_assigned_once;');

            $projects = Project::where('project_coordinator_id', $user->id)->get();

            foreach ($projects as $project) {
                $newCoordinator = Account::where('id', '!=', $user->id)->first();

                if ($newCoordinator) {
                    $this->changeCoordinator($request, $project->id, $newCoordinator->id);
                } else {
                    session()->flash('message', [
                        'type' => 'error',
                        'text' => 'No available coordinator to replace.',
                    ]);
                    return redirect()->back()->with('error', 'No available coordinator to replace');
                }
            }

            $projectsAsMember = $user->projects()->get();
            foreach ($projectsAsMember as $project) {
                $project->members()->syncWithoutDetaching([$unknownUser->id => ['is_favourite' => false]]);
                $project->members()->detach($user->id);
            }

            $user->tasks()->each(function ($task) use ($unknownUser) {
                if (!$task->accounts->contains($unknownUser->id)) {
                    $task->accounts()->syncWithoutDetaching([$unknownUser->id => ['account' => $unknownUser->id]]);
                }

            });

            ProjectEvent::where('account', $user->id)->update(['account' => $unknownUser->id]);

            $user->comments()->update(['account' => $unknownUser->id]);

            $user->forumMessages()->update(['account' => $unknownUser->id]);

            DB::statement('ALTER TABLE account_task ENABLE TRIGGER notify_task_assigned;');
            DB::statement('ALTER TABLE project_member ENABLE TRIGGER delete_project_member;');
            DB::statement('ALTER TABLE account_task ENABLE TRIGGER check_account_membership_in_project;');
        });

        try {
            $user->delete();
            session()->flash('message', [
                'type' => 'success',
                'text' => 'User deleted successfully.',
            ]);
            return redirect()->back()->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Error deleting the user.',
            ]);
            return redirect()->back()->with('error', 'Error deleting the user');
        }

        return redirect()->route('admin.users')->with('success', 'User deleted and tasks reassigned.');
    }

    /**
     * Change the coordinator of a project
     */
    public function changeCoordinator(Request $request, $projectId, $newCoordinatorId)
    {
        try {
            $project = Project::findOrFail($projectId);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested project could not be found.',
            ]);
            return redirect()->back()->with('error', 'The requested project could not be found');
        }
        try {
            $newCoordinator = Account::findOrFail($newCoordinatorId);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested account could not be found.',
            ]);
            return redirect()->back()->with('error', 'The requested account could not be found');
        }
        try {
            $this->authorize('changeCoordinator', $project);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back()->with('error', 'Failed changing project coordinator');
        }
        $project->update(['project_coordinator_id' => $newCoordinator->id]);
        session()->flash('message', [
            'type' => 'success',
            'text' => 'Project coordinator changed successfully.',
        ]);
        return redirect()->back()->with('success', 'Project coordinator changed successfully');
    }

    /**
     * Show the Task Hub page with all related data
     */
    public function showTaskHubPage(Request $request)
    {
        if (Auth::check()) {
            $account = Auth::user();

            // Get all assigned tasks where finish_date is null
            $assignedTasksAll = $account->tasks()
                ->whereNull('finish_date')
                ->whereHas('taskTable', function ($query) {
                    $query->where('position', '!=', 0);
                })
                ->get();

            // Compute summary data based on all tasks (not just the current page)
            $projects = $assignedTasksAll->map(function($task) {
                return $task->getProject();
            })->unique('id');  // Get distinct projects

            $projectCount = $projects->count();  // Count distinct projects

            $startDates = $assignedTasksAll->pluck('start_date')->map(fn($date) => \Carbon\Carbon::parse($date));
            $deadlineDates = $assignedTasksAll->pluck('deadline_date')->filter()->map(fn($date) => \Carbon\Carbon::parse($date));
            $priorities = $assignedTasksAll->pluck('priority')->map(function ($priority) {
                return $priority->value;
            })->toArray();
            $priorityCounts = array_merge(
                ['High' => 0, 'Medium' => 0, 'Low' => 0],
                array_count_values($priorities)
            );
            $startDateRange = $startDates->isNotEmpty() ? $startDates->min()->format('d M Y') . ' - ' . $startDates->max()->format('d M Y') : 'N/A';
            $deadlineRange = $deadlineDates->isNotEmpty() ? $deadlineDates->min()->format('d M Y') . ' - ' . $deadlineDates->max()->format('d M Y') : 'N/A';

            $assignedTasksPaginated = $account->tasks()
                ->whereNull('finish_date')
                ->whereHas('taskTable', function ($query) {
                    $query->where('position', '!=', 0);
                })
                ->orderBy('start_date', 'desc') // Order by start_date descending
                ->paginate(10);


            return view('pages.taskHub', compact('assignedTasksPaginated', 'projectCount', 'startDateRange', 'deadlineRange', 'priorityCounts'));
        }

        session()->flash('message', [
            'type' => 'error',
            'text' => 'Unauthorized action.',
        ]);
        return redirect()->back();
    }

    /**
     * Search for users by name, email or username
     */
    public function searchUsers(Request $request)
    {
        $query = $request->input('query');

        $users = Account::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->orWhere('username', 'LIKE', "%{$query}%")
                    ->take(10)
                    ->get();

        return response()->json($users);
    }

}
