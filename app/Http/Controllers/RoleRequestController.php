<?php








namespace App\Http\Controllers;

use App\Models\RoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class RoleRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $roleRequests = RoleRequest::where('status', 'pending')->with('user')->get();

            if ($roleRequests->isEmpty()) {
                return response()->json(['status' => 'failed', 'message' => 'There aren\'t any RoleRequests yet.'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $roleRequests], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $roleRequest = RoleRequest::create([
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

            return response()->json(['status' => 'success', 'message' => 'RoleRequest created successfully!'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
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
        try {
            $roleRequest = RoleRequest::with('user')->findOrFail($id);

            return response()->json(['status' => 'success', 'data' => $roleRequest], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'fail', 'message' => 'RoleRequest not found!'], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }




    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:accepted,rejected',
            ]);

            DB::transaction(function () use ($request, $id) {
                $roleRequest = RoleRequest::findOrFail($id);

                // Update the RoleRequest status
                $roleRequest->update([
                    'status' => $request->status,
                ]);

                $user = User::findOrFail($roleRequest->user_id);

                // Update the User role if the status is accepted
                if ($request->status === 'accepted') {
                    $user->update(['role' => 'admin']);
                }
            });

            return response()->json(['status' => 'success', 'message' => 'RoleRequest and User updated successfully!'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'fail', 'message' => 'RoleRequest or User not found!'], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }




    // public function update(Request $request, $id)
    // {
    //     try {
    //         $request->validate([
    //             'status' => 'required|string|in:accepted,rejected',
    //         ]);

    //         $roleRequest = RoleRequest::findOrFail($id);

    //         $roleRequest->update([
    //             'status' => $request->status,
    //         ]);

    //         $user = User::findOrFail($roleRequest->user_id);

    //         if ($request->status == 'accepted') {
    //             $user->update(['role' => 'admin']);

    //             return response()->json(['status' => 'success', 'message' => 'RoleRequest updated successfully!'], 200);
    //         }

    //         return response()->json(['status' => 'success', 'message' => 'User role remains the same.'], 200);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json(['status' => 'fail', 'message' => 'RoleRequest or User not found!'], 404);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RoleRequest  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(RoleRequest $role)
    {
        try {
            $role->delete();

            return response()->json(['status' => 'success', 'message' => 'RoleRequest deleted successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}











// namespace App\Http\Controllers;

// use App\Models\RoleRequest;
// use App\Models\User;
// use Illuminate\Http\Request;

// class RoleRequestController extends Controller
// {
//     /**
//      * Display a listing of the resource.
//      *
//      * @return \Illuminate\Http\Response
//      */
//     public function index()
//     {
//         $roleRequest = RoleRequest::where(['status' => 'pending'])->load('user');

//         if (!$roleRequest) {
//             return response()->json(['status' => 'failed', 'message' => 'There aren\'t any RoleRequest yet.'], 404);
//         }

//         return response()->json(['status' => 'success', 'data' => $roleRequest], 200);
//     }



//     /**
//      * Store a newly created resource in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @return \Illuminate\Http\Response
//      */
//     public function store(Request $request)
//     {
//         $user = $request->user();
//         $roleRequest = RoleRequest::create([
//             'user_id' => $user->id,
//             'status' => 'pending',
//         ]);
//         return response()->json(['status' => 'success', 'message' => 'RoleRequest created successfully !'], 201);
//     }


//     public function show($id)
//     {
//         $roleRequest = RoleRequest::findOrFail($id)->load('user');
//         if (!$roleRequest) {
//             return response()->json(['status' => 'fail', 'message' => 'RoleRequest not Found !'], 404);
//         }
//         return response()->json(['status' => 'success', 'data' => $roleRequest], 200);
//     }

//     public function update(Request $request, $id)
//     {
//         $request->validate([
//             'status' => 'required|string|in:accepted,rejected',
//         ]);
//         $roleRequest = RoleRequest::find($id);

//         if (!$roleRequest) {
//             return response()->json(['status' => 'failed', 'message' => 'There aren\'t any RoleRequest yet.'], 404);
//         }

//         $roleRequest->update([
//             'status' => $request->status,
//         ]);

//         $user = User::findOrFail($roleRequest->user_id);


//         if ($request->status == 'accepted') {
//             $user->update([
//                 'role' => 'admin',
//             ]);
//             return response()->json(['status' => 'success', 'message' => 'RoleRequest Updated Successfully !'], 200);
//         }

//         return response()->json(['status' => 'success', 'message' => 'User role still the same.'], 200);
//     }



//     public function destroy(RoleRequest $role)
//     {
//         //
//     }
// }
