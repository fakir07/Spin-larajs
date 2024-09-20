<?php

namespace App\Http\Controllers;

use App\Models\winner;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use Spatie\SimpleExcel\SimpleExcelWriter;
use Spatie\SimpleExcel\SimpleExcelReader;

class WinnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        $users = Winner::all();

        $data = [];

        foreach ($users as $index => $user) {
            $data[] = [
                "label" => $user->name, 
                "value" => $index + 1,   
                "question" => "HAPPY EID " . strtoupper($user->name) . "!!!"
            ];
        }

        return view('welcome', compact('data'));
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
        $winner = new  winner();
        $winner->name = $request->input('name');
        $winner->save();
        return Redirect::back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\winner  $winner
     * @return \Illuminate\Http\Response
     */
    public function show(winner $winner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\winner  $winner
     * @return \Illuminate\Http\Response
     */
    public function edit(winner $winner)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\winner  $winner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, winner $winner)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\winner  $winner
     * @return \Illuminate\Http\Response
     */
    public function destroy(winner $winner)
    {
        //
    }

    public function getuser()
    {

        $user = winner::all();
        return response()->json($user);
    }

    public function import(Request $request)
{
    $this->validate($request, [
        'fichier' => 'bail|required|file|mimes:xlsx'
    ]);
    // dd('aziz1');

    $fichierPath = $request->fichier->move(public_path('uploads'), $request->fichier->hashName());
    // dd($fichierPath);

    try {
        // dd('aziz3');

        $reader = SimpleExcelReader::create($fichierPath);
        // dd($reader);

        // Get all rows without filtering
        $allRows = $reader->getRows()->toArray();
        // dd($allRows);


        // Insert all rows into the database
        DB::transaction(function () use ($allRows) {
            winner::insert($allRows);
        });


        $reader->close();
        unlink($fichierPath);

        return back()->with('msg', 'Importation réussie !');

    } catch (\Exception $e) {
        \Log::error($e);
        return back()->withErrors(['msg' => 'Une erreur est survenue lors de l\'importation.']);
    }
}

    
    
    
}
