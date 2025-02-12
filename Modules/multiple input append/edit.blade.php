@extends('backend.app')

@section('title', 'Edit Plan')

@push('styles')
    <style>
        .ck-editor__editable[role="textbox"] {
            min-height: 150px;
        }

        .form-control {
            border-radius: 0.375rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-options i {
            font-size: 1.2rem;
        }

        /* Button Styles */
        .btn-success {
            background-color: #6c757d;
            /* Lighter dark color */
            border-color: #6c757d;
        }

        .btn-danger {
            background-color: #6e9bc7;
            /* Gray color */
            border-color: #adb5bd;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Plan Edit Page</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('plan.index') }}"><i data-feather="skip-back"></i></a>
                        </li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item active">Edit Plan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <form method="POST" action="{{ route('plan.update', $plan->id) }}">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Edit Plan</h4>
                    <div class="card-options">
                        <a class="card-options-collapse" href="#" data-bs-toggle="card-collapse"><i
                                class="fe fe-chevron-up"></i></a>
                        <a class="card-options-remove" href="#" data-bs-toggle="card-remove"><i
                                class="fe fe-x"></i></a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- User Selection -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="user_id" class="form-label f-w-500">Select User:</label>
                                <select name="user_id" id="user_id" class="form-control" required>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ $user->id == $plan->user_id ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Plan Title -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="title" class="form-label f-w-500">Plan Title:</label>
                                <input type="text" name="title" id="title" placeholder="Enter Plan Title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $plan->title) }}">
                                @error('title')
                                    <div style="color: red">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description:</label>
                                <textarea id="body" name="description" placeholder="Write here about plan description..."
                                    class="form-control ck-editor">{{ old('description', $plan->description) }}</textarea>
                            </div>
                        </div>

                        <!-- Meal Plans Section -->
                        <div id="meal_plans" class="col-12">
                            <h5>Meal Plans</h5>
                            @foreach ($plan->meals as $key => $meal)
                                <div class="meal-plan">
                                    <label>Meal Type:</label>
                                    <select name="meal_plans[{{ $key }}][meal_type]" class="form-control">
                                        <option value="breakfast" {{ $meal->meal_type == 'breakfast' ? 'selected' : '' }}>
                                            Breakfast</option>
                                        <option value="lunch" {{ $meal->meal_type == 'lunch' ? 'selected' : '' }}>Lunch
                                        </option>
                                        <option value="dinner" {{ $meal->meal_type == 'dinner' ? 'selected' : '' }}>Dinner
                                        </option>
                                        <option value="snacks" {{ $meal->meal_type == 'snacks' ? 'selected' : '' }}>Snacks
                                        </option>
                                    </select>

                                    <label>Meal Details:</label>
                                    <textarea name="meal_plans[{{ $key }}][meal_details]" class="form-control ck-editor">{{ old('meal_plans.' . $key . '.meal_details', $meal->meal_details) }}</textarea>

                                    <button type="button" class="btn btn-dark text-danger m-2"
                                        onclick="removeMealPlan(this)">Remove</button>
                                </div>
                            @endforeach
                            <button type="button" class="btn btn-dark text-info" onclick="addMealPlan()">Add More
                                Meals</button>
                        </div>

                        <!-- Workout Videos Section -->
                        <div id="workout_videos" class="col-12 mt-5">
                            <h5>Workout Videos</h5>
                            @foreach ($plan->workoutVideos as $key => $video)
                                <div class="workout-video">
                                    <label for="date" class="form-label f-w-500">Date:</label>
                                    <input type="date" name="workout_videos[{{ $key }}][date]"
                                        class="form-control" value="{{ $video->date }}" onchange="updateDayName()">
                                    <span id="day_name"></span>

                                    <label>Title:</label>
                                    <input type="text" name="workout_videos[{{ $key }}][title]"
                                        class="form-control"
                                        value="{{ old('workout_videos.' . $key . '.title', $video->title) }}">

                                    <label>Video Title:</label>
                                    <input type="text" name="workout_videos[{{ $key }}][sub_title]"
                                        class="form-control"
                                        value="{{ old('workout_videos.' . $key . '.sub_title', $video->sub_title) }}">

                                    <label>Video Link:</label>
                                    <input type="text" name="workout_videos[{{ $key }}][video_link]"
                                        class="form-control"
                                        value="{{ old('workout_videos.' . $key . '.video_link', $video->video_link) }}">

                                    <button type="button" class="btn btn-dark text-danger m-2"
                                        onclick="removeWorkoutVideo(this)">Remove</button>
                                </div>
                            @endforeach
                            <button type="button" class="btn btn-dark text-info" onclick="addWorkoutVideo()">Add More
                                Videos</button>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary mt-3">Update Plan</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <script>
        // Initialize CKEditor on all textareas with class 'ck-editor'
        function initCKEditor() {
            const editors = document.querySelectorAll('.ck-editor');
            editors.forEach(editor => {
                ClassicEditor.create(editor).catch(error => {
                    console.error(error);
                });
            });
        }

        // Call the function to initialize CKEditor on page load
        document.addEventListener('DOMContentLoaded', function() {
            initCKEditor();
        });

        function updateDayName() {
            const dateInput = document.querySelector('input[type="date"]').value;
            const dayNameElement = document.getElementById('day_name');
            if (dateInput) {
                const date = new Date(dateInput);
                const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                dayNameElement.textContent = "Day: " + dayNames[date.getUTCDay()];
            }
        }

        function removeMealPlan(element) {
            element.parentElement.remove();
        }

        function removeWorkoutVideo(element) {
            element.parentElement.remove();
        }


        function addMealPlan() {
            const mealPlansDiv = document.getElementById('meal_plans');
            const mealPlanCount = mealPlansDiv.querySelectorAll('.meal-plan').length;
            const newMealPlan = `
                    <div class="meal-plan">
                        <label>Meal Type:</label>
                        <select name="meal_plans[${mealPlanCount}][meal_type]" class="form-control">
                            <option value="breakfast">Breakfast</option>
                            <option value="lunch">Lunch</option>
                            <option value="dinner">Dinner</option>
                            <option value="snacks">Snacks</option>
                        </select>

                        <label>Meal Details:</label>
                        <textarea id="meal_details_${mealPlanCount}" name="meal_plans[${mealPlanCount}][meal_details]" class="form-control"></textarea>

                        <button type="button" class="btn btn-dark text-danger m-2" onclick="removeMealPlan(this)">Remove</button>
                    </div>
                `;
            mealPlansDiv.insertAdjacentHTML('beforeend', newMealPlan);

            // Newly Added Textarea te CKEditor Initialize Kora
            ClassicEditor.create(document.getElementById(`meal_details_${mealPlanCount}`)).catch(error => {
                console.error(error);
            });
        }



        function addWorkoutVideo() {
            const workoutVideosDiv = document.getElementById('workout_videos');
            const workoutVideoCount = workoutVideosDiv.querySelectorAll('.workout-video').length;
            const newWorkoutVideo = `
                <div class="workout-video">
                    <label for="date" class="form-label f-w-500">Date:</label>
                    <input type="date" name="workout_videos[${workoutVideoCount}][date]" class="form-control" onchange="updateDayName()">
                    <span id="day_name"></span>

                    <label>Title:</label>
                    <input type="text" name="workout_videos[${workoutVideoCount}][title]" class="form-control">

                    <label>Video Title:</label>
                    <input type="text" name="workout_videos[${workoutVideoCount}][sub_title]" class="form-control">

                    <label>Video Link:</label>
                    <input type="text" name="workout_videos[${workoutVideoCount}][video_link]" class="form-control">

                    <button type="button" class="btn btn-dark text-danger m-2" onclick="removeWorkoutVideo(this)">Remove</button>
                </div>
            `;
            workoutVideosDiv.insertAdjacentHTML('beforeend', newWorkoutVideo);
        }
    </script>
@endpush
