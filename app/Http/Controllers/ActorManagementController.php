<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Actor;
use \App\Models\Domain;
use App\Http\Requests\ActorRequest;

class ActorManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $actors = Actor::with('domains')->get();
        return view ('pages.ActorManagement.index', compact('actors')) ;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $domains = Domain::all();
        return view('pages.ActorManagement.create', compact('domains'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ActorRequest $request)
    {
        $imageName = time().'.'.$request->profilePicture->extension();

        $request->profilePicture->move(public_path('actorPictures'), $imageName);
        
        $actor = new Actor([
            "fullName" => $request->get('fullName'),
            "birthDate" => $request->get('birthDate'),
            "birthPlace" => $request->get('birthPlace'),
            "biography" => $request->get('biography'),
            "nationality" => $request->get('nationality'),
            "specialties" => $request->get('specialties'),
            "profilePicture" => $imageName,
            "email" => $request->get('email'),
            "phoneNumber" => $request->get('phoneNumber'),
            "socialConnections" => $request->get('socialConnections'),
            "discography" => $request->get('discography'),
            "availability" => $request->get('availability'),
        ]);

        $actor->save();

        $actor->domains()->attach($request->input('domains'));

        return redirect()->route('actor-management.index')->with('success', 'Actor is added successfully !');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = $request->query('id');
        $actor = Actor::with('domains')->find($id);

        if (!$actor) {
            return redirect()->route('actor-management.index')->with('error', 'Actor not found !');
        }
        return view('pages.ActorManagement.actor', compact('actor'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $id = $request->query('id');
        $actor = Actor::with('domains')->find($id);
        $domains = Domain::all();

        return view('pages.ActorManagement.edit', compact('actor'), compact('domains'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->query('id');
        if ($id) {
            $actor = Actor::find($id);

            $request->validate([
                'fullName' => 'required|max:30',
                'email' => 'required',
                'phoneNumber' => 'required|numeric',
                'profilePicture' => 'required',
                'birthDate' => 'required|date|before:today',
            ], [
                'birthDate.required' => "BirthDate is required !"
            ]);

            if (!is_string($request->profilePicture)) {
                $imageName = time().'.'.$request->profilePicture->extension();
                $request->profilePicture->move(public_path('actorPictures'), $imageName);
                $actor->profilePicture = $imageName;
            }
            
            $actor->fullName = $request->fullName;
            $actor->birthDate = $request->birthDate;
            $actor->birthPlace = $request->birthPlace;
            $actor->biography = $request->biography;
            $actor->nationality = $request->nationality;
            $actor->email = $request->email;
            $actor->phoneNumber = $request->phoneNumber;
            $actor->socialConnections = $request->socialConnections;
            $actor->discography = $request->discography;
            $actor->availability = $request->availability;
            $actor->save();

            $actor->domains()->sync($request->input('domains'));

            return redirect()->route('actor-management.index')->with('success', 'Actor is updated successfully !');
            
        } else {
            return redirect()->route('actor-management.index')->with('error', 'Actor not found !');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $actor = Actor::find($id);
        if (!$actor) {
            return redirect()->route('actor-management.index')->with('error', 'Actor not found !');
        }
        $actor->delete();
        return redirect()->route('actor-management.index')->with('success', 'Actor is deleted with success !');
    }
}
