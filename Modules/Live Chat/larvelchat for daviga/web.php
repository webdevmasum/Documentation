
***********
Step-1: Work Flow >>>>
***********

    //!! Front/admin dashboard
    1. sidebar
    2. route
    3. Controller
    4. Make View
    * * * * * * * * * *
    database, model
    * * * * * * * * * *


    //!! API end
    1. Install Reverb 
    -->> php artisan install:broadcasting 

    2. register channel to bootstrap/app.php
    3. Make DataTable
    4. Make Controller
    5. Make a Chennel into channel.php

    //!! maybe automatically created 
    php artisan make:channel OrderChannel (channel name)
    php artisan channel:list

    6. Make Event
    -->> php artisan make:event EventName

    7. Make Route
    8. Make View 


***********
Step-2: Sidebar
***********

    <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a
            class="sidebar-link sidebar-title {{ Request::routeIs('chat.*') ? 'active open' : ' ' }}"
            href="javascript:void(0)"><i data-feather="message-circle"></i><span>Chat</span></a>
        <ul class="sidebar-submen
            <li><a class="{{ Request::routeIs('chat.index') ? 'active' : ' ' }}"
                    href="{{ route('chat.index') }}">Chat Lists</a></li>
        </ul>
    </li>

***********
Step-3: Route 
***********

    //! Route for Chat In Admin Dashboard
    //! web/backend.php

    Route::controller(ChatController::class)->prefix('chat')->name('chat.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/search', 'search')->name('search');
        Route::Post('/send/{id}', 'sendMessage')->name('send');
        Route::get('/response/{id}', 'getMessages')->name('response');
    });

   
***********
Step-4: Controller
***********

<?php

namespace App\Http\Controllers\Web\Backend\Chat;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Exception;


class ChatController extends Controller
{


    public function index()
    {
        $users = User::whereHas('senders', function ($query) {
            $query->where('receiver_id', Auth::id());
        })->orWhereHas('receivers', function ($query) {
            $query->where('sender_id', Auth::id());
        })->where('id', '!=', Auth::user()->id)->get();
        return view('backend.layouts.chat.index', compact('users'));
    }

    public function search(Request $request)
    {
        $keyword = $request->get('keyword');
        $users = User::where('id', '!=', Auth::user()->id)->where('name', 'like', '%' . $keyword . '%')->get();
        return view('backend.layouts.chat.index', compact('users'));
    }

    public function sendMessage(Request $request, $receiver_id): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        try {

            $conversation = ChatGroup::where(function ($query) use ($receiver_id) {
                $query->where('user_one_id', $receiver_id)->where('user_two_id', Auth::id());
            })->orWhere(function ($query) use ($receiver_id) {
                $query->where('user_one_id', Auth::id())->where('user_two_id', $receiver_id);
            })->first();

            if (!$conversation) {
                $conversation = ChatGroup::create([
                    'user_one_id' => Auth::id(),
                    'user_two_id' => $receiver_id,
                ]);
            }

            $message = ChatMessage::create([
                'sender_id'   => Auth::id(),
                'receiver_id' => $receiver_id,
                'text'        => $request->message,
                'conversation_id' => $conversation->id
            ]);

            //* Load the sender's information
            $message->load(['sender:id,name,avatar', 'receiver:id,name,avatar']);

            broadcast(new MessageSent($message))->toOthers();

            return response()->json([
                'success'       => true,
                'message'       => 'Message sent successfully',
                'receiver'      => User::find($receiver_id),
                'sender'        => User::find(Auth::id()),
                'data'          => $message,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending message',
            ], 422);
        }
    }

    public function getMessages($receiver_id): JsonResponse
    {
        $messages = ChatMessage::query()
            ->where(function ($query) use ($receiver_id) {
                $query->where('sender_id', Auth::id())
                    ->where('receiver_id', $receiver_id);
            })
            ->orWhere(function ($query) use ($receiver_id) {
                $query->where('sender_id', $receiver_id)
                    ->where('receiver_id', Auth::id());
            })
            ->with([
                'sender:id,name,avatar',
                'receiver:id,name,avatar',
            ])
            ->orderBy('id', 'asc')
            ->limit(100)
            ->get();

        $conversation = ChatGroup::where(function ($query) use ($receiver_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', Auth::id());
        })->orWhere(function ($query) use ($receiver_id) {
            $query->where('user_one_id', Auth::id())->where('user_two_id', $receiver_id);
        })->first();

        if (!$conversation) {
            $conversation = ChatGroup::create([
                'user_one_id' => Auth::id(),
                'user_two_id' => $receiver_id,
            ]);
        }

        return response()->json([
            'success'       => true,
            'message'       => 'Messages retrieved successfully',
            'receiver'      => User::find($receiver_id),
            'sender'        => User::find(Auth::id()),
            'group'         => $conversation,
            'data'          => $messages
        ]);
    }
}
?>


