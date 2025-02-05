
***********
Step-1: Work Flow >>>>
***********

    //!! It's a Live Chat API Ended
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
Step-2: Project Setup
***********

    1. composer create-project Laravel/Laravel example-app
    2. .env             (database)
    3. composer require Laravel/breeze --dev
    4. php artisan breeze:install
    5. php artisan migrate
    6. npm install
    7. npm run dev

***********
Step-3: Reverd - Broadcasting 
***********

    1. php artisan install:broadcasting
       (yes) (yes)
       [show .env , config/reverb.php, brodcasting.php, resourse/js/echo.js] 

    2. bootstrap/app.php
       ->withRouting(
       channels: __DIR__.'/../routes/channels.php',
       )

    3.  php artisan make:model Message -m
        php artisan migrate



        
***********
Step-4: Database 
***********
1. 

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_two_id')->constrained('users')->onDelete('cascade');
            $table->unique(['user_one_id', 'user_two_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_groups');
    }
};



2.

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('conversation_id')->nullable()->constrained('chat_groups')->nullOnDelete();
            $table->text('text')->nullable();
            $table->enum('status', ['sent', 'read', 'unread'])->default('unread');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};



        
***********
Step-4: Model - Relationship
***********


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    protected $fillable = ['user_one_id','user_two_id'];
}
?>




    
    ------>>>>> User Model Relationship <<<<<<-------

<?php
    public function senders()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function receivers()
    {
        return $this->hasMany(ChatMessage::class, 'receiver_id');
    }
?>



<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'text',
        'conversation_id'
    ];

    protected function casts(): array {
        return [
            'sender_id'         => 'integer',
            'receiver_id'       => 'integer',
            'text'              => 'string',
        ];
    }

    public function sender(): BelongsTo {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}

?>


-----------------------------------


    //! user has many chat messages
    public function senders() {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function receivers() {
        return $this->hasMany(ChatMessage::class, 'receiver_id');
    }




***********
Step-5: Make a Chennel
***********

    //!! channel.php automatically created 
    //!! If need create channel 
    php artisan make:channel OrderChannel (channel name)
    php artisan channel:list


    -->> routes/channels.php

    //! live chat channel added by rasel bhai last time

    /*
    * The user who is logged in to the website is the $user here.
    * reciver_id is the $id which receives the message
    */
    Broadcast::channel('chat.{conversation_id}', function ($user, $conversation_id) {
        $conversation = ChatGroup::find($conversation_id);

        return (int) $user->id === (int) $conversation?->user_one_id || (int) $user->id === (int) $conversation?->user_two_id;
    });


***********
Step-6: Make a Event
***********

    -->> app/Event/MessageSent


    <?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatMessage $message;

    public function __construct(ChatMessage $message) {
        $this->message = $message;
    }

    public function broadcastOn(): array {
        return [
            new PrivateChannel("chat.{$this->message->conversation_id}")
        ];
    }
}

?>

***********
Step-7: Route - API
***********

    [user can send message any user, get message between tow user (sender-receiver), get all message which user loged in, loged in user's conversation with diffrent user's with last message ]

    -->> routes/api.php

    //! Route for Chat Controller added by masum
    Route::get('/messages/receive/{user}', [ChatController::class, 'getMessages']);
    Route::post('/messages/send/{user}', [ChatController::class, 'sendMessage']);
    Route::get('/messages/group/{user}', [ChatController::class, 'getGroup']);
    Route::get('/messages/all', [ChatController::class, 'index']);
    Route::get('/messages/search', [ChatController::class, 'search']);
    Route::get('/messages/history', [ChatController::class, 'history']);
    Route::post('/messages/seen/{user}', [ChatController::class, 'seenMessage']);



***********
Step-8: Controller - API 
***********

    -->> app/http/controllers/api/chatcontroller.php 

    <?php

namespace App\Http\Controllers\API;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{

    public function index(): JsonResponse
    {

        //! (write 2-2-25)
        $authUserId = Auth::id();
        $adminId = 1;

        // Fetch users who are connected as senders or receivers with the authenticated user
        $users = User::whereHas('senders', function ($query) {
            $query->where('receiver_id', Auth::id());
        })->orWhereHas('receivers', function ($query) {
            $query->where('sender_id', Auth::id());
        })->where('id', '!=', Auth::user()->id)->get();


        //! Ensure Admin is always included in the list (write 2-2-25)
        $adminUser = User::find($adminId);
        if ($adminUser && !$users->contains('id', $adminId)) {
            $users->push($adminUser);
        }

        // Append the last message for each user
        $usersWithMessages = $users->map(function ($user) {
            $lastMessage = ChatMessage::where(function ($query) use ($user) {
                $query->where('sender_id', Auth::id())
                    ->where('receiver_id', $user->id);
            })->orWhere(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', Auth::id());
            })->latest()->first();

            return [
                'user'              => $user,
                'last_message'      => $lastMessage,
            ];
        });

