<?php

namespace App\Http\Controllers;

use App\Models\Procedure;
use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class ProcedureController extends Controller
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
        $request->validate([
            'revision_id' => 'required|integer|exists:revisions,id',
            'name' => 'required|string',
        ]);

        $procedure = Procedure::create([
            'name' => $request->name,
            'revision_id' => $request->revision_id, 
            'position' => Procedure::where('revision_id', $request->revision_id)
                                    ->whereNull('parent_id')
                                    ->count() + 1,
        ]);

        if ($procedure) {
            return redirect()->back()->with('success', __(
                'procedure `:name` has been created', [
                    'name' => $procedure->name,
                ],
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t create procedure',
        ));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Procedure  $procedure
     * @return \Illuminate\Http\Response
     */
    public function show(Procedure $procedure)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Procedure  $procedure
     * @return \Illuminate\Http\Response
     */
    public function edit(Procedure $procedure)
    {
        return Inertia::render('Procedure/Edit')->with([
            'procedure' => $procedure,
            'content' => $procedure->content,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Procedure  $procedure
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Procedure $procedure)
    {
        $request->validate([
            'name' => 'string|required',
        ]);

        if ($procedure->update([ 'name' => $request->name ])) {
            return redirect()->back()->with('success', __(
                'procedure has been updated',
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t update procedure',
        ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Procedure  $procedure
     * @return \Illuminate\Http\Response
     */
    public function destroy(Procedure $procedure)
    {
        if ($procedure->delete()) {
            Procedure::where('revision_id', $procedure->revision_id)
                    ->where('parent_id', $procedure->parent_id)
                    ->where('position', '>=', $procedure->position)
                    ->decrement('position');

            return redirect()->back()->with('success', __(
                'procedure `:name` has been deleted', [
                    'name' => $procedure->name,
                ]
            ));
        }

        return redirect()->back()->with('error', __(
            'can\'t delete procedure',
        ));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Revision $revision
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, Revision $revision)
    {
        $procedures = collect($this->positions($request->procedures))->flatMap(function ($procedure) {
            return $this->flatMap($procedure);
        });
        
        DB::beginTransaction();

        try {
            $procedures->each(function (Procedure $procedure) {
                Procedure::where('id', $procedure->id)->update($procedure->only([
                    'revision_id',
                    'parent_id',
                    'name',
                    'position',
                ]));
            });

            DB::commit();

            return redirect()->back()->with('success', __(
                'position has been updated',
            ));
        } catch (Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', __(
                $e->getMessage()
            ));
        }
    }

    /**
     * @param array|\App\Models\Procedure $procedure
     * @return array
     */
    private function flatMap(array|Collection|Procedure $procedure)
    {
        return [
            $procedure,
            ...collect($procedure->childs)->flatMap(function ($procedure) {
                return $this->flatMap($procedure);
            }),
        ];
    }

    /**
     * @param \Illuminate\Support\Collection|\App\Models\Menu|array
     * @param int $parent
     */
    private function positions(Collection|Procedure|array $procedures, int $parent = null)
    {
        return array_map(function ($procedure) use (&$i, $parent) {
            $new = new Procedure($procedure);
            $new->id = $procedure['id'];
            $new->position = ++$i;
            $new->parent_id = $parent;
            $new->childs = array_key_exists('childs', $procedure) ? $this->positions($procedure['childs'], $new->id) : [];

            return $new;
        }, $procedures);
    }
}
