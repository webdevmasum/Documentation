
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

    
    ------>>>>> User Model Relationship <<<<<<-------

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
            'sender_id'   => 'integer',
            'receiver_id' => 'integer',
            'text'        => 'string',
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
    Broadcast::channel('chat.{receiver_id}', function ($user, $receiver_id) {
        return (int) $user->id === (int) $receiver_id;
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

    /**
     * Create a new event instance.
     */
    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("chat.{$this->message->receiver_id}"),
        ];
    }

    public function broadcastWith()
    {
        return ['message' => $this->message];
    }
}
?>

***********
Step-7: Route - API
***********

    [user can send message any user, get message between tow user (sender-receiver), get all message which user loged in, loged in user's conversation with diffrent user's with last message ]

    -->> routes/api.php

    //! Route for Chat Controller added by masum
    Route::post('/send-message', [ChatController::class, 'sendMessage']);
    // Route::get('/get-messages/{conversation_id}', [ChatController::class, 'getMessages']);

    Route::get('/get-messages/{sender_id}/{receiver_id}', [ChatController::class, 'getMessages']);
    Route::get('/messages/all', [ChatController::class, 'index']);
    Route::get('/messages/history', [ChatController::class, 'history']);


***********
Step-8: Controller - API 
***********

    -->> app/http/controllers/api/chatcontroller.php 


namespace App\Http\Controllers\API;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

public function index(): JsonResponse
    {
        // Fetch users with the last message for each user
        $users = User::with([
            'senders' => function ($query) {
                $query->where('receiver_id', Auth::id())
                    ->latest()
                    ->limit(1); // Fetch only the latest message
            },
            'receivers' => function ($query) {
                $query->where('sender_id', Auth::id())
                    ->latest()
                    ->limit(1); // Fetch only the latest message
            },
        ])->where('id', '!=', Auth::id())->get();

        // Map users with their last message
        $usersWithMessages = $users->map(function ($user) {
            // Check for the last message from either sender or receiver
            $lastMessage = $user->senders->first() ?: $user->receivers->first();

            return [
                'user' => $user,
                'last_message' => $lastMessage,
            ];
        })->filter(function ($item) {
            return $item['last_message'] !== null;
        });

        // Sort users by the last message's created_at timestamp in descending order
        $sortedUsersWithMessages = $usersWithMessages->sortByDesc(function ($item) {
            return $item['last_message']->created_at;
        })->values();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Users retrieved successfully',
            'data' => $sortedUsersWithMessages,
        ], 200);
    }


    //! this function will send message working perfectly
      public function sendMessage(Request $request)
    {
        $message = ChatMessage::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'text' => $request->message,

            //! in database conversation_id will no need if it's not use
            // 'conversation_id' => $request->conversation_id

        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data'    => $message,
        ], 200);
    }    

    //!! this function is write with error message to get messages with sender_id and receiver_id
    public function getMessages($sender_id, $receiver_id)
    {
        try {
            // Get the messages where the sender and receiver match
            $messages = ChatMessage::where(function ($query) use ($sender_id, $receiver_id) {
                $query->where('sender_id', $sender_id)
                    ->where('receiver_id', $receiver_id);
            })->orWhere(function ($query) use ($sender_id, $receiver_id) {
                $query->where('sender_id', $receiver_id)
                    ->where('receiver_id', $sender_id);
            })
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Messages retrieved successfully',
                'data'    => $messages,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve messages',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }



    //!! One user conversation with multiple users history
    public function history(): \Illuminate\Http\JsonResponse
    {
        $authUser = auth('api')->user();

        $senders = ChatMessage::where('receiver_id', $authUser->id)->distinct()->pluck('sender_id');
        $receivers = ChatMessage::where('sender_id', $authUser->id)->distinct()->pluck('receiver_id');

        $users = User::whereIn('id', $senders->merge($receivers))->get();

        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => 'Chat users retrieved successfully',
            'data' => $users,
        ], 200);

    }




    ---------->>>>>>>>>>>>> For Flutter The Chatting System API Complete <<<<<<<<------------