        // Sort users by the last message's created_at timestamp in descending order
        $sortedUsersWithMessages = $usersWithMessages->sortByDesc(function ($item) {
            return optional($item['last_message'])->created_at;
        })->values(); // Reset keys after sorting

        return response()->json([
            'success'               => true,
            'code'                  => 200,
            'message'               => 'Trainers retrieved successfully',
            'data'                  => $sortedUsersWithMessages,
        ], 200);
    }



    public function search(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $users = User::where('id', '!=', auth('api')->user()->id)->where(function ($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
        })->get();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Trainers retrieved successfully',
            'data'    => $users,
        ], 200);
    }

    /* public function history(): JsonResponse
    {
        $authUser = auth('api')->user();

        $senders = ChatMessage::where('receiver_id', $authUser->id)->distinct()->pluck('sender_id');
        $receivers = ChatMessage::where('sender_id', $authUser->id)->distinct()->pluck('receiver_id');

        $users = User::whereIn('id', $senders->merge($receivers))->get();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Chat messages retrieved successfully',
            'data' => $users,
        ], 200);

    } */

    /**
     ** Get messages between the authenticated user and another user
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function getMessages(User $user, Request $request): JsonResponse
    {
        $messages = ChatMessage::query()
            ->where(function ($query) use ($user, $request) {
                $query->where('sender_id', $request->user()->id)
                    ->where('receiver_id', $user->id);
            })
            ->orWhere(function ($query) use ($user, $request) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', $request->user()->id);
            })
            ->with([
                'sender:id,name,avatar',
                'receiver:id,name,avatar',
            ])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success'           => true,
            'message'           => 'Messages retrieved successfully',
            'data'              => $messages,
        ]);
    }

    /**
     *! Send a message to another user
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'message'         => 'required|string',
        ]);

        $receiver_id = $user->id;
        $conversation = ChatGroup::where(function ($query) use ($receiver_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', Auth::id());
        })->orWhere(function ($query) use ($receiver_id) {
            $query->where('user_one_id', Auth::id())->where('user_two_id', $receiver_id);
        })->first();

        if (!$conversation) {
            $conversation = ChatGroup::create([
                'user_one_id'   => Auth::id(),
                'user_two_id'   => $receiver_id,
            ]);
        }

        $message = ChatMessage::create([
            'sender_id'         => $request->user()->id,
            'receiver_id'       => $receiver_id,
            'text'              => $request->message,
            'conversation_id'   => $conversation->id,
            'status'            => 'sent',
        ]);

        //* Load the sender's information
        $message->load(['sender:id,name,avatar', 'receiver:id,name,avatar']);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success'           => true,
            'message'           => 'Message sent successfully',
            'data'              => $message,
        ]);
    }

    /* public function seenMessage(User $user, Request $request): JsonResponse
    {
        $message = ChatMessage::where('receiver_id', $request->user()->id)->update(['status' => true]);
        return response()->json([
            'success'           => true,
            'message'           => 'Message seen successfully',
            'data'              => $message,
        ]);
    } */

    public function getGroup(User $user)
    {
        $receiver_id = $user->id;

        $conversation = ChatGroup::where(function ($query) use ($receiver_id) {
            $query->where('user_one_id', $receiver_id)->where('user_two_id', Auth::id());
        })->orWhere(function ($query) use ($receiver_id) {
            $query->where('user_one_id', Auth::id())->where('user_two_id', $receiver_id);
        })->first();

        if (!$conversation) {
            $conversation = ChatGroup::create([
                'user_one_id'   => Auth::id(),
                'user_two_id'   => $receiver_id,
            ]);
        }

        return response()->json([
            'success'           => true,
            'message'           => 'Group retrieved successfully',
            'data'              => $conversation,
        ]);
    }
}
?>



    ---------->>>>>>>>>>>>> For Flutter The Chatting System API Complete <<<<<<<<------------







