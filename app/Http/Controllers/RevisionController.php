<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Revision;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
