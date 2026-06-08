<?php

namespace App\Http\Controllers;

use App\Models\Occasion;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('User - Occasions', 'Endpoint untuk mengambil data acara user')]
class OccasionController extends Controller
{
    public function index()
    {
        $occasions = Occasion::all();

        return response()->json([
            'status' => 'success',
            'data' => $occasions
        ]);
    }
}
