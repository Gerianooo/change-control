<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('/approval')->name('approval.')->controller(App\Http\Controllers\ApprovalController::class)->group(function () {
        Route::get('/documents', 'documents')->name('document.index');
        Route::post('/documents', 'paginateDocuments')->name('document.paginate');
        Route::get('/revisions', 'revisions')->name('revision.index');
        Route::post('/revisions', 'paginateRevisions')->name('revision.paginate');
    });
    Route::resource('approval', App\Http\Controllers\ApprovalController::class);
    
    Route::resource('approver', App\Http\Controllers\ApproverController::class);
    Route::prefix('/approver')->name('approver.')->controller(App\Http\Controllers\ApproverController::class)->group(function () {
        Route::patch('/{approver}/up', 'up')->name('up');
        Route::patch('/{approver}/down', 'down')->name('down');
    });

    Route::resource('document', App\Http\Controllers\DocumentController::class);
    Route::prefix('/document/{document}')->name('document.')->controller(App\Http\Controllers\DocumentController::class)->group(function () {
        Route::get('/revisions', 'revisions')->name('revisions');
        Route::get('/approvers', 'approvers')->name('approvers');
        Route::post('/approvers/{user}', 'addApproverFor')->name('approver.add');
        Route::get('/approvals', 'approvals')->name('approvals');
        Route::post('/request', 'request')->name('approval.request');
        Route::patch('/approve', 'approve')->name('approve');
        Route::patch('/reject', 'reject')->name('reject');
        Route::patch('/approver/save', 'saveApproverFor')->name('approver.save');
    });
    Route::prefix('/document/{approver}')->name('document.')->controller(App\Http\Controllers\DocumentController::class)->group(function () {
        Route::delete('/detach', 'detachApprover')->name('approver.detach');
        Route::patch('/approvers/{user}', 'updateApprover')->name('approver.update');
    });

    Route::resource('revision', App\Http\Controllers\RevisionController::class);
    Route::prefix('/revision/{revision}')->name('revision.')->controller(App\Http\Controllers\RevisionController::class)->group(function () {
        Route::get('/approver', 'approver')->name('approver');
        Route::post('/attach/{user}', 'addApproverFor')->name('approver.add');
        Route::patch('/approver/save', 'saveApproverFor')->name('approver.save');
    });
    Route::prefix('/revision/{approver}/approver')->name('revision.')->controller(App\Http\Controllers\RevisionController::class)->group(function () {
        Route::delete('/detach', 'detachApprover')->name('approver.detach');
        Route::patch('/{user}', 'updateApprover')->name('approver.update');
    });

    Route::resource('procedure', App\Http\Controllers\ProcedureController::class);
    Route::patch('/procedure/{revision}/save', [App\Http\Controllers\ProcedureController::class, 'save'])->name('procedure.save');

    Route::resource('content', App\Http\Controllers\ContentController::class);

    Route::prefix('/superuser')->name('superuser.')->group(function () {
        Route::resource('permission', App\Http\Controllers\Superuser\PermissionController::class)->only([
            'index', 'store', 'update', 'destroy',
        ])->middleware(['permission:read permission']);

        Route::resource('role', App\Http\Controllers\Superuser\RoleController::class)->only([
            'index', 'store', 'update', 'destroy',
        ])->middleware(['permission:read role']);

        Route::patch('/role/{role}/detach/{permission}', [App\Http\Controllers\Superuser\RoleController::class, 'detach'])->name('role.detach')->middleware(['permission:update role']);

        Route::resource('user', App\Http\Controllers\Superuser\UserController::class)->only([
            'index', 'store', 'update', 'destroy',
        ])->middleware(['permission:read user']);

        Route::prefix('/user/{user}')->name('user.')->controller(App\Http\Controllers\Superuser\UserController::class)->middleware(['permission:update user'])->group(function () {
            Route::patch('/role/{role}/detach', 'detachRole')->name('role.detach');
            Route::patch('/permission/{permission}/detach', 'detachPermission')->name('permission.detach');
        });

        Route::patch('/menu/save', [App\Http\Controllers\Superuser\MenuController::class, 'save'])->name('menu.save')->middleware(['permission:update menu']);
        Route::resource('menu', App\Http\Controllers\Superuser\MenuController::class)->only([
            'index', 'store', 'update', 'destroy',
        ])->middleware(['permission:read menu']);
        
        Route::get('/activity/login', [App\Http\Controllers\ActivityController::class, 'login'])->name('activity.login');
    });
});