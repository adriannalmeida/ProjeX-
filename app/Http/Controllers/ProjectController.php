<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Models\City;
use App\Models\Country;
use App\Models\ForumMessage;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\TaskTable;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Account;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use Illuminate\Support\Facades\DB;



class ProjectController extends Controller
{
    /**
     * Auxiliary function according to the Active Tab and Search query
     */
    private function getProjectsByTabAndSearch($tab, $search)
    {
        $query = null;

        if ($tab === "tabFav") {
            $query = Auth::user()->projects()
                ->wherePivot('is_favourite', true);
        } elseif ($tab === "tabPub") {
            $query = Project::where('ispublic', true);
        } elseif ($tab === "tabArchived") {
            $query = Project::where('archived', true);
        } else {
            $query = Auth::user()->projects()->where('archived', false)->orderByDesc('pivot_last_accessed');
        }

        if ($search) {
            $query = $query->selectRaw(
                "*, ts_rank(tsvectors, to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')) as rank",
                [$search]
            )->whereRaw(
                "tsvectors @@ to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')",
                [$search]
            )->orderByDesc('rank');
        }

        return $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function list(Request $request)
    {
        $search = $request->input('search', '');
        $tab = $request->input('tab', 'tabMy');
// Get the 10 most recently accessed projects that are not archived
        $recentProjects = Auth::user()->projects()
            ->where('archived', false)
            ->orderByDesc('pivot_last_accessed')
            ->take(10)
            ->get();

        $query = $this->getProjectsByTabAndSearch($tab, $search);

        if ($search) {
            $projects = $query->paginate(10, ['*'], $tab ? "{$tab}_projects" : 'my_projects');
        } else {
            //Handle no search, with tabs specified
            if ($tab === "tabPub" || $tab === "tabArchived") {
                $projects = Project::with(['members' => function ($query) {
                    $query->where('account', Auth::id());
                }])
                    ->select('project.*', DB::raw('COALESCE(project_member.is_favourite, false) as is_favourite'))
                    ->leftJoin('project_member', function ($join) {
                        $join->on('project.id', '=', 'project_member.project')
                            ->where('project_member.account', Auth::id());
                    });

                if ($tab === "tabArchived") {
                    $projects = $projects->where('archived', true);
                } else {
                    $projects = $projects->where('ispublic', true);
                }
                $projects = $projects->orderBy('id')
                    ->paginate(10, ['*'], "{$tab}_projects");
            }else {
                $projects = $query->paginate(10, ['*'], $tab ? "{$tab}_projects" : 'my_projects');
            }
        }
        return view('pages.projects', compact(['projects', 'recentProjects', 'search', 'tab']));
    }
    /**
     * Dynamically get projects when search query changes in Projects Page
     */
    public function searchAjax(Request $request)
    {
        $search = $request->input('search', '');
        $tab = $request->input('tab', '');

        $query = $this->getProjectsByTabAndSearch($tab, $search);
        $projects = $query->get();

        return response()->json(compact(['projects', 'tab', 'search']));
    }
    /**
     * Dynamically get projects when tab changes in Projects Page
     */
    public function tabSearchAjax(Request $request)
    {
        $recentProjects = Auth::user()->projects()
            ->where('archived', false)
            ->orderByDesc('pivot_last_accessed')
            ->take(10)
            ->get();

        $search = $request->input('search', '');
        $tab = $request->input('tab', '');

        $query = $this->getProjectsByTabAndSearch($tab, $search);
        $projects = $query->paginate(10);

        // Set base URL and append all required query parameters
        $projects->withPath(route('projects')) // Base route for pagination
        ->appends([
            'tab' => $tab, // Keep the tab parameter
        ]);

        $projects->setPageName("{$tab}_projects");
        return response()->json([
            'projects' => $projects->items(),
            'recentProjects' => $recentProjects,
            'tab' => $tab,
            'pagination' => $projects->links()->render()
        ]);
    }


    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'isPublic' => 'required|boolean',
            'finishDate' => 'nullable|date|after_or_equal:today',
        ]);

        //transaction ensures project coordinator and deleted task table are created with the project
        DB::transaction(function () use ($request) {
            $project = new Project();
            $project->name = $request->name;
            $project->description = $request->description;
            $project->project_coordinator_id = Auth::user()->id;
            $project->ispublic = $request->isPublic;
            $project->save();

            $user = Auth::user();
            $user->projects()->attach($project->id, ['is_favourite' => false]);

            //$pos = 0;
            $deletedTasksTable = new TaskTable();
            $deletedTasksTable->name = 'Deleted tasks';
            $deletedTasksTable->project = $project->id;
            $deletedTasksTable->position = 0;
            $deletedTasksTable->save();
        });

        session()->flash('message', [
            'type' => 'success',
            'text' => 'Project created successfully.',
        ]);
        return response()->json(['success' => true, 'message' => 'Project created successfully.']);
    }

    /**
     * Display the specified project page.
     */
    public function show($id, Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search', '');
        $filter = $request->input('filter', '');
        $project = Project::with(['members', 'coordinator'])->find($id);
        //check action authorization
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

        //check if deleted tasks are visible and get TaskTables
        if($project->view_deleted_tasks_permission){
            $taskTables = $project->taskTables()->with(['tasks' => function ($query) use ($search, $filter) {
                if ($search) {
                    $query->whereRaw(
                        "tsvectors @@ to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')",
                        [$search]
                    );
                }
                if ($filter) {
                    $query->where('priority', $filter);
                }
                $query->with(['accounts.accountImage'])->orderBy('position');
            }])->orderBy('position')->get();
        }else{
            $taskTables = $project->taskTables()->with(['tasks' => function ($query) use ($search, $filter) {
                if ($search) {
                    $query->whereRaw(
                        "tsvectors @@ to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')",
                        [$search]
                    );
                }
                if ($filter) {
                    $query->where('priority', $filter);
                }
                $query->with(['accounts.accountImage'])->orderBy('position');
            }])->where('position', '>', 0)->orderBy('position')->get();
        }

        $isCoordinator = Auth::user()->id === $project->project_coordinator_id;
        //check if authenticated user is a member of the project
        try {
            $this->authorize('interact', $project);
            $isMember = true;
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'success',
                'text' => 'You can only view this project, not edit.',
            ]);
            $isMember = false;
        }
        $forumMessages = $project->forumMessages()->orderBy('create_date', 'asc')->get()->slice(-30);
        $projectMembers = $project->members()->limit(30)->get();

        // Pivot Layout Values (only for members)
        $left_top = $left_bottom = $right_top = $right_bottom = null;

        $member = $project->members->where('id', $user->id)->first();
        if($isMember && $member){
            foreach ($member->pivot->toArray() as $key => $value) {
                if (gettype($value) == 'string') {
                    switch ($value) {
                        case 'LeftUp':
                            $left_top = $key;
                            break;
                        case 'LeftDown':
                            $left_bottom = $key;
                            break;
                        case 'RightUp':
                            $right_top = $key;
                            break;
                        case 'RightDown':
                            $right_bottom = $key;
                            break;
                    }
                }
            }
        }

        $projectEvents = $project->projectEvents()->orderBy('time', 'desc')->limit(15)->get();
        $assignedTasks = $user->tasks()->get()->filter(function ($task) use ($project) {
            return is_null($task->finish_date) && $task->getProject()->id === $project->id;
        });
        $assignedTasks = $assignedTasks->take(15)->values();
        // Determine which partial to use based on the route
        $tableView = request()->routeIs('project.table.task.show') || request()->routeIs('project.table.show');
        if ($tableView) {

            // Retrieve all the tasks for the project through the task tables
            $tasksPaginated = Task::whereHas('taskTable', function ($query) use ($project) {
                $query->where('position', '!=', '0')
                    ->where('project', $project->id);
            })
            ->whereNull('finish_date') // Filter tasks with no finish date
            ->when($search, function ($query) use ($search) {
                    if ($search) {
                        $query->selectraw(
                            "*, ts_rank(tsvectors, to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')) as rank",
                            [$search]
                        )->whereRaw(
                            "tsvectors @@ to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')",
                            [$search]
                        );
                    }
            })
            ->when($filter, function ($query) use ($filter) {
                $query->where('priority', $filter);
            })
            ->orderBy('start_date', 'desc') // Order by start_date descending
            ->paginate(10); // Paginate results (10 per page)


            // Retrieve all the tasks for the project through the task tables
            $tasks = Task::whereHas('taskTable', function ($query) use ($project) {
                $query->where('name', '!=', 'Deleted tasks')
                    ->where('project', $project->id);
            })
            ->whereNull('finish_date') // Filter tasks with no finish date
            ->when($search, function ($query) use ($search) {
                $query->whereRaw(
                    "tsvectors @@ to_tsquery('english', NULLIF(websearch_to_tsquery('english', ?)::text, '') || ':*')",
                    [$search]
                );
            })
            ->when($filter, function ($query) use ($filter) {
                $query->where('priority', $filter);
            })
            ->get();


            // Compute summary data
            $startDates = $tasks->pluck('start_date')->map(fn($date) => \Carbon\Carbon::parse($date));
            $deadlineDates = $tasks->pluck('deadline_date')->filter()->map(fn($date) => \Carbon\Carbon::parse($date));
            $priorities = $tasks->pluck('priority')->map(function ($priority) {
                return $priority->value;
            })->toArray();
            $priorityCounts = array_merge(
                ['High' => 0, 'Medium' => 0, 'Low' => 0],
                array_count_values($priorities)
            );
            $startDateRange = $startDates->isNotEmpty() ? $startDates->min()->format('d M Y') . ' - ' . $startDates->max()->format('d M Y') : 'N/A';
            $deadlineRange = $deadlineDates->isNotEmpty() ? $deadlineDates->min()->format('d M Y') . ' - ' . $deadlineDates->max()->format('d M Y') : 'N/A';
            // Return the view with all the relevant data
            return view('pages.project', compact(
                'user','project','taskTables','search', 'filter', 'isCoordinator', 'isMember', 'tableView', 'tasksPaginated', 'startDateRange', 'deadlineRange', 'priorityCounts', 'left_top', 'left_bottom', 'right_top', 'right_bottom', 'projectEvents', 'assignedTasks'
            ));
        }
        return view('pages.project', compact(['user','project','taskTables','search', 'filter', 'isCoordinator', 'isMember', 'tableView', 'forumMessages', 'projectMembers', 'left_top', 'left_bottom', 'right_top', 'right_bottom', 'projectEvents', 'assignedTasks']));
    }
    /**
     * Mark the project last accessed
     */
    public function accessed($id)
    {
        try {
            $project = Project::findOrFail($id);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested project could not be found.',
            ]);
            return response()->json(['success' => false, 'error' => 'The requested project could not be found.'], 404);
        }
        try {
            $this->authorize('interact', $project);
        }
        catch (\Exception $e) {
            return response()->json(['success' => true], 200);
        }
        $project->members()->updateExistingPivot(Auth::user()->id, ['last_accessed' => now()]);
        return response()->json(['success' => true], 200);
    }

    /**
     * Remove user from project as project coordinator, or leave project as a member
     */
    public function removeAccountFromProject(Project $project, Account $user)
    {
        $authUser = Auth::user();

        //check action authorization for coordinator or member
        try {
            if($authUser->id != $user->id) {
                $this->authorize('removeAccount', $project);
            }
            else {
                $this->authorize('leaveProject', $project);
            }
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return redirect()->back();
        }

        // Remove user from assigned tasks
        $tasks = Task::whereHas('taskTable', function ($query) use ($project) {
            $query->where('project', $project->id);
        })->get();

        foreach ($tasks as $task) {
            $task->accounts()->detach($user->id);
        }

        $project->members()->detach($user->id);

        Invitation::where('account', $user->id)
            ->where('project', $project->id)
            ->delete();

        if($authUser->id == $user->id) {
            session()->flash('message', [
                'type' => 'success',
                'text' => 'Left Project successfully..',
            ]);
            return redirect('/projects');
        }
        session()->flash('message', [
            'type' => 'success',
            'text' => 'User removed from the project.',
        ]);
        return redirect()->back()->with('success', 'Account removed from the project.');
    }

    /**
     * Show the project members page
     */
    public function showProjectMembers(Project $project){
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
        $account = Auth::user();
        $isCoordinator = Auth::user()->id === $project->project_coordinator_id;
        $invitedAccountsIds = $project->invitations()
            ->where('accepted', false)  // Filter invitations where accepted is false
            ->with('account')  // Eager load the 'account' relationship
            ->get()
            ->pluck('account');
        $invitedAccounts = Account::whereIn('id', $invitedAccountsIds)->paginate(5);
        $projectMembers = $project->members()->paginate(5);
        return view('pages.projectMembers', compact( 'project', 'isCoordinator', 'invitedAccounts', 'account', 'projectMembers'));
    }

    /**
     * Show members profile page
     */
    public function showMemberPage(Project $project, Account $member)
    {
        if(Auth::check()) {
            try {
                $this->authorize('view', $project);
            } catch (\Exception $e) {
                session()->flash('message', [
                    'type' => 'error',
                    'text' => 'Unauthorized action.',
                ]);
                return redirect()->back();
            }
            if ($project->members->contains($member)) {
                $city = $member->city ? City::find($member->city) : null;
                $country = $city ? Country::find($city->country) : null;
                $accountImage = $member->accountImage ?? null;
                $account = $member;
                return view('pages.accountpage', compact('project','account', 'accountImage', 'city', 'country'));
            }
            else {
                session()->flash('message', [
                    'type' => 'error',
                    'text' => 'The requested user could not be found.',
                ]);
                return redirect()->back();
            }
        }

        session()->flash('message', [
            'type' => 'error',
            'text' => 'Not logged-in.',
        ]);
        return view('pages.mainPage');
    }

    /**
     * Add project to Favourite Projects
     */
    public function addToFavourites(Request $request, $projectId)
    {
        $user = Auth::user();
        try {
            $project = Project::findOrFail($projectId);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested project could not be found.',
            ]);
            return redirect()->back();
        }
        $user->projects()->updateExistingPivot($project->id, [
            'is_favourite' => true,
        ]);
        session()->flash('message', [
            'type' => 'success',
            'text' => 'Project added to favourites successfully.',
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Project added to favourites successfully.',
        ], 200);
    }

    /**
     * Remove project from Favourite Projects
     */
    public function removeFromFavorites(Request $request, $projectId)
    {
        $user = Auth::user();
        try {
            $project = Project::findOrFail($projectId);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested project could not be found.',
            ]);
            return redirect()->back();
        }
        $user->projects()->updateExistingPivot($project->id, [
            'is_favourite' => false,
        ]);
        session()->flash('message', [
            'type' => 'success',
            'text' => 'Project removed from favourites successfully.',
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Project removed from favourites successfully.',
        ], 200);
    }



    /**
     * Change the project coordinator
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
        // Check if the new coordinator exists
        try {
            $newCoordinator = Account::findOrFail($newCoordinatorId);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested account could not be found.',
            ]);
            return redirect()->back()->with('error', 'The requested account could not be found');
        }
        // check action authorization
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
        //send notifications
        $notification = DB::table('notification')
            ->where('project', $projectId)
            ->where('type', 'Coordinator_Change')
            ->latest('id')
            ->first();
        $projectMembers = $project->members;
        foreach ($projectMembers as $member) {
            broadcast(new NotificationEvent($notification->id, $member->id));
        }
        session()->flash('message', [
            'type' => 'success',
            'text' => 'Project coordinator changed successfully.',
        ]);
        return redirect()->back()->with('success', 'Project coordinator changed successfully');
    }

    /**
     * Change the project description
     */
    public function changeDescription(Request $request, $projectId)
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
            $this->authorize('changeDescription', $project);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return response()->json(['error' => 'Failed changing project description.'], 403);
        }
        $request->validate([
            'description' => 'nullable|string|max:500',
        ]);
        $project->update(['description' => $request->description]);
        return response()->json(['success' => true, 'message' => 'Project description changed successfully', 'new_description' => $request->description]);
    }

    /**
     * Show the forum page
     */
    public function showForumPage(Request $request, $projectId)
    {
        $user = Auth::user();
        try {
            $project = Project::findOrFail($projectId);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'The requested project could not be found.',
            ]);
            return redirect()->back();
        }
        // check action authorization
        try {
            $this->authorize('interact', $project);
            $isMember = true;
        }
        catch (\Exception $e) {
            try {
                $this->authorize('view', $project);
                session()->flash('message', [
                    'type' => 'success',
                    'text' => 'You can only view this project, not edit.',
                ]);
                $isMember = false;
            }
            catch (\Exception $e) {
                session()->flash('message', [
                    'type' => 'error',
                    'text' => 'Unauthorized action.',
                ]);
                return redirect()->back();
            }
        }
        $forumMessages = $project->forumMessages()->orderBy('create_date', 'asc')->get();
        return view('pages.forum', compact('project','forumMessages', 'isMember'));
    }

    /**
     * Archive or unarchive a project
     */
    public function toggleArchive(Project $project)
    {
        if (Auth::user()->id !== $project->project_coordinator_id) {
            abort(403, 'Unauthorized action.');
        }
        $project->archived = !$project->archived;
        $project->save();


        if ($project->archived) {
            Invitation::where('project', $project->id)
                ->delete();
            session()->flash('message', [
                'type' => 'success',
                'text' => 'Project archived successfully.',
            ]);
        } else {
            session()->flash('message', [
                'type' => 'success',
                'text' => 'Project unarchived successfully.',
            ]);
        }

        $message = $project->archived ? 'Project archived successfully.' : 'Project unarchived successfully.';


        return redirect()->route('project.show', ['project' => $project])->with('success', $message);
    }

    /**
     * Show the project timeline page
     */
    public function showProjectTimelinePage(Request $request, Project $project)
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
        // Fetch all project events for chart data (without pagination)
        $allProjectEvents = $project->projectEvents()->orderBy('time', 'desc')->get();

        // Paginate the events for the view
        $projectEvents = $project->projectEvents()->orderBy('time', 'desc')->paginate(10);

        // Determine the date range
        $minDate = $allProjectEvents->min('time'); // Use 'time' for min/max
        $maxDate = $allProjectEvents->max('time');

        $start = $minDate ? (new \DateTime($minDate))->setTime(0, 0) : new \DateTime();
        $end = $maxDate ? (new \DateTime($maxDate))->setTime(0, 0) : new \DateTime();

        // Generate the date range
        $interval = new \DateInterval('P1D'); // 1 day interval
        $dateRange = new \DatePeriod($start, $interval, $end->modify('+1 day')); // Include the last date

        // Count events per day
        $eventsPerDay = [];
        foreach ($dateRange as $date) {
            $day = $date->format('Y-m-d');
            $eventsPerDay[] = [
                'date' => $day,
                'count' => $allProjectEvents->filter(function ($event) use ($day) {
                    // Group by date (ignoring time)
                    $eventDate = (new \DateTime($event->time))->format('Y-m-d');
                    return $eventDate === $day;
                })->count(),
            ];
        }

        // Extract data and labels for the chart
        $data = array_column($eventsPerDay, 'count');
        $labels = array_column($eventsPerDay, 'date');

        // Build the chart
        $chart = Chartjs::build()
            ->name('ProjectEventsChart')
            ->type('line')
            ->size(['width' => 400, 'height' => 400])
            ->labels($labels)
            ->datasets([
                [
                    'label' => 'Events Count',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'data' => $data,
                ]
            ])
            ->options([
                'responsive' => true, // Enable responsiveness
                'maintainAspectRatio' => false, // Allows the chart to grow dynamically
                'scales' => [
                    'x' => [
                        'type' => 'time',
                        'time' => [
                            'unit' => 'day',
                            'tooltipFormat' => 'YYYY-MM-DD',
                            'displayFormats' => [
                                'day' => 'YYYY-MM-DD',
                            ],
                        ],
                    ],
                ],
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Daily Project Events',
                    ],
                ],
            ]);

        return view('pages.projectTimeline', compact('project', 'projectEvents', 'chart'));
    }

    /*
     * Update the slider value from the bar in the Project Suggestions Page
     */
    public function updateSliderValue(Request $request, Project $project) {
        $user = Auth::user();

        // Validate the input
        $request->validate([
            'weight' => 'required|integer|min:0|max:100',
        ]);

        // Check if the user is authorized for this project
        try {
            $this->authorize('view', $project);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized action.'], 403);
        }

        // Update the task_sort_weight for the user and project
        DB::table('project_member')
            ->where('account', $user->id)
            ->where('project', $project->id)
            ->update(['task_sort_weight' => $request->input('weight')]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Show the project settings page
     */
    public function settings(Project $project)
    {
        $account = Auth::user();
        //check action authorization
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
        $isCoordinator = $account->id === $project->project_coordinator_id;
        try {
            $this->authorize('interact', $project);
            $isMember = true;
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'success',
                'text' => 'You can only view this project, not edit.',
            ]);
            $isMember = false;
        }
        $userSettings = null;
        if($isMember){
            $userSettings = $project->members()
                ->where('account', $account->id)
                ->first()
                ->pivot;
        }
        return view('pages.settings', compact('project', 'isCoordinator', 'userSettings', 'account', 'isMember'));
    }

    /**
     * Update the project privacy settings
     */
    public function updatePrivacy(Request $request, Project $project)
    {
        try {
            $this->authorize('updatePrivacy', $project);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return response()->json(['error' => 'Failed updating project privacy.'], 403);
        }

        $validated = $request->validate([
            'visibility' => 'required|in:public,private',
        ]);

        $project->ispublic = $validated['visibility'] === 'public';
        $project->save();
        $visibilityText = $project->ispublic ? 'public' : 'private';
        session()->flash('message', [
            'type' => 'success',
            'text' => "Privacy settings updated successfully. The project is now {$visibilityText}.",
        ]);

        return redirect()->route('project.settings', ['project' => $project->id])
            ->with('success', 'Privacy settings updated successfully.');
    }

    /**
     * Update Project Permissions
     */
    public function updatePermissions(Request $request, Project $project)
    {
        // Check action authorization
        try {
            $this->authorize('changePermissions', $project);
        }
        catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unauthorized action.',
            ]);
            return response()->json(['error' => 'Failed updating project privacy.'], 403);
        }

        // List of available permissions
        $permissions = [
            'add_deadline_permission',
            'create_task_permission',
            'edit_task_permission',
            'assign_tasks_permission',
            'create_tasktable_permission',
            'add_member_permission',
            'view_deleted_tasks_permission',
        ];

        // Update the permissions
        foreach ($permissions as $permission) {
            $project->$permission = $request->has("permissions.$permission");
        }

        $project->save();

        session()->flash('message', [
            'type' => 'success',
            'text' => "Members permissions updated successfully.",
        ]);

        return redirect()->route('project.settings', ['project' => $project->id])
            ->with('success', 'Permissions updated successfully.');
    }

    /**
     * Update the project layout
     */
    public function updateComponent(Request $request, $projectId)
    {
        // Obter o projeto e verificar se ele existe
        $project = Project::findOrFail($projectId);

        //check action authorization
        try {
            $this->authorize('interact', $project);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'You can´t edit this project´s layout.',
            ]);
            return redirect()->back();
        }

        $user = Auth::user();

        // Validation to avoid double selection of one component
        $validatedData = $request->validate([
            'topLeftComponent' => 'required|in:forum_component,members_component,analytics_component,productivity_component,none',
            'topRightComponent' => 'required|in:forum_component,members_component,analytics_component,productivity_component,none',
            'bottomLeftComponent' => 'required|in:forum_component,members_component,analytics_component,productivity_component,none',
            'bottomRightComponent' => 'required|in:forum_component,members_component,analytics_component,productivity_component,none',
        ]);

        $components = [
            $validatedData['topLeftComponent'],
            $validatedData['topRightComponent'],
            $validatedData['bottomLeftComponent'],
            $validatedData['bottomRightComponent'],
        ];

        try {
            $this->authorize('validateComponents', [$project, $components]);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'A component cannot be used in multiple positions.',
            ]);
            return redirect()->back();
        }


        // Prepare the data to update the pivot table
        $updateData = [
            'forum_component' => 'None',
            'members_component' => 'None',
            'analytics_component' => 'None',
            'productivity_component' => 'None',
        ];

        // Map values to positions
        $positions = ['topLeftComponent' => 'LeftUp', 'topRightComponent' => 'RightUp', 'bottomLeftComponent' => 'LeftDown', 'bottomRightComponent' => 'RightDown'];

        foreach ($positions as $key => $position) {
            $component = $validatedData[$key];
            if ($component !== 'none') {
                $updateData[$component] = $position;
            }
        }

        // Update the pivot table
        $project->members()->updateExistingPivot($user->id, $updateData);

        session()->flash('message', [
            'type' => 'success',
            'text' => 'Sidebar updated successfully.',
        ]);

        return redirect()->route('project.show', ['project' => $projectId])
            ->with('message', 'Sidebar updated successfully!');
    }


}


