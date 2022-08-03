<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Document;
use App\Models\Revision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                            $user = $request->user();

                            $query->whereRelation('approvals', 'status', '!=', 'rejected')
                                    ->whereRelation('approvals', function (Builder $query) use ($user) {
                                        $query->when(!$user->hasRole('superuser'), fn (Builder $query) => $query->where('responder_id', $user->id))
                                                ->where('status', 'pending');
                                    });
                        })
                        ->where(function (Builder $query) use ($request) {
                            $search = '%' . $request->search . '%';

                            $query->where('name', 'like', $search)
                                    ->orWhere('max_revision_interval', 'like', $search)
                                    ->orWhere('created_at', 'like', $search)
                                    ->orWhere('updated_at', 'like', $search);
                        })
                        ->whereRaw(trim(<<< SQL
                            (
                                select
                                    count(*)
                                from
                                    approvals
                                where approvals.approvalable_id = (
                                    select
                                        id
                                    from
                                        approves
                                    where approves.approvable_id = documents.id
                                    order by
                                        created_at desc
                                    limit 1
                                )
                            )
                            !=
                            (
                                select
                                    count(*)
                                from
                                    approvals
                                where approvals.approvalable_id = (
                                    select
                                        id
                                    from
                                        approves
                                    where approves.approvable_id = documents.id
                                    order by
                                        created_at desc
                                    limit 1
                                )
                                and status = 'approved'
                            )
                        SQL))
                        ->orderBy($request->input('order.key') ?: 'name', $request->input('order.dir') ?: 'asc')
                        ->paginate($request->per_page ?: 10);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function revisions()
    {
        return Inertia::render('Approval/Revision');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function paginateRevisions(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|max:1000',
            'order.key' => 'nullable|string',
            'order.dir' => 'nullable|in:asc,desc',
        ]);

        return Revision::whereHas('approves', function (Builder $query) use ($request) {
                            $user = request()->user();

                            $query->doesntHave('approvals', callback: function (Builder $query) {
                                        $query->where('status', 'rejected');
                                    })
                                    ->when(!$user->hasRole('superuser'), function (Builder $query) use ($user) {
                                        $query->whereRelation('approvals', 'responder_id', $user->id);
                                    });
                        })
                        ->where(function (Builder $query) use ($request) {
                            $query->where('code', 'like', '%' . $request->search . '%');
                        })
                        ->orderBy($request->input('order.key') ?: 'code', $request->input('order.dir') ?: 'asc')
                        ->paginate($request->per_page ?: 10);
    }
}
