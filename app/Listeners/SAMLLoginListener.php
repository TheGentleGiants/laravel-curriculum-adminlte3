<?php

namespace App\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use App\Http\Controllers\LogController;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SAMLLoginListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Saml2LoginEvent  $event
     * @return void
     */
    public function handle(Saml2LoginEvent $event)
    {
        $messageId = $event->getSaml2Auth()->getLastMessageId();
        // Add your own code preventing reuse of a $messageId to stop replay attacks

        $user = $event->getSaml2User();
        session(['sessionIndex' => $user->getSessionIndex()]);
        session(['nameId' => $user->getNameId()]);

        $laravelUser = User::where('username', $user->getUserId())->get(); //find user by ID or attribute
        //if it does not exist create it and go on or show an error message
        Auth::login($laravelUser->first());

        // if users current_organization_id is not set -> get first organization as default
        if (auth()->user()->current_organization_id === null) {
            $u = \App\User::find(auth()->user()->id);
            // if provisioning is correct set current_organization_id else abort
            $u->current_organization_id = (auth()->user()->organizations()->first() === null) ? abort(422) : auth()->user()->organizations()->first()->id;
            $u->save();
        }
        // if users current_period_id is not set -> if not enroled in group current_period_id == null
        if (auth()->user()->current_period_id === null) {
            $u = \App\User::find(auth()->user()->id);
            $u->current_period_id = optional(DB::table('periods')
                    ->select('periods.*')
                    ->join('groups', 'groups.period_id', '=', 'periods.id')
                    ->join('group_user', 'group_user.group_id', '=', 'groups.id')
                    ->where('group_user.user_id', $u->id)
                    ->where('groups.organization_id', $u->current_organization_id)
                    ->get()->first())->id;
            $u->save();
        }

        //setStatistics
        LogController::setStatistics();
        LogController::set('ssoLogin');
        LogController::set('activeOrg', auth()->user()->current_organization_id);
    }
}
