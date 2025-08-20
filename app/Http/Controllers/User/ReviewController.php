<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    /**
     * Display user's reviews
     */
    public function index(Request $request)
    {
        $reviews = Review::where('user_id', Auth::id())
            ->with(['order', 'game'])
            ->when($request->status, function ($query, $status) {
                if ($status === 'approved') {
                    return $query->where('is_approved', true);
                } elseif ($status === 'pending') {
                    return $query->where('is_approved', false);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_reviews' => Review::where('user_id', Auth::id())->count(),
            'approved_reviews' => Review::where('user_id', Auth::id())
                ->where('is_approved', true)
                ->count(),
            'pending_reviews' => Review::where('user_id', Auth::id())
                ->where('is_approved', false)
                ->count(),
            'average_rating' => Review::where('user_id', Auth::id())
                ->avg('rating') ?? 0,
        ];

        // Get orders that can be reviewed
        $reviewableOrders = Order::where('user_id', Auth::id())
            ->where('status', 'DELIVERED')
            ->whereDoesntHave('review')
            ->with(['game', 'denomination'])
            ->orderBy('delivered_at', 'desc')
            ->limit(5)
            ->get();

        return view('user.reviews.index', compact('reviews', 'stats', 'reviewableOrders'));
    }

    /**
     * Show form to create review
     */
    public function create($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->where('status', 'DELIVERED')
            ->whereDoesntHave('review')
            ->with(['game', 'denomination'])
            ->firstOrFail();

        return view('user.reviews.create', compact('order'));
    }

    /**
     * Store new review
     */
    public function store(Request $request, $orderId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'is_anonymous' => 'nullable|boolean'
        ]);

        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->where('status', 'DELIVERED')
            ->whereDoesntHave('review')
            ->firstOrFail();

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $imagePaths[] = $path;
            }
        }

        $review = Review::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'game_id' => $order->game_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'images' => !empty($imagePaths) ? $imagePaths : null,
            'is_anonymous' => $request->boolean('is_anonymous'),
            'is_verified' => true, // Auto verified for logged in users
            'is_approved' => false, // Needs admin approval
            'helpful_count' => 0,
            'metadata' => [
                'device' => $request->userAgent(),
                'ip' => $request->ip(),
                'order_amount' => $order->total,
                'item_name' => $order->denomination->name
            ]
        ]);

        // Give points/rewards for reviewing
        $this->giveReviewReward($order);

        return redirect()->route('user.reviews')
            ->with('success', 'Terima kasih atas review Anda! Review akan ditampilkan setelah disetujui admin.');
    }

    /**
     * Show single review detail
     */
    public function show($id)
    {
        $review = Review::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['order.game', 'order.denomination'])
            ->firstOrFail();

        // Get responses/replies from admin if any
        $responses = $review->responses()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.reviews.show', compact('review', 'responses'));
    }

    /**
     * Show form to edit review
     */
    public function edit($id)
    {
        $review = Review::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('is_approved', false) // Can only edit unapproved reviews
            ->with(['order.game', 'order.denomination'])
            ->firstOrFail();

        return view('user.reviews.edit', compact('review'));
    }

    /**
     * Update review
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'remove_images' => 'nullable|array',
            'is_anonymous' => 'nullable|boolean'
        ]);

        $review = Review::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('is_approved', false)
            ->firstOrFail();

        // Handle image removal
        $currentImages = $review->images ?? [];
        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $imageToRemove) {
                Storage::disk('public')->delete($imageToRemove);
                $currentImages = array_diff($currentImages, [$imageToRemove]);
            }
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $currentImages[] = $path;
            }
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'images' => !empty($currentImages) ? array_values($currentImages) : null,
            'is_anonymous' => $request->boolean('is_anonymous'),
            'is_approved' => false, // Reset approval status
        ]);

        return redirect()->route('user.reviews.show', $review->id)
            ->with('success', 'Review berhasil diperbarui. Menunggu persetujuan admin.');
    }

    /**
     * Delete review
     */
    public function destroy($id)
    {
        $review = Review::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Delete associated images
        if ($review->images) {
            foreach ($review->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $review->delete();

        return redirect()->route('user.reviews')
            ->with('success', 'Review berhasil dihapus.');
    }

    /**
     * Mark review as helpful
     */
    public function helpful($id)
    {
        $review = Review::findOrFail($id);
        
        // Check if user already marked as helpful
        $userId = Auth::id();
        $helpfulUsers = $review->helpful_users ?? [];
        
        if (!in_array($userId, $helpfulUsers)) {
            $helpfulUsers[] = $userId;
            $review->update([
                'helpful_users' => $helpfulUsers,
                'helpful_count' => count($helpfulUsers)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Terima kasih atas feedback Anda!',
                'helpful_count' => $review->helpful_count
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Anda sudah memberikan feedback untuk review ini.',
            'helpful_count' => $review->helpful_count
        ]);
    }

    /**
     * Report inappropriate review
     */
    public function report(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|in:spam,inappropriate,fake,offensive,other',
            'description' => 'required_if:reason,other|nullable|string|max:500'
        ]);

        $review = Review::findOrFail($id);
        
        // Store report in metadata
        $metadata = $review->metadata ?? [];
        $metadata['reports'][] = [
            'user_id' => Auth::id(),
            'reason' => $request->reason,
            'description' => $request->description,
            'reported_at' => now()
        ];
        
        $review->update(['metadata' => $metadata]);
        
        // If multiple reports, flag for admin review
        if (count($metadata['reports'] ?? []) >= 3) {
            $review->update(['is_flagged' => true]);
        }

        return back()->with('success', 'Terima kasih atas laporan Anda. Tim kami akan meninjau review ini.');
    }

    /**
     * Get review statistics for user dashboard
     */
    public function statistics()
    {
        $userId = Auth::id();
        
        $stats = [
            'total_reviews' => Review::where('user_id', $userId)->count(),
            'total_helpful' => Review::where('user_id', $userId)->sum('helpful_count'),
            'average_rating' => Review::where('user_id', $userId)->avg('rating'),
            'reviews_by_rating' => Review::where('user_id', $userId)
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray(),
            'recent_reviews' => Review::where('user_id', $userId)
                ->with('game')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json($stats);
    }

    /**
     * Give reward points for reviewing
     */
    private function giveReviewReward($order)
    {
        $user = Auth::user();
        $points = 50; // Base points
        
        // Bonus points for detailed review
        if (strlen($order->review->comment ?? '') > 100) {
            $points += 25;
        }
        
        // Bonus for including images
        if (!empty($order->review->images)) {
            $points += 25;
        }
        
        $user->addPoints($points);
        
        // Log the reward
        activity()
            ->performedOn($order->review)
            ->causedBy($user)
            ->withProperties(['points' => $points])
            ->log('Review reward given');
    }
}