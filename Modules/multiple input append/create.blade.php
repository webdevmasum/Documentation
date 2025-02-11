@extends('backend.app')

@section('title', 'Meal Plan Create')

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
                    <h3>Create Meal Plan</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('plan.index') }}"><i data-feather="skip-back"></i></a>
                        </li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item active">Create Meal Plan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <form method="POST" action="{{ route('plan.store') }}">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Create Meal Plan</h4>
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
                                    <option value="">Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Plan Title -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="title" class="form-label f-w-500">Plan Title:</label>
                                <input type="text" name="title" id="title" placeholder="Enter Plan Title"
                                    class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
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
                                    class="form-control ck-editor">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <!-- Meal Plans Section -->
                        <div id="meal_plans" class="col-12">
                            <h5>Meal Plans</h5>
                            <div class="meal-plan">
                                <label>Meal Type:</label>
                                <select name="meal_plans[0][meal_type]" class="form-control">
                                    <option value="breakfast">Breakfast</option>
                                    <option value="lunch">Lunch</option>
                                    <option value="dinner">Dinner</option>
                                    <option value="snacks">Snacks</option>
                                    <option value="another">Another</option>
                                </select>

                                <!-- meal_details -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="meal_details" class="form-label">Meal Details:</label>
                                        {{-- <textarea id="body1" name="meal_plans[0][meal_details]" class="form-control ck-editor">{{ old('meal_details') }}</textarea> --}}
                                        <textarea id="meal_details_0" placeholder="Write here about meal details..." name="meal_plans[0][meal_details]"
                                            class="form-control"></textarea>

                                    </div>
                                </div>

                                <button type="button" class="btn btn-dark text-danger m-2"
                                    onclick="removeMealPlan(this)">Remove</button>
                            </div>
                            <button type="button" class="btn btn-dark text-info" onclick="addMealPlan()">Add More
                                Meals</button>
                        </div>

                        <!-- Workout Videos Section -->
                        <div id="workout_videos" class="col-12 mt-5">
                            <h5>Workout Videos</h5>
                            <div class="workout-video">

                                <!-- Date Selection -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="date" class="form-label f-w-500">Date:</label>
                                        <input type="date" name="workout_videos[0][date]" id="date"
                                            class="form-control" onchange="updateDayName()">
                                        <span id="day_name"></span>
                                    </div>
                                </div>

                                <!-- workout video's title Selection -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label>Title:</label>
                                        <input type="text" name="workout_videos[0][title]" placeholder="Title"
                                            class="form-control">
                                        @error('workout_videos.0.title')
                                            <div style="color: red">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- workout video's sub_title Selection -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label>Video Title:</label>
                                        <input type="text" name="workout_videos[0][sub_title]"
                                            placeholder="Title for Video" class="form-control">
                                        @error('workout_videos.0.sub_title')
                                            <div style="color: red">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- workout video's video Selection -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label>Video Link:</label>
                                        <input type="text" name="workout_videos[0][video_link]"
                                            placeholder="Youtube Video Link" class="form-control">
                                        @error('workout_videos.0.video_link')
                                            <div style="color: red">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <button type="button" class="btn btn-dark text-danger m-2"
                                    onclick="removeWorkoutVideo(this)">Remove</button>
                            </div>
                            <button type="button" class="btn btn-dark text-info" onclick="addWorkoutVideo()">Add More
                                Videos</button>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary mt-3">Create Plan</button>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
    <script>
        // Update day name based on the selected date
        function updateDayName() {
            const dateInput = document.getElementById('date').value;
            const dayNameElement = document.getElementById('day_name');
            if (dateInput) {
                const date = new Date(dateInput);
                const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                dayNameElement.textContent = "Day: " + dayNames[date.getUTCDay()];
            }
        }

        //!! for mealplan old functions
        /* // Add meal plan input dynamically at the top
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
                    <option value="another">Another</option>
                </select>
                <label>Meal Details:</label>
                <textarea name="meal_plans[${mealPlanCount}][meal_details]" class="form-control"></textarea>
                <button type="button" class="btn btn-dark text-danger mb-5 mt-2" onclick="removeMealPlan(this)">Remove</button>
            </div>
            `;
                    // Append the new meal plan at the top instead of the bottom
                    mealPlansDiv.insertAdjacentHTML('afterbegin', newMealPlan);
                }

                // Remove meal plan section
                function removeMealPlan(button) {
                    button.parentElement.remove();
                } */




        // Add workout video input dynamically, appends to the top
        function addWorkoutVideo() {
            const workoutVideosDiv = document.getElementById('workout_videos');
            const workoutVideoCount = workoutVideosDiv.querySelectorAll('.workout-video').length;
            const newWorkoutVideo = `
            <div class="workout-video">

                <label for="date" class="form-label f-w-500">Date:</label>
                    <input type="date" name="workout_videos[${workoutVideoCount}][date]" id="date" class="form-control"
                        onchange="updateDayName()">
                        <span id="day_name"></span>

                <label>Title:</label>
                <input type="text" name="workout_videos[${workoutVideoCount}][title]" placeholder="Title" class="form-control">
                <div style="color: red" class="error-message" id="error-title-${workoutVideoCount}"></div>

                <label>Video Title:</label>
                <input type="text" name="workout_videos[${workoutVideoCount}][sub_title]" placeholder="Title for Video" class="form-control">
                <div style="color: red" class="error-message" placeholder="Title for Video" id="error-sub_title-${workoutVideoCount}"></div>

                <label>Video Link:</label>
                <input type="text" name="workout_videos[${workoutVideoCount}][video_link]" placeholder="Youtube Video Link" class="form-control">
                <div style="color: red" class="error-message" id="error-link-${workoutVideoCount}"></div>
                <button type="button" class="btn btn-dark text-danger mb-5 mt-2" onclick="removeWorkoutVideo(this)">Remove</button>
            </div>
        `;
            workoutVideosDiv.insertAdjacentHTML('afterbegin', newWorkoutVideo);
        }

        // Remove workout video section
        function removeWorkoutVideo(button) {
            const workoutVideoDiv = button.parentElement;
            workoutVideoDiv.remove();
        }
    </script>

    //!! for ck editor
    <script>
        ClassicEditor
            .create(document.querySelector('#body'))
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#body1'))
            .catch(error => {
                console.error(error);
            });
    </script>

    //!! for ck editor and meal plan
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize CKEditor for the first (default) textarea
            ClassicEditor.create(document.querySelector('#meal_details_0'))
                .catch(error => {
                    console.error(error);
                });
        });

        function addMealPlan() {
            const mealPlansDiv = document.getElementById('meal_plans');
            const mealPlanCount = mealPlansDiv.querySelectorAll('.meal-plan').length + 1;
            const newMealPlanId = `meal_details_${mealPlanCount}`; // Unique ID for CKEditor

            const newMealPlan = `
            <div class="meal-plan mt-3 p-3 border rounded">
                <label>Meal Type:</label>
                <select name="meal_plans[${mealPlanCount}][meal_type]" class="form-control">
                    <option value="breakfast">Breakfast</option>
                    <option value="lunch">Lunch</option>
                    <option value="dinner">Dinner</option>
                    <option value="snacks">Snacks</option>
                    <option value="another">Another</option>
                </select>

                <label>Meal Details:</label>
                <textarea id="${newMealPlanId}" placeholder="Write here about meal details..." name="meal_plans[${mealPlanCount}][meal_details]" class="form-control"></textarea>

                <button type="button" class="btn btn-dark mt-2 mb-2 text-danger" onclick="removeMealPlan(this)">Remove</button>
            </div>
        `;

            // Append the new meal plan at the bottom
            mealPlansDiv.insertAdjacentHTML('beforeend', newMealPlan);

            // Initialize CKEditor for the newly added textarea
            ClassicEditor.create(document.querySelector(`#${newMealPlanId}`))
                .catch(error => {
                    console.error(error);
                });
        }

        function removeMealPlan(button) {
            button.parentElement.remove();
        }
    </script>
@endpush
