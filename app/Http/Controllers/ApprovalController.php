<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Approval  $approval
     * @return \Illuminate\Http\Response
     */
    public function show(Approval $approval)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Approval  $approval
     * @return \Illuminate\Http\Response
     */
    public function edit(Approval $approval)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Approval  $approval
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Approval $approval)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Approval  $approval
     * @return \Illuminate\Http\Response
     */
    public function destroy(Approval $approval)
    {
        //
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function documents()
    {
        return Inertia::render('Approval/Document');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function paginateDocuments(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|max:1000',
            'order.key' => 'nullable|string',
            'order.dir' => 'nullable|in:asc,desc',
        ]);

        return Document::whereHas('approves', function (Builder $query) use ($request) {
                            $user = request()->user();

                            $query->doesntHave('approvals', callback: function (Builder $query) {
                                        $query->where('status', 'rejected');
                                    })
                                    ->when(!$user->hasRole('superuser'), function (Builder $query) use ($user) {
                                        $query->whereRelation('approvals', 'responder_id', $user->id);
                                    });
                        })
                        ->where(function (Builder $query) use ($request) {
                            $query->where('name', 'like', '%' . $request->search . '%');
                        })
                        ->orderBy($request->input('order.key') ?: 'name', $request->input('order.dir') ?: 'asc')
                        ->paginate($request->per_page ?: 10);
    }
}
