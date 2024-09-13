<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreforumRequest;
use App\Http\Requests\UpdateforumRequest;
use App\Models\forum;

class ForumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreforumRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(forum $forum)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(forum $forum)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateforumRequest $request, forum $forum)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(forum $forum)
    {
        //
    }
}