***********
Step-5: Make View
***********

@extends('backend.app')

@section('title', 'Dashboard Chat App')

@section('content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Chat App</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i data-feather="home"></i></a></li>
                        <li class="breadcrumb-item">Chat</li>
                        <li class="breadcrumb-item active">Chat App</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- Container-fluid starts-->
    <div class="container-fluid">
        <div class="row">
            <div class="col call-chat-sidebar">
                <div class="card">
                    <div class="card-body chat-body">
                        <div class="chat-box">
                            <!-- Chat left side Start-->
                            <div class="chat-left-aside">
                                <div class="d-flex"><img class="rounded-circle user-image"
                                        src="{{ asset('/') }}backend/images/user/12.png" alt="">
                                    <div class="flex-grow-1">
                                        <div class="about">
                                            <div class="name f-w-600"> <a>{{ Auth::user()->name?? "" }}</a></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="people-list" id="people-list">
                                    <div class="search">
                                        <hr>
                                    </div>
                                    <ul class="list custom-scrollbar" id="conversations_list">
                                        @foreach ($users as $user)
                                            <li class="clearfix">
                                                <a class="d-flex align-items-center" href="javascript:void(0)"
                                                    onclick="getMessage({{ $user->id }})">
                                                    <img class="rounded-circle user-image"
                                                        src="{{ asset($user->avatar ? $user->avatar : 'default/profile.jpg') }}"
                                                        alt="" />
                                                    <div ></div>
                                                    <div class="flex-grow-1">
                                                        <div class="about">
                                                            <div class="name">{{ $user->name }}</div>
                                                            <div class="status">{{ $user->role }}</div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <!-- Chat left side Ends-->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col call-chat-body">

                <div class="card">
                    <div class="card-body p-0">
                        <div class="row chat-box">
                            <!-- Chat right side start-->
                            <div class="col chat-right-aside">
                                <!-- chat start-->
                                <div class="chat">
                                    <!-- chat-header start-->
                                    <div class="d-flex chat-header clearfix align-items-start"><img
                                            class="rounded-circle rounded-circle img-fluid w-25" id="ReceiverImage"
                                            src="{{ asset('default/profile.jpg') }}" alt="">
                                        <div class="flex-grow-1">
                                            <div class="about">
                                                <div class="name"><a id="ReceiverName"></a></div>
                                                <div class="name"><a id="ReceiverId"></a></div>
                                                <div class="name" hidden><a id="GroupId"></a></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- chat-header end-->
                                    <div class="chat-history chat-msg-box custom-scrollbar" id="ChatContent">
                                        <!-- Messages will be dynamically loaded here -->
                                    </div>
                                    <!-- end chat-history-->
                                    <div class="chat-message clearfix">
                                        <div class="row">
                                            <div class="col-xl-12 d-flex">
                                                <div class="input-group text-box">
                                                    <input class="form-control input-txt-bx" id="message-to-send"
                                                        type="text" name="message" placeholder="Type a message......">
                                                    <button class="btn btn-primary input-group-text" type="button"
                                                        id="sendMessageButton"
                                                        onclick="sendMessage($('#ReceiverId').val())">SEND</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end chat-message-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Container-fluid Ends-->
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
    <script>
        function getMessage(receiver_id) {
            $.ajax({
                url: `{{ route('chat.response', ':id') }}`.replace(':id', receiver_id),
                type: "GET",
                success: function(response) {
                    $('#ChatContent').html('');
                    $('#ReceiverId').val(receiver_id);
                    $('#ReceiverName').html(response.receiver.name);
                    $('#ReceiverRole').html(response.receiver.role);
                    $('#GroupId').val(response.group.id);
                    $('#sendMessageButton').show();

                    let receiverAvatar = response.receiver.avatar ?
                        `{{ asset('${response.receiver.avatar}') }}` : "{{ asset('default/profile.jpg') }}";
                    let senderAvatar = response.sender.avatar ? `{{ asset('${response.sender.avatar}') }}` :
                        "{{ asset('default/profile.jpg') }}";

                    $('#ReceiverImage').html(`<img alt="avatar" src="${receiverAvatar}">`);

                    let senderClass = 'chat-right';
                    let receiverClass = 'chat-left';

                    response.data.forEach(data => {
                        const formattedDate = dayjs(data.created_at).format('DD-MM-YY h:mm A');
                        if (data.sender_id === {{ auth()->user()->id }}) {
                            $('#ChatContent').append(`
                                <div class="${senderClass}">
                                    <div class="main-img-user "><img alt="avatar" src="${senderAvatar}"></div>
                                    <div class="media-body">
                                        <div class="main-msg-wrapper">${data.text}</div>
                                        <div><span>${formattedDate}</span></div>
                                    </div>
                                </div>
                            `);
                        } else {
                            $('#ChatContent').append(`
                                <div class="${receiverClass}">
                                    <div class="main-img-user "><img alt="avatar" src="${receiverAvatar}"></div>
                                    <div class="media-body">
                                        <div class="main-msg-wrapper">${data.text}</div>
                                        <div><span>${formattedDate}</span></div>
                                    </div>
                                </div>
                            `);
                        }
                    });

                    $('#ChatContent').scrollTop($('#ChatContent')[0].scrollHeight);
                },
                error: function(xhr, status, error) {
                    console.log('Error sending message:', error);
                }
            });
        }

        function sendMessage(receiver_id) {
            // console.log(receiver_id);
            let message = $('#message-to-send').val();
            $.ajax({
                url: `{{ route('chat.send', ':id') }}`.replace(':id', receiver_id),
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: {
                    message: message
                },
                success: function(response) {
                    getMessage(receiver_id);
                    $('#message-to-send').val('');
                },
                error: function(xhr, status, error) {
                    console.log('Error sending message:', error);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const checkGroupId = setInterval(() => {
                let group_id = document.getElementById('GroupId').value;
                if (group_id) {
                    clearInterval(checkGroupId);
                    Echo.private(`chat.${group_id}`).listen('MessageSent', function(e) {
                        getMessage(document.getElementById('ReceiverId').value);
                    });
                }
            }, 1000);
        });


        /// here toggle buton on send mesage button
        $(document).ready(function() {
            $('#sendMessageButton').hide();
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Set image size for chat */
        .chat-left .main-img-user img,
        .chat-right .main-img-user img {
            width: 35px;
            /* Adjust this to fit your requirement */
            height: 35px;
            border-radius: 50%;
        }

        /* Align chat bubbles for sender (admin) and receiver (user) */
        .chat-left {
            display: flex;
            margin-bottom: 10px;
        }

        .chat-right {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        /* Align message content */
        .chat-left .media-body,
        .chat-right .media-body {
            max-width: 70%;
            /* Adjust the width of message box */
            padding: 8px;
            border-radius: 10px;
            /* background-color: #b84a4a; Receiver message background color */
        }

        .chat-right .media-body {
            /* background-color: #0df715; Admin message background color */
            color: rgb(196, 242, 253);
        }

        /* Adjust time label for messages */
        .chat-left .media-body span,
        .chat-right .media-body span {
            font-size: 0.8rem;
            color: #ffffff;
        }

        /* Adjust margin for message container */
        .chat-left .media-body .main-msg-wrapper,
        .chat-right .media-body .main-msg-wrapper {
            padding: 10px;
            background-color: #45D1BC;
            /* color: #308ac5 */
            border-radius: 5px;
            box-shadow: 0 1px 4px rgba(8, 8, 8, 0.1);
        }
    </style>
@endpush






