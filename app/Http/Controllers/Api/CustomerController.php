<?php

namespace App\Http\Controllers\Api;

use Alert;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Models\Comment;
use App\Models\Reply;
use App\Models\Contact;
use App\Http\Resources\CustomerResource;
use App\Http\Requests\StoreCustomerRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


use Stripe\Stripe;
use Stripe\Charge;

use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return CustomerResource::collection($products);
    }

    public function addToCart(Request $request, $id)
    {
        
        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->id;
            $product = Product::find($id);

            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $productExistInCart = Cart::where('Product_id', $id)->where('user_id', $userId)->first();

            if ($productExistInCart) {
                $productExistInCart->quantity += $request->quantity;
                $productExistInCart->price = $product->discount_price ?? $product->price;
                $productExistInCart->save();

                return response()->json(['message' => 'Product quantity updated in the cart'], 200);
            } else {
                $cart = new Cart;
                $cart->user_id = $userId;
                $cart->product_title = $product->title;
                $cart->price = $product->discount_price ?? $product->price;
                $cart->image = $product->image;
                $cart->Product_id = $product->id;
                $cart->quantity = $request->quantity;
                $cart->save();

                return response()->json(['message' => 'Product added to cart successfully'], 201);
            }
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function getCart()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->id;

            $cart_count = Cart::where('user_id', '=', $userId)->count();
            
            $cart = Cart::where('user_id', '=', $userId)->orderBy('created_at', 'desc')->get();

            return response()->json([
                'cart' => $cart,
                'cart_count' => $cart_count,
            ], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function removeCart($id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(['error' => 'Cart item not found'], 404);
        }

        $cart->delete();

        return response()->json(['message' => 'Cart item removed successfully'], 200);
    }

    public function cashOrder(Request $request)
    {
        $totalProduct = $request->input('total_product');

        if ($totalProduct == 0) {
            return response()->json(['message' => 'No Product In Cart', 'alert_type' => 'warning'], 400);
        }

        $user = Auth::user();
        $userId = $user->id;

        $data = Cart::where('user_id', '=', $userId)->get();

        foreach ($data as $cartItem) {
            $order = new Order;

            $order->name = $cartItem->name;
            $order->email = $cartItem->email;
            $order->phone = $cartItem->phone;
            $order->address = $cartItem->address;
            $order->user_id = $cartItem->user_id;
            $order->product_title = $cartItem->product_title;
            $order->price = $cartItem->price;
            $order->quantity = $cartItem->quantity;
            $order->image = $cartItem->image;
            $order->product_id = $cartItem->Product_id;
            $order->payment_status = 'cash on delivery';
            $order->delivery_status = 'processing';

            $order->save();

            $product = Product::find($cartItem->Product_id);
            $product->quantity -= $cartItem->quantity; 
            $product->save();

            $cartItem->delete();
        }

        return response()->json(['message' => 'Order placed successfully', 'alert_type' => 'success'], 200);
    }

    public function stripe(Request $request, $totalprice)
    {
        if ($totalprice == 0) {
            return response()->json(['message' => 'No Product In Cart', 'alert_type' => 'warning'], 400);
        } else {
            $userId = Auth::user()->id;

            $cartCount = Cart::where('user_id', '=', $userId)->count();

            return response()->json(['totalprice' => $totalprice, 'cart_count' => $cartCount], 200);
        }
    }

    public function stripePost(Request $request, $totalprice)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            Charge::create([
                "amount" => $totalprice * 100,
                "currency" => "usd",
                "source" => $request->stripeToken,
                "description" => "Thanks for payment."
            ]);

            $user = Auth::user();
            $userId = $user->id;

            $data = Cart::where('user_id', '=', $userId)->get();

            foreach ($data as $cartItem) {
                $order = new Order;

                
                $order->name = $cartItem->name;
                $order->email = $cartItem->email;
                $order->phone = $cartItem->phone;
                $order->address = $cartItem->address;
                $order->user_id = $cartItem->user_id;
                $order->product_title = $cartItem->product_title;
                $order->price = $cartItem->price;
                $order->quantity = $cartItem->quantity;
                $order->image = $cartItem->image;
                $order->product_id = $cartItem->Product_id;
                $order->payment_status = 'Paid';
                $order->delivery_status = 'processing';

                $order->save();

                $cartItem->delete();
            }

            return response()->json(['message' => 'Payment Successful', 'alert_type' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment Failed', 'error' => $e->getMessage(), 'alert_type' => 'error'], 500);
        }
    }

    public function showOrder()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->id;

            $cartCount = Cart::where('user_id', '=', $userId)->count();
            $orders = Order::where('user_id', '=', $userId)->orderBy('created_at', 'desc')->get();

            return response()->json([
                'orders' => $orders,
                'cart_count' => $cartCount,
            ], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function cancelOrder($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->delivery_status = 'You canceled the order';

        $product = Product::find($order->product_id);

        if ($product) {
            $product->quantity += $order->quantity;
            $product->save();
        }

        $order->save();

        Alert::warning('Order Canceled', 'You Have Canceled Your Order');

        return response()->json(['message' => 'Order canceled successfully']);
    }

    public function addComment(Request $request)
    {
        if (Auth::id()) {
            $comment = new Comment;

            $comment->name = Auth::user()->name;
            $comment->user_id = Auth::user()->id;
            $comment->comment = $request->comment;

            $comment->save();

            return response()->json(['message' => 'Comment added successfully']);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


    public function show_comment()
    {
        $comments = Comment::with('replies')->orderBy('created_at', 'desc')->get();
        return response()->json(['comments' => $comments]);
    }


    public function deleteComment($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        if (Auth::id() == $comment->user_id) {
            $comment->delete();
            return response()->json(['message' => 'Comment deleted successfully']);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function addReply(Request $request)
    {
        if (Auth::id()) {
            $reply = new Reply;

            $reply->name = Auth::user()->name;
            $reply->user_id = Auth::user()->id;
            $reply->comment_id = $request->commentId;
            $reply->reply = $request->reply;

            $reply->save();

            return response()->json(['message' => 'Reply added successfully']);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteReply($id)
    {
        $reply = Reply::find($id);

        if (!$reply) {
            return response()->json(['message' => 'Reply not found'], 404);
        }

        if (Auth::id() == $reply->user_id) {
            $reply->delete();
            return response()->json(['message' => 'Reply deleted successfully']);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function productSearch(Request $request)
    {
        if (Auth::id()) {
            $user_id = Auth::user()->id;
            $cart_count = Cart::where('user_id', '=', $user_id)->count();
        } else {
            $cart_count = 0;
        }

        $comment = Comment::orderBy('id', 'desc')->get();
        $reply = Reply::all();

        $search_text = $request->search;

        $products = Product::where('title', 'LIKE', "%$search_text%")
            ->orWhere('category', 'LIKE', "%$search_text%")
            ->orderBy('id', 'desc')
            ->paginate(6);

        return response()->json([
            'products' => $products->items(), 
            
        ]);
    }

    public function addContact(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        
        $contact = new Contact;
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->subject = $request->subject;
        $contact->message = $request->message;
        $contact->save();

        return response()->json(['message' => 'Message Received. We will review your message and contact you soon.']);
    }


    public function viewDetails()
    {
        
        $user = Auth::user();

        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json(['user' => $user]);
    }
    

    public function updateDetails(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        
        $user = Auth::user();

        
        if ($user->id !== $request->user_id) {
            return response()->json(['error' => 'Unauthorized. You can only update your own details.'], 403);
        }

        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return response()->json(['message' => 'User details updated successfully']);
    }





    
        
}



