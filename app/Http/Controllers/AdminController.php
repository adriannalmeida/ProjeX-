<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\Account;
use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;


class AdminController extends Controller
{
    /**
     * Display the admin page with user accounts.
     */
    public function listAccounts(Request $request)
    {
        try {
            $this->authorize('isAdmin', auth::user());
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }

        $search = $request->input('search', '');
        $filter = $request->input('filter', null);
        $query = Account::where('id', '!=', 0)->where('name', '!=', 'Unknown');

        // Apply search filter if there's a search term
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', '%' . $search . '%')
                  ->orWhere('email', 'ILIKE', '%' . $search . '%')
                  ->orWhere('username', 'ILIKE', '%' . $search . '%');
            });
        }
        
        // Apply filter for blocked users
        if ($filter === 'Blocked') {
            $query->where('blocked', true);
        } elseif ($filter === 'Not Blocked') {
            $query->where('blocked', false);
        }
        // Get the users based on the filter and search criteria
        $users = $query->with('accountImage')
        ->orderBy('id')
        ->paginate(10);
        return view('pages.admin', compact('users', 'search', 'filter'));
    }

    /**
     * Display the admin page with user projects.
     */
    public function listProjects(Request $request)
    {
        try {
            $this->authorize('isAdmin', auth::user());
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }

        $search = $request->input('search', '');
        $filter = $request->input('filter', null);
        if (empty($search)) {
            $projects = Project::paginate(10, ['*'], 'projects'); // Default query when no search
        } else {
            $projects = Project::selectRaw(
                "*, ts_rank(tsvectors, to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')) as rank",
                [$search]
            )->whereRaw(
                "tsvectors @@ to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')",
                [$search]
            )->orderByDesc('rank')
                ->paginate(10, ['*'], 'projects'); // Paginated search results
        }
        return view('pages.admin', compact( 'projects', 'search', 'filter'));
    }

    /**
     * Search Users
     */

    public function searchAjax(Request $request)
    {
        if (!Auth::check()) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Not logged-in.',
            ]);
            return redirect('/login');
        }

        $search = $request->input('search', '');
        $filter = $request->input('filter', null);

        $query = Account::where('id', '!=', 0);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', '%' . $search . '%')
                    ->orWhere('email', 'ILIKE', '%' . $search . '%')
                    ->orWhere('username', 'ILIKE', '%' . $search . '%');
            });
        }

        if ($filter === 'Blocked') {
            $query->where('blocked', true);
        } elseif ($filter === 'Not Blocked') {
            $query->where('blocked', false);
        }

        $users = $query->orderBy('name', 'asc')
            ->limit(10)
            ->get();

        return response()->json(['users' => $users]);
    }


    /**
     * Search Projects
     */
    public function searchProjectsAjax(Request $request)
    {
        if (!Auth::check()) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Not logged-in.',
            ]);
            return redirect('/login');
        }
        else{
            $search = $request->input('search', '');
            $projects = Project::selectraw(
                "*, ts_rank(tsvectors, to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')) as rank",
                [$search]
            )->whereRaw(
                "tsvectors @@ to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')",
                [$search]
            )->orderByDesc('rank')
                ->limit(10)
                ->get();
            return response()->json(['projects' => $projects]);
        }
    }

    /**
     * Block or unblock a user.
     */
    public function blockAccount($id)
    {
        try {
            $user = Account::findOrFail($id);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested user could not be found.',
            ]);
            return redirect()->back();
        }

        try {
            $this->authorize('isAdmin', auth::user());
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }

        $user->blocked = !$user->blocked;
        $user->save();

        if ($user->blocked) {
            session()->flash('message', [
                'type' => 'success',
                'text' => 'User blocked successfully.',
            ]);
        }
        else {
            session()->flash('message', [
                'type' => 'success',
                'text' => 'User unblocked successfully.',
            ]);
        }

        $message = $user->blocked ? 'User blocked successfully.' : 'User unblocked successfully.';

        return redirect()->back()->with('success', $message);
    }
}
