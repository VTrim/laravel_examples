<?php

namespace App\Http\Controllers;
use App\User\User;
use App\Vkusers;

use Illuminate\Http\Request;

use DB;
use Gate;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(User $infoUser)
    {
        $this->middleware('auth');
        $this->infoUser = $infoUser;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Vkusers $vkusers, $timestamp = null)
    {

        $vkusers->vk_id = 74676576;
        $vkusers->gender = 1;
        $vkusers->age = 20;
        $vkusers->reputation = 5;
        $vkusers->talk = 2;
        $vkusers->online = 457557;

        $vkusers->save();
    	
        return view('home');
    }




}