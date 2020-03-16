<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Notes;
use Illuminate\Http\Request;
use App\Http\Resources\Notes as NotesResource;
use App\Transformers\Json;
use Illuminate\Support\Str;

class NotesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notes = Notes::where(['user_id' => auth()->user()->id])->orderByDesc('id')->paginate(5);

        // Return collection of notes as a resource
        return NotesResource::collection($notes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
        ]);

        if (!$validator) {
            return response()->json(Json::response(422, ['error' => $validator->errors()], "Validation failed"), 422);
        } else {
            $createNote = Notes::create(array_merge(
                [
                    'uuid' => Str::orderedUuid(),
                    'user_id' => auth()->user()->id,
                ],
                $request->only('title', 'description')
            ));

            if($createNote){
                
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Notes  $notes
     * @return \Illuminate\Http\Response
     */
    public function show(Notes $notes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Notes  $notes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notes $notes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Notes  $notes
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notes $notes)
    {
        //
    }
}
