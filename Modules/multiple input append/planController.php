<?php

namespace App\Http\Controllers\Web\Backend\Plan;

use App\Http\Controllers\Controller;
use App\Models\MealPlan;
use App\Models\Plan;
use App\Models\User;
use App\Models\WorkoutVideo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $data = Plan::with('user')->latest();

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()

                // User Name Column
                ->addColumn('user', function ($data) {
                    return $data->user ? $data->user->name : 'N/A'; // User er name show korbo
                })

                ->addColumn('status', function ($data) {
                    $status = '<div class="switch-sm icon-state">';
                    $status .= '<label class="switch">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" name="status"';

                    if ($data->status == "active") {
                        $status .= ' checked';
                    }

                    $status .= '>';
                    $status .= '<span class="switch-state"></span>';
                    $status .= '</label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                            <a href="' . route('plan.edit', $data->id) . '" type="button" class="action edit text-success" title="Edit">
                            <i class="icon-pencil-alt"></i>
                            </a>&nbsp;
                            <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="action delete text-danger" title="Delete">
                            <i class="icon-trash"></i>
                          </a>
                        </div>';
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('backend.layouts.plan.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('backend.layouts.plan.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meal_plans' => 'nullable|array',
            'meal_plans.*.meal_type' => 'required|string',
            'meal_plans.*.meal_details' => 'nullable|string',
            'workout_videos' => 'nullable|array',
            'workout_videos.*.date' => 'nullable|date',
            'workout_videos.*.title' => 'nullable|string|max:255',
            'workout_videos.*.video_link' => 'nullable|string|max:255',
        ]);


        // dd($request);

        DB::beginTransaction();
        try {
            // Create Plan
            $plan = Plan::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'description' => strip_tags($request->description),
            ]);


            // dd($request->has('meal_plans'));
            // Insert Meal Plans
            if ($request->has('meal_plans')) {
                foreach ($request->meal_plans as $meal) {
                    MealPlan::create([
                        'plan_id' => $plan->id,
                        'meal_type' => $meal['meal_type'],
                        // 'meal_details' => $meal['meal_details'] ?? null,
                        'meal_details' => isset($meal['meal_details']) ? strip_tags($meal['meal_details']) : null,
                    ]);
                }
            }

            if ($request->has('workout_videos')) {
                foreach ($request->workout_videos as $video) {
                    // Ensure 'date' key exists before using it
                    if (!isset($video['date'])) {
                        dd('Missing date key in workout_videos', $video);
                    }

                    WorkoutVideo::create([
                        'plan_id' => $plan->id,
                        'date' => $video['date'],
                        'title' => $video['title'] ?? null,
                        'sub_title' => $video['sub_title'] ?? null,
                        'video_link' => $video['video_link'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('plan.index')->with('notify-success', 'Plan created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            // dd($e->getMessage());
            return redirect()->back()->with('notify-error', 'Something went wrong! ' . $e->getMessage());
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $plan = Plan::with('meals')->findOrFail($id);
        $users = User::all();
        return view('backend.layouts.plan.edit', compact('plan', 'users'));
    }


    public function update(Request $request, $id)
    {
        // dd($request->all());
        /* $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meal_plans' => 'nullable|array',
            'meal_plans.*.id' => 'nullable|exists:meal_plans,id',
            'meal_plans.*.meal_type' => 'required|string',
            'meal_plans.*.meal_details' => 'nullable|string',
            'workout_videos' => 'nullable|array',
            'workout_videos.*.id' => 'nullable|exists:workout_videos,id',
            'workout_videos.*.date' => 'nullable|date',
            'workout_videos.*.title' => 'nullable|string|max:255',
            'workout_videos.*.video_link' => 'nullable|string|max:255',
        ]); */


        DB::beginTransaction();
        // try {
        $plan = Plan::findOrFail($id);
        $plan->update([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'description' => strip_tags($request->description),
        ]);

        // **Meal Plans Update**
        $existingMealPlanIds = $plan->meals()->pluck('id')->toArray();
        $newMealPlanIds = [];

        $meals = $request->meal_plans ?? []; // Null holeo empty array
        foreach ($meals as $meal) {
            if (!empty($meal['id'])) {
                $mealPlan = MealPlan::find($meal['id']);
                if ($mealPlan) {
                    $mealPlan->update([
                        'meal_type' => $meal['meal_type'],
                        'meal_details' => isset($meal['meal_details']) ? strip_tags($meal['meal_details']) : null,
                    ]);
                    $newMealPlanIds[] = $meal['id'];
                }
            } else {
                $newMeal = MealPlan::create([
                    'plan_id' => $plan->id,
                    'meal_type' => $meal['meal_type'],
                    'meal_details' => isset($meal['meal_details']) ? strip_tags($meal['meal_details']) : null,
                ]);
                $newMealPlanIds[] = $newMeal->id;
            }
        }

        MealPlan::whereIn('id', array_diff($existingMealPlanIds, $newMealPlanIds))->delete();

        // **Workout Videos Update**
        $existingWorkoutIds = $plan->workoutVideos()->pluck('id')->toArray();
        $newWorkoutIds = [];

        $workoutVideos = $request->workout_videos ?? [];
        foreach ($workoutVideos as $video) {
            if (!empty($video['id'])) {
                $workoutVideo = WorkoutVideo::find($video['id']);
                if ($workoutVideo) {
                    $workoutVideo->update([
                        'date' => $video['date'],
                        'title' => $video['title'] ?? null,
                        'sub_title' => $video['sub_title'] ?? null,
                        'video_link' => $video['video_link'],
                    ]);
                    $newWorkoutIds[] = $video['id'];
                }
            } else {
                $newWorkout = WorkoutVideo::create([
                    'plan_id' => $plan->id,
                    'date' => $video['date'],
                    'title' => $video['title'] ?? null,
                    'sub_title' => $video['sub_title'] ?? null,
                    'video_link' => $video['video_link'],
                ]);
                $newWorkoutIds[] = $newWorkout->id;
            }
        }

        WorkoutVideo::whereIn('id', array_diff($existingWorkoutIds, $newWorkoutIds))->delete();

        DB::commit();
        return redirect()->route('plan.index')->with('notify-success', 'Plan updated successfully!');
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return redirect()->back()->with('notify-error', 'Something went wrong! ' . $e->getMessage());
        // }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Plan::findOrFail($id);

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deleted successfully.',
        ]);
    }


    /**
     * Update the status of a blog.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status($id)
    {
        $data = Plan::findOrFail($id);

        if ($data->status == 'active') {
            $data->status = 'inactive';
            $data->save();
            return response()->json([
                'success' => false,
                'message' => 'Unpublished Successfully.',
                'data' => $data,
            ]);
        } else {
            $data->status = 'active';
            $data->save();
            return response()->json([
                'success' => true,
                'message' => 'Published Successfully.',
                'data' => $data,
            ]);
        }
    }
}
