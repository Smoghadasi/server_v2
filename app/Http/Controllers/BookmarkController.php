<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Driver;
use App\Models\Owner;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $bookmarks = Bookmark::when($request->type !== null, function ($query) use ($request) {
            return $query->where('type', 'owner');
        })->paginate(100);
        return view('admin.bookmark.index', compact('bookmarks'));
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
        $bookmark = Bookmark::where('userable_id', $request->user_id)->where('type', $request->type)->first();
        if ($bookmark) {
            $bookmark->delete();
            return back();
        }

        $user = null;

        if ($request->type == 'owner') {
            $user = Owner::find($request->user_id);
        } elseif ($request->type == 'driver') {
            $user = Driver::find($request->user_id);
        }

        if ($user) {
            $bookmark = new Bookmark;
            $bookmark->type = $request->type;
            $user->bookmark()->save($bookmark);

            return back();
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bookmark $bookmark)
    {
        $bookmark->delete();
        return back()->with('success', 'آیتم مورد نظر ذخیره شد');
    }
}
