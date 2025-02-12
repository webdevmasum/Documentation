<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlanController extends Controller
{



    //!! finally done perfectly updated
    public function getPlan(Request $request)
    {
        // Fetch single plan with meals and workout videos based on user_id
        $plan = Plan::with(['meals', 'workoutVideos'])
            ->where('user_id', $request->user_id)
            ->first();

        // Check if plan is found
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'No plan found for this user',
                'data' => [],
            ]);
        }

        // Process workout videos: Group by day_of_week
        $workout_videos_grouped = $plan->workoutVideos->groupBy(function ($video) {
            return Carbon::parse($video->date)->format('l'); // Group by day name (e.g., "Sunday")
        })->map(function ($videos, $dayOfWeek) {
            return [
                'day' => $dayOfWeek,
                'title' => $videos->first()->title,
                'videos' => $videos->map(function ($video) {
                    return [
                        'sub_title' => $video->sub_title,
                        'video_link' => $video->video_link,
                    ];
                })->values()->all(),
            ];
        })->values()->all(); // Convert to array

        // Remove `workoutVideos` relation data
        unset($plan->workoutVideos);

        // Assign the grouped workout videos
        $plan->workout_videos_grouped = $workout_videos_grouped;

        // Hide the `created_at` and `updated_at` fields
        $plan->makeHidden(['created_at', 'updated_at']);

        // Hide the `created_at` and `updated_at` fields from meals
        $plan->meals->map(function ($meal) {
            $meal->makeHidden(['created_at', 'updated_at']);
            return $meal;
        });

        // Return the response as an object instead of an array
        return response()->json([
            'success' => true,
            'message' => 'User plan fetched successfully',
            'data' => $plan,
        ]);
    }
}
