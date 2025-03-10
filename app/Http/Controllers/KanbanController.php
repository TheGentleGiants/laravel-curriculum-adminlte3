<?php

namespace App\Http\Controllers;

use App\Kanban;
use App\Medium;
use App\Organization;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class KanbanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort_unless(\Gate::allows('kanban_access'), 403);

        return view('kanbans.index');
    }

    protected function userKanbans()
    {
        $userCanSee = auth()->user()->kanbans;

        foreach (auth()->user()->currentGroups as $group) {
            $userCanSee = $userCanSee->merge($group->kanbans);
        }

        $organization = Organization::find(auth()->user()->current_organization_id)->kanbans;
        $userCanSee = $userCanSee->merge($organization);

        return $userCanSee->unique();
    }

    public function list()
    {
        abort_unless(\Gate::allows('kanban_access'), 403);
        $kanbans = (auth()->user()->role()->id == 1) ? Kanban::all() : $this->userKanbans();

        $edit_gate = \Gate::allows('kanban_edit');
        $delete_gate = \Gate::allows('kanban_delete');

        return empty($kanbans) ? '' : DataTables::of($kanbans)
            ->addColumn('action', function ($kanbans) use ($edit_gate, $delete_gate) {
                $actions = '';
                if ($edit_gate) {
                    $actions .= '<a href="'.route('kanbans.edit', $kanbans->id).'" '
                                    .'id="edit-kanban-'.$kanbans->id.'" '
                                    .'class="px-2 text-black">'
                                    .'<i class="fa fa-pencil-alt"></i>'
                                    .'</a>';
                }
                if ($delete_gate) {
                    $actions .= '<button type="button" class="btn text-danger" onclick="event.preventDefault();destroyDataTableEntry(\'kanbans\','.$kanbans->id.');"><i class="fa fa-trash"></i></button>';
                }

                return $actions;
            })

            ->addColumn('check', '')
            ->setRowId('id')
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_unless(\Gate::allows('kanban_create'), 403);

        return view('kanbans.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('kanban_create'), 403);
        $new_kanban = $this->validateRequest();

        $kanban = Kanban::Create([
            'title'         => $new_kanban['title'],
            'description'   => $new_kanban['description'],
            'medium_id'     => $this->getMediumIdByInputFilepath($new_kanban),
            'owner_id'      => auth()->user()->id,
        ]);

        LogController::set(get_class($this).'@'.__FUNCTION__);
        // axios call?
        if (request()->wantsJson()) {
            return ['message' => $kanban->path()];
        }

        return redirect($kanban->path());
    }

    /**
     * If $input['filepath'] is set and medium exists, id is return, else return is null
     *
     * @param  array  $input
     * @return mixed
     */
    public function getMediumIdByInputFilepath($input)
    {
        if (isset($input['filepath'])) {
            $medium = new Medium();

            return (null !== $medium->getByFilemanagerPath($input['filepath'])) ? $medium->getByFilemanagerPath($input['filepath'])->id : null;
        } else {
            return null;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Kanban  $kanban
     * @return \Illuminate\Http\Response
     */
    public function show(Kanban $kanban)
    {
        abort_unless((\Gate::allows('kanban_show') and $kanban->isAccessible()), 403);
        $kanban = $kanban->with(['statuses', 'statuses.items' => function ($query) use ($kanban) {
            $query->where('kanban_id', $kanban->id)->with(['owner', 'taskSubscription.task.subscriptions' => function ($query) {
                $query->where('subscribable_id', auth()->user()->id)
                    ->where('subscribable_type', 'App\User');
            }, 'mediaSubscriptions.medium'])->orderBy('order_id');
        }, 'statuses.items.subscriptions',
        ])->where('id', $kanban->id)->get()->first();

        LogController::set(get_class($this).'@'.__FUNCTION__);

        return view('kanbans.show')
                ->with(compact('kanban'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Kanban  $kanban
     * @return \Illuminate\Http\Response
     */
    public function edit(Kanban $kanban)
    {
        abort_unless((\Gate::allows('kanban_edit') and $kanban->isAccessible()), 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Kanban  $kanban
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Kanban $kanban)
    {
        abort_unless((\Gate::allows('kanban_edit') and $kanban->isAccessible()), 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Kanban  $kanban
     * @return \Illuminate\Http\Response
     */
    public function destroy(Kanban $kanban)
    {
        abort_unless((\Gate::allows('kanban_delete') and $kanban->isAccessible()), 403);

        //delete relations
        $kanban->items()->delete();
        $kanban->statuses()->delete();
        $kanban->subscriptions()->delete();

        $kanban->delete();
    }

    protected function validateRequest()
    {
        return request()->validate([
            'title'         => 'sometimes|required',
            'description'   => 'sometimes',
            'filepath'      => 'sometimes',
        ]);
    }
}
