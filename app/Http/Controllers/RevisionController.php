<?php

namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\Document;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class RevisionController extends Controller
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
     * @param \App\Models\Revision $revision
     * @return \Illuminate\Http\Response
     */
    public function approver(Revision $revision)
    {
        return Inertia::render('Revision/Approver')->with([
            'revision' => $revision,
            'users' => User::where('username', '!=', 'su')->get(['id', 'name']),
            'approvers' => $revision->approvers,
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Revision $revision
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function addApproverFor(Request $request, Revision $revision, User $user)
    {
        $request->validate([
            'user' => 'required|exists:users,id',
        ]);

        $approver = $revision->approvers()->create([
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
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Revision $revision
     * @return \Illuminate\Http\Response
     */
    public function saveApproverFor(Request $request, Revision $revision)
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

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Document $document
     * @return \Illuminate\Http\Response
     */
    public function paginate(Request $request, Document $document)
    {
        $request->validate([
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|max:1000',
            'order.key' => 'nullable|string',
            'order.dir' => 'nullable|in:asc,desc',
        ]);

        return $document->revisions()
                        ->where(function (Builder $query) use ($request) {
                            $model = new Revision();
                            $search = '%' . $request->search . '%';

                            foreach ($model->getFillable() as $column) {
                                $query->orWhere($column, 'like', $search);
                            }
                        })
                        ->orderBy($request->input('order.key') ?: 'created_at', $request->input('order.dir') ?: 'desc')
                        ->paginate($request->per_page ?: 10);
    }

    /**
     * @param \App\Models\Revision $revision
     * @return \Illuminate\Http\Response
     */
    public function procedures(Revision $revision)
    {
        return $revision->procedures;
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
        $request->validate([
            'document_id' => 'required|integer|exists:documents,id',
        ]);

        $document = Document::findOrFail($request->document_id);
        $count = $document->revisions()->count();
        $expired = now()->addMonth($document->max_revision_interval);
        $code = sprintf('%s-%d', $document->code, $count);
        
        if ($revision = $document->revisions()->create([ 'code' => $code, 'expired_at' => $expired ])) {
            return redirect()->back()->with('success', __(
                'revision `:code` has been created', [
                    'code' => $revision->code,
                ]
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t create revision',
        ));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Revision  $revision
     * @return \Illuminate\Http\Response
     */
    public function show(Revision $revision)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Revision  $revision
     * @return \Illuminate\Http\Response
     */
    public function edit(Revision $revision)
    {
        return Inertia::render('Revision/Edit')->with([
            'revision' => $revision,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Revision  $revision
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Revision $revision)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Revision  $revision
     * @return \Illuminate\Http\Response
     */
    public function destroy(Revision $revision)
    {
        if ($revision->delete()) {
            return redirect()->back()->with('success', __(
                'revision `:code` has been deleted', [
                    'code' => $revision->code,
                ]
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t delete revision',
        ));
    }
}
