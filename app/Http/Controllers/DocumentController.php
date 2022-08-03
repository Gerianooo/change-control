<?php

namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Throwable;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Document/Index');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function paginate(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|max:1000',
            'order.key' => 'nullable|string',
            'order.dir' => 'nullable|in:asc,desc',
        ]);

        return Document::where(function (Builder $query) use ($request) {
            $model = new Document();
            $search = '%' . $request->search . '%';

            foreach ($model->getFillable() as $column) {
                $query->orWhere($column, 'like', $search);
            }
        })
        ->orderBy($request->input('order.key') ?: 'name', $request->input('order.dir') ?: 'asc')
        ->paginate($request->per_page ?: 10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Document/Create')->with([
            'users' => User::get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:documents',
            'max_revision_interval' => 'required|integer',
        ]);

        if ($document = Document::create($request->all())) {
            return redirect()->back()->with('success', __(
                'document `:name` has been created', [
                    'name' => $document->name,
                ]
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t create document',
        ));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document)
    {
        return Inertia::render('Document/Show')->with([
            'document' => $document,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function edit(Document $document)
    {
        return Inertia::render('Document/Edit')->with([
            'document' => $document,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Document $document)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'code' => ['required', 'string', Rule::unique('documents')->ignore($document->id)],
            'max_revision_interval' => ['required', 'integer'],
        ]);

        if ($document->update($request->all())) {
            return redirect()->back()->with('success', __(
                'document `:name` has been updated', [
                    'name' => $document->name,
                ]
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t update document',
        ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function destroy(Document $document)
    {
        if ($document->delete()) {
            return redirect()->back()->with('success', __(
                'document `:name` has been deleted', [
                    'name' => $document->name,
                ]
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t delete document',
        ));
    }

    /**
     * @param \App\Models\Document $document
     * @return \Illuminate\Http\Response
     */
    public function revisions(Document $document)
    {
        return Inertia::render('Document/Revision')->with([
            'document' => $document,
        ]);
    }
    
    /**
     * @param \App\Models\Document $document
     * @return \Illuminate\Http\Response
     */
    public function approvers(Document $document)
    {
        return Inertia::render('Document/Approver')->with([
            'document' => $document,
            'approvers' => $document->approvers,
            'users' => User::where('username', '!=', 'su')->get(['id', 'name']),
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Document $document
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function addApproverFor(Request $request, Document $document, User $user)
    {
        $request->validate([
            'user' => 'required|exists:users,id',
        ]);
        
        $approver = $document->approvers()->create([
            'user_id' => $user->id,
        ]);

        if ($approver) {
            return redirect()->back()->with('success', __(
                'user `:name` has been added to document approver', [
                    'name' => $user->name,
                ],
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t add approver',
        ));
    }

    /**
     * @param \App\Models\Approver $approver
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function updateApprover(Approver $approver, User $user)
    {
        if ($approver->update(['user_id' => $user->id])) {
            return redirect()->back()->with('success', __(
                'approver has been updated',
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t remove approver',
        ));
    }

    /**
     * @param \App\Models\Approver $approver
     * @return \Illuminate\Http\Response
     */
    public function detachApprover(Approver $approver)
    {
        if ($approver->delete()) {
            $approver->approverable->approvers()->where('position', '>', $approver->position)->decrement('position');

            return redirect()->back()->with('success', __(
                'user `:name` has been removed from document approver', [
                    'name' => $approver->user->name,
                ]
            ));
        }
    }

    /**
     * @param \App\Models\Document $document
     * @return \Illuminate\Http\Response
     */
    public function approvals(Document $document)
    {
        return Inertia::render('Document/Approval')->with([
            'document' => $document,
            'approves' => $document->approves,
            'approvers' => $document->approvers,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Document $document
     * @return \Illuminate\Http\Response
     */
    public function request(Request $request, Document $document)
    {
        if ($document->pending && !$document->rejected) {
            return redirect()->back()->with('error', __(
                'document is waiting for response approver',
            ));
        }

        if ($document->approved) {
            return redirect()->back()->with('error', __(
                'document is already approved',
            ));
        }

        $user = $request->user();
        $approve = $document->approves()->create();
        $document->approvers->each(function (Approver $approver) use ($approve, $user) {
            $approve->approvals()->create([
                'status' => 'pending',
                'requester_id' => $user->id,
                'requested_at' => now(),
                'responder_id' => $approver->user->id,
            ]);
        });

        return redirect()->back()->with('success', __(
            'approval has been requested',
        ));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Document $document
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, Document $document)
    {
        $user = $request->user();
        $approve = $document->approve;
        $approvers = $document->approvers;

        if (!$approve) {
            return redirect()->back()->with('error', __(
                'nothing to do',
            ));
        }

        if ($document->rejected) {
            return redirect()->back()->with('error', __(
                'document is rejected',
            ));
        }

        if ($document->approved) {
            return redirect()->back()->with('info', __(
                'document is already approved',
            ));
        }

        if (!$user->hasRole('superuser')) {
            if ($current = $approvers->where('user_id', $user->id)->first()) {
                if (($before = $approvers->where('position', $current->position - 1)->first()) && ($latest = $approve->approvals->where('responder_id', $before->user_id)->first())) {
                    if ($latest->status !== 'approved') {
                        return redirect()->back()->with('error', __(
                            'user `:name` haven\'t response the approval', [
                                'name' => $latest->responder->name,
                            ]
                        ));
                    }
                }
            } else {
                return redirect()->back()->with('error', __(
                    'can\'t find approval for you',
                ));
            }
        }
        
        $approval = $approve->approvals
                            ->where('status', 'pending')
                            ->when(!$user->hasRole('superuser'), function ($query) use ($user) {
                                $query->where('responder_id', $user->id);
                            })
                            ->first();

        if ($approval) {
            if ($approval->update(['status' => 'approved'])) {
                return redirect()->back()->with('success', __(
                    'document `:name` successfuly approved', [
                        'name' => $document->name,
                    ],
                ));
            }

            return redirect()->back()->with('error', __(
                'can\'t approve document, try again later',
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t find approval for you',
        ));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Document $document
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, Document $document)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        $user = $request->user();
        $approve = $document->approve;
        $approvers = $document->approvers;

        if (!$approve) {
            return redirect()->back()->with('error', __(
                'nothing to do',
            ));
        }

        if ($document->rejected) {
            return redirect()->back()->with('error', __(
                'document already is rejected',
            ));
        }

        if ($document->approved) {
            return redirect()->back()->with('info', __(
                'document is already approved',
            ));
        }

        if (!$user->hasRole('superuser')) {
            if ($current = $approvers->where('user_id', $user->id)->first()) {
                if (($before = $approvers->where('position', $current->position - 1)->first()) && ($latest = $approve->approvals->where('responder_id', $before->user_id)->first())) {
                    if ($latest->status !== 'approved') {
                        return redirect()->back()->with('error', __(
                            'user `:name` haven\'t response the approval', [
                                'name' => $latest->responder->name,
                            ]
                        ));
                    }
                }
            } else {
                return redirect()->back()->with('error', __(
                    'can\'t find approval for you',
                ));
            }
        }
        
        $approval = $approve->approvals
                            ->where('status', 'pending')
                            ->when(!$user->hasRole('superuser'), function ($query) use ($user) {
                                $query->where('responder_id', $user->id);
                            })
                            ->first();

        if ($approval) {
            if ($approval->update(['status' => 'rejected', 'responder_note' => $request->note])) {
                return redirect()->back()->with('success', __(
                    'document `:name` successfuly rejected', [
                        'name' => $document->name,
                    ],
                ));
            }

            return redirect()->back()->with('error', __(
                'can\'t reject document, try again later',
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t find approval for you',
        ));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Document $document
     * @return \Illuminate\Http\Response
     */
    public function saveApproverFor(Request $request, Document $document)
    {
        $request->validate([
            'approvers.*.id' => 'required|integer|exists:approvers,id',
            'approvers.*.position' => 'required|integer|min:0',
            'approvers.*.user.id' => 'required|integer|exists:users,id',
        ]);

        DB::beginTransaction();

        try {
            $request->collect('approvers')
                    ->map(function ($approver) use (&$i) {
                        return collect(array_merge(
                            $approver, [
                                'position' => ++$i,
                            ]
                        ));
                    })
                    ->each(function ($approver) {
                        return Approver::where('id', $approver['id'])
                                        ->update(
                                            $approver->only([
                                                'approverable_id',
                                                'approverable_type',
                                                'user_id',
                                                'position',
                                            ])->toArray()
                                        );
                    });

            DB::commit();

            return redirect()->back()->with('success', __(
                'approvers has been updated',
            ));
        } catch (Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', __(
                $e->getMessage(),
            ));
        }

    }
}
