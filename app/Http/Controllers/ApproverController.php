<?php

namespace App\Http\Controllers;

use App\Models\Approver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ApproverController extends Controller
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
     * @param  \App\Models\Approver  $approver
     * @return \Illuminate\Http\Response
     */
    public function show(Approver $approver)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Approver  $approver
     * @return \Illuminate\Http\Response
     */
    public function edit(Approver $approver)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Approver  $approver
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Approver $approver)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Approver  $approver
     * @return \Illuminate\Http\Response
     */
    public function destroy(Approver $approver)
    {
        //
    }

    /**
     * @param \App\Models\Approver $approver
     * @return \Illuminate\Http\Response
     */
    public function up(Approver $approver)
    {
        $before = $approver->approverable
                            ->approvers()
                            ->where('position', '<', $approver->position)
                            ->orderBy('position', 'desc')
                            ->first();

        if (!$before) {
            return redirect()->back()->with('errors', __(
                'can\'t find approver before it',
            ));
        }

        DB::beginTransaction();

        try {
            Approver::where('id', $before->id)->update([
                'position' => $approver->position,
            ]);

            Approver::where('id', $approver->id)->update([
                'position' => $before->position,
            ]);

            DB::commit();

            return redirect()->back()->with('success', __(
                'position has been updated',
            ));
        } catch (Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', __(
                'can\'t update position. ' . $e->getMessage(),
            ));
        }
    }

    /**
     * @param \App\Models\Approver $approver
     * @return \Illuminate\Http\Response
     */
    public function down(Approver $approver)
    {
        $after = $approver->approverable
                            ->approvers()
                            ->where('position', '>', $approver->position)
                            ->orderBy('position', 'asc')
                            ->first();

        if (!$after) {
            return redirect()->back()->with('errors', __(
                'can\'t find approver after it',
            ));
        }

        DB::beginTransaction();

        try {
            Approver::where('id', $after->id)->update([
                'position' => $approver->position,
            ]);

            Approver::where('id', $approver->id)->update([
                'position' => $after->position,
            ]);

            DB::commit();

            return redirect()->back()->with('success', __(
                'position has been updated',
            ));
        } catch (Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', __(
                'can\'t update position. ' . $e->getMessage(),
            ));
        }
    }
}
