<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Shop;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * List conversations for the authenticated shop owner.
     */
    public function index(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        $conversations = $shop->conversations()
            ->with(['latestMessage', 'product:id,name,images'])
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('sender_type', 'customer')->where('read', false);
            }])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return response()->json($conversations);
    }

    /**
     * Get a single conversation with messages.
     */
    public function show(Request $request, Conversation $conversation)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        if ($conversation->shop_id !== $shop->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Mark customer messages as read
        $conversation->messages()
            ->where('sender_type', 'customer')
            ->where('read', false)
            ->update(['read' => true]);

        $conversation->update(['shop_read' => true]);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'conversation' => $conversation->load('product:id,name,images'),
            'messages' => $messages,
        ]);
    }

    /**
     * Shop owner sends a message.
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        if ($conversation->shop_id !== $shop->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        $request->validate([
            'body' => 'required|string|max:5000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'string',
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'shop',
            'body' => $request->body,
            'attachments' => $request->attachments,
            'read' => true,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'customer_read' => false,
        ]);

        return response()->json($message, 201);
    }

    /**
     * Customer initiates or continues a conversation (public).
     */
    public function customerSend(Request $request, string $shopSlug)
    {
        $shop = Shop::where('slug', $shopSlug)->where('active', true)->firstOrFail();

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email',
            'product_id' => 'nullable|exists:products,id',
            'body' => 'required|string|max:5000',
            'conversation_id' => 'nullable|exists:conversations,id',
        ]);

        // Continue existing or create new conversation
        if ($request->conversation_id) {
            $conversation = Conversation::where('id', $request->conversation_id)
                ->where('shop_id', $shop->id)
                ->firstOrFail();
        } else {
            $conversation = Conversation::create([
                'shop_id' => $shop->id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'product_id' => $request->product_id,
                'last_message_at' => now(),
                'shop_read' => false,
                'customer_read' => true,
            ]);
        }

        $message = $conversation->messages()->create([
            'sender_type' => 'customer',
            'body' => $request->body,
            'read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
            'shop_read' => false,
        ]);

        return response()->json([
            'conversation_id' => $conversation->id,
            'message' => $message,
        ], 201);
    }

    /**
     * Customer fetches their conversation messages (by conversation_id + phone).
     */
    public function customerMessages(Request $request, Conversation $conversation)
    {
        $request->validate([
            'customer_phone' => 'required|string',
        ]);

        if ($conversation->customer_phone !== $request->customer_phone) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Mark shop messages as read
        $conversation->messages()
            ->where('sender_type', 'shop')
            ->where('read', false)
            ->update(['read' => true]);

        $conversation->update(['customer_read' => true]);

        return response()->json([
            'conversation' => $conversation->load('product:id,name,images'),
            'messages' => $conversation->messages()->orderBy('created_at')->get(),
        ]);
    }

    /**
     * Get unread count for shop dashboard.
     */
    public function unreadCount(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->firstOrFail();

        $count = Message::whereHas('conversation', function ($q) use ($shop) {
            $q->where('shop_id', $shop->id);
        })->where('sender_type', 'customer')
          ->where('read', false)
          ->count();

        return response()->json(['unread_count' => $count]);
    }
}
