<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\ForumMessageController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TaskTableController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('projects'); 
    }
    return redirect()->route('main.page');
});

//Route that require not being authenticated
Route::middleware('guest')->group(function () {
    Route::get('/mainPage', [LoginController::class,'mainPage'])->name('main.page');
});
//Routes that require not being authenticated or being an admin
Route::controller(RegisterController::class)->middleware('admin_or_guest')->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register')->name('register.submit');
});
Route::get('/mainPage', [LoginController::class, 'mainPage'])->name('main.page');
Route::get('/aboutUs', [LoginController::class, 'aboutUs'])->name('aboutUs.page');


// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});
Route::controller(AccountController::class)->group(function () {
    Route::get('/forgot-password', 'showForgotPasswordForm')->name('forgot.password.show');
    Route::post('/forgot-password', 'sendRecoverPasswordMail');
    Route::get('/reset-password/{token}', 'showResetPasswordForm')->name('password.reset');
    Route::post('/reset-password', 'resetPassword')->name('password.update');
});

//Routes that require authentication
Route::middleware('auth')->group(function () {
    //Account
    Route::controller(AccountController::class)->group(function () {
        Route::get('/account', 'showAccountPage')->name('account.show');
        Route::get('account/edit', 'showEditAccountPage')->name('account.edit');
        Route::put('/account/update/{id?}', 'update')->where('id', '[0-9]+')->name('account.update');
        Route::put('/account/updatePassword/{id?}', 'updatePassword')->where('id', '[0-9]+')->name('account.update.password');
        Route::delete('/admin/users/{id}/delete', 'delete')->name('account.delete');
        Route::get('/account/taskHub', 'showTaskHubPage')->name('account.taskHub');
        Route::get('/search-users','searchUsers')->name('users.search');
    });

    // Invitations
    Route::controller(InvitationController::class)->group(function () {
        Route::get('/invitation/{invitation}/email/accept', 'accept')->where('invitation', '[0-9]+')->name('invitation.email.accept');
        Route::patch('/invitation/{invitation}/accept', 'accept')->where('invitation', '[0-9]+')->name('invitation.accept');
        Route::delete('/invitation/{invitation}/decline', 'decline')->where('invitation', '[0-9]+')->name('invitation.decline');
        Route::post('/project/{project}/invite', 'invite')->name('invitation.invite');
    });
    // Notifications
    Route::controller(NotificationController::class)->group(function () {
        Route::patch('/notification/{notification}/check', 'check')->where('notification', '[0-9]+')->name('notification.check');
    });
    //Projects
    Route::controller(ProjectController::class)->group(function () {
        Route::get('/projects', 'list')->name('projects');
        Route::get('/projects/search', 'searchAjax')->name('projects.search.ajax');
        Route::get('/projects/tab/search', 'tabSearchAjax')->name('projects.tab.search.ajax');
        Route::post('/projects', 'store')->name('projects.store');
        Route::get('/project/{project}/task/{task}', 'show')->where('project', '[0-9]+')->where('task', '[0-9]*')->name('project.task.show');
        Route::get('/project/{project}/table/task/{task}', 'show')->where('project', '[0-9]+')->where('task', '[0-9]*')->name('project.table.task.show');
        Route::get('/project/{project}', 'show')->where('project', '[0-9]+')->name('project.show');
        Route::get('/project/{project}/table', 'show')->where('project', '[0-9]+')->name('project.table.show');
        Route::get('/project/{project}/projectMembers', 'showProjectMembers')->where('project', '[0-9]+')->name('projectMembers.show');
        Route::get('/project/{project}/projectMembers/{member}', 'showMemberPage')->where('project', '[0-9]+')->where('member', '[0-9]+')->name('memberAccount.show');
        Route::put('/project/{project}/addToFavorites', 'addToFavourites')->where('project', '[0-9]+')->name('project.addToFavorites');
        Route::put('/project/{project}/removeFromFavorites', 'removeFromFavorites')->where('project', '[0-9]+')->name('project.removeFromFavorites');
        Route::put('/project/{project}/changeCoordinator/{account}', 'changeCoordinator')->where('project', '[0-9]+')->where('account', '[0-9]+')->name('project.changeCoordinator');
        Route::post('/project/{project}/changeDescription', 'changeDescription')->where('project', '[0-9]+')->name('project.changeDescription');
        Route::put('/project/{project}/accessed', 'accessed')->where('project', '[0-9]+')->name('project.accessed');
        Route::get('/project/{project}/forum', 'showForumPage')->where('project', '[0-9]+')->name('project.forum');
        Route::delete('/projects/{project}/members/{user}', 'removeAccountFromProject')->name('project.removeAccount');
        Route::patch('/projects/{project}/toggle-archive',  'toggleArchive')->name('projects.toggleArchive');
        Route::get('/project/{project}/timeline', 'showProjectTimelinePage')->where('project', '[0-9]+')->name('project.timeline');
        Route::post('/project/{project}/update-slider-value', 'updateSliderValue')->name('slider.update');
        Route::get('/project/{project}/settings', 'settings')->name('project.settings');
        Route::patch('/projects/{project}/privacy', 'updatePrivacy')->name('project.updatePrivacy');
        Route::put('/project/{project}/update-permissions', 'updatePermissions')->name('project.updatePermissions');
        Route::post('/project/{project}/update-components','updateComponent')->name('project.updateComponent');
    });
    // TaskTables
    Route::controller(TaskTableController::class)->group(function () {
        Route::get('/project/{project}/task-tables', 'show')->name('taskTables.show');
        Route::get('/project/{project}/task-tables/search', 'searchAjax')->name('task.search.ajax');
        Route::get('/project/{project}/table/task-tables/search', 'searchAjax')->name('tableView.task.search.ajax');
        Route::post('/project/{project}/task-tables', 'store')->name('taskTable.store');
        Route::delete('/taskTables/{id}', 'destroy')->name('taskTable.destroy');
    })->where(['project' => '[0-9]+']);
    //Tasks
    Route::controller(TaskController::class)->group(function () {
        Route::get('/task/{task}', 'show')->name('task');
        Route::put('/task/{task}', 'update')->where('task', '[0-9]+')->name('task.update');
        Route::put('/task/{task}/delete', 'delete')->where('task', '[0-9]+')->name('task.delete');
        Route::patch('/task/{task}/completed', 'completed')->where('task', '[0-9]+')->name('task.completed');
        Route::patch('/task/{task}/uncompleted', 'uncompleted')->where('task', '[0-9]+')->name('task.uncompleted');
        Route::post('/taskTable/{taskTable}/storeTask', 'store')->where('taskTable', '[0-9]+')->name('task.store');
        //Route::delete('/tasks/{task}/remove-user/{user}', 'removeUser')->where('task', '[0-9]+')->where('user', '[0-9]+')->name('tasks.removeUser');
        Route::put('/task/{task}/change-position/{posDest}/{tableDest}', 'changeTaskPosition')->where('task', '[0-9]+')->where('posDest', '[0-9]+')->name('tasks.changePosition');
        Route::get('/task/{taskId}/users',  'usersAssigned');
        Route::post('/task/{taskId}/assign-user',  'assignUser');
        Route::delete('task/{taskId}/removeAccount/{user}', 'removeAccount');

    });
    Route::controller(ForumMessageController::class)->group(function(){
        Route::post('/project/{project}/forum', 'store')->where('project', '[0-9]+')->name('forum.store');
        Route::delete('/forum/{message}', 'destroy')->where('forumMessage', '[0-9]+')->name('forum.delete');
        Route::post('/forum/{message}/edit', 'update')->where('forumMessage', '[0-9]+')->name('forum.edit');
    });
    Route::controller(CommentController::class)->group(function(){
        Route::post('/task/{taskId}/comment', 'store')->where('taskId', '[0-9]+')->name('comment.store');
    });
});


Route::middleware(['auth', 'is_admin'])->group(function () {
    Route::post('/admin', [RegisterController::class, 'register']);
    Route::get('/admin/accounts', [AdminController::class, 'listAccounts'])->name('admin.accounts');
    Route::get('/admin/projects', [AdminController::class, 'listProjects'])->name('admin.projects');
    Route::patch('/admin/block/{id}', [AdminController::class, 'blockAccount'])->where('id', '[0-9]+')->name('admin.block');
    Route::get('/account/manage/{id}', [AccountController::class, 'manageAccount'])->where('id', '[0-9]+')->name('manageAccount.show');
    Route::get('/admin/accounts/search',  [AdminController::class,'searchAjax'])->name('users.search.ajax');
    Route::get('/admin/projects/search', [AdminController::class, 'searchProjectsAjax'])->name('admin.projects.search.ajax');
    
    //Route::get('/admin/projects', [AdminController::class, 'searchProjects'])->name('admin.projects.search');

    //Route::delete('/admin/delete/{id}', [AdminController::class, 'delete'])->where('id', '[0-9]+')->name('admin.delete');
});
