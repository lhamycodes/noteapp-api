<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Notes;
use Illuminate\Http\Request;
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
        $notes = Notes::where(['user_id' => auth()->user()->id])->orderByDesc('id')->get();

        return response()->json(Json::response(200, ['notes' => $notes], "All Notes"), 200);
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
            $note = Notes::create(array_merge(
                [
                    'uuid' => Str::orderedUuid(),
                    'user_id' => auth()->user()->id,
                ],
                $request->only('title', 'description')
            ));

            if ($note) {
                return response()->json(Json::response(200, ['note' => $note], "Note created Succcessfully"), 200);
            } else {
                return response()->json(Json::response(400, [], "Couldn't create note"), 400);
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
    { }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Notes  $notes
     * @return \Illuminate\Http\Response
     */
    public function update($uuid, Request $request)
    {
        $note = Notes::where('uuid', $uuid)->first();
        if ($note) {
            if ($note->user_id == auth()->user()->id) {
                $note->update($request->only('title', 'description'));
                return response()->json(Json::response(200, ['note' => $note], "Note updated Succcessfully"), 200);
            } else {
                return response()->json(Json::response(403, ['note' => $note], "Unauthorized access to note"), 403);
            }
        } else {
            return response()->json(Json::response(400, [], "Couldn't find note"), 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Notes  $notes
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        $note = Notes::where('uuid', $uuid)->first();
        if ($note) {
            if ($note->user_id == auth()->user()->id) {
                $note->delete();
                return response()->json(Json::response(200, ['note' => $note], "Note deleted Succcessfully"), 200);
            } else {
                return response()->json(Json::response(403, ['note' => $note], "Unauthorized access to note"), 403);
            }
        } else {
            return response()->json(Json::response(400, [], "Couldn't find note"), 400);
        }
    }
}
