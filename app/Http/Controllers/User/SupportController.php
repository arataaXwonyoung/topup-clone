<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    /**
     * Display list of support tickets
     */
    public function index(Request $request)
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->with(['category', 'lastMessage'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->priority, function ($query, $priority) {
                return $query->where('priority', $priority);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_tickets' => SupportTicket::where('user_id', Auth::id())->count(),
            'open_tickets' => SupportTicket::where('user_id', Auth::id())
                ->whereIn('status', ['open', 'pending'])
                ->count(),
            'resolved_tickets' => SupportTicket::where('user_id', Auth::id())
                ->where('status', 'resolved')
                ->count(),
            'average_response_time' => $this->getAverageResponseTime(),
        ];

        return view('user.support.index', compact('tickets', 'stats'));
    }

    /**
     * Show form to create new ticket
     */
    public function create()
    {
        $categories = [
            'payment' => 'Masalah Pembayaran',
            'delivery' => 'Masalah Pengiriman',
            'account' => 'Masalah Akun',
            'refund' => 'Permintaan Refund',
            'technical' => 'Masalah Teknis',
            'other' => 'Lainnya'
        ];

        // Get recent orders for reference
        $recentOrders = Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('user.support.create', compact('categories', 'recentOrders'));
    }

    /**
     * Store new support ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|in:payment,delivery,account,refund,technical,other',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'order_id' => 'nullable|exists:orders,id',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpeg,png,jpg,pdf,doc,docx|max:5120'
        ]);

        // Generate ticket number
        $ticketNumber = 'TK' . date('Ymd') . strtoupper(Str::random(6));

        // Handle attachments
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('support-attachments', 'public');
                $attachmentPaths[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType()
                ];
            }
        }

        // Create ticket
        $ticket = SupportTicket::create([
            'ticket_number' => $ticketNumber,
            'user_id' => Auth::id(),
            'category' => $request->category,
            'subject' => $request->subject,
            'status' => 'open',
            'priority' => $request->priority ?? 'normal',
            'order_id' => $request->order_id,
            'metadata' => [
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'created_via' => 'web'
            ]
        ]);

        // Create initial message
        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'attachments' => !empty($attachmentPaths) ? $attachmentPaths : null,
            'is_staff' => false
        ]);

        // Send notification to admin
        $this->notifyAdminNewTicket($ticket);

        // Send confirmation email to user
        $this->sendTicketConfirmation($ticket);

        return redirect()->route('user.support.show', $ticket->ticket_number)
            ->with('success', 'Tiket support berhasil dibuat. Tim kami akan segera merespon.');
    }

    /**
     * Show single ticket detail
     */
    public function show($ticketNumber)
    {
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', Auth::id())
            ->with(['messages.user', 'order.game', 'order.denomination'])
            ->firstOrFail();

        // Mark messages as read
        SupportMessage::where('ticket_id', $ticket->id)
            ->where('is_staff', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Get canned responses for quick reply
        $quickReplies = [
            'Terima kasih atas responnya.',
            'Masalah sudah teratasi, terima kasih.',
            'Saya masih mengalami masalah yang sama.',
            'Mohon tindak lanjut lebih lanjut.',
            'Bisakah saya mendapatkan update?'
        ];

        return view('user.support.show', compact('ticket', 'quickReplies'));
    }

    /**
     * Reply to ticket
     */
    public function reply(Request $request, $ticketNumber)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:jpeg,png,jpg,pdf|max:5120'
        ]);

        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Check if ticket is closed
        if ($ticket->status === 'closed') {
            return back()->with('error', 'Tiket ini sudah ditutup. Silakan buat tiket baru untuk masalah baru.');
        }

        // Handle attachments
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('support-attachments', 'public');
                $attachmentPaths[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType()
                ];
            }
        }

        // Create message
        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'attachments' => !empty($attachmentPaths) ? $attachmentPaths : null,
            'is_staff' => false
        ]);

        // Update ticket status if it was pending
        if ($ticket->status === 'pending') {
            $ticket->update(['status' => 'open']);
        }

        // Update ticket timestamp
        $ticket->touch();

        // Notify admin of new reply
        $this->notifyAdminNewReply($ticket);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Close ticket
     */
    public function close($ticketNumber)
    {
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', Auth::id())
            ->where('status', '!=', 'closed')
            ->firstOrFail();

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => Auth::id()
        ]);

        // Add system message
        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => 'Tiket ditutup oleh pengguna.',
            'is_system' => true
        ]);

        return redirect()->route('user.support')
            ->with('success', 'Tiket berhasil ditutup.');
    }

    /**
     * Reopen closed ticket
     */
    public function reopen($ticketNumber)
    {
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', Auth::id())
            ->where('status', 'closed')
            ->where('closed_at', '>', now()->subDays(7)) // Can only reopen within 7 days
            ->firstOrFail();

        $ticket->update([
            'status' => 'open',
            'reopened_at' => now()
        ]);

        // Add system message
        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => 'Tiket dibuka kembali oleh pengguna.',
            'is_system' => true
        ]);

        return redirect()->route('user.support.show', $ticket->ticket_number)
            ->with('success', 'Tiket berhasil dibuka kembali.');
    }

    /**
     * Rate support experience
     */
    public function rate(Request $request, $ticketNumber)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500'
        ]);

        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', Auth::id())
            ->where('status', 'closed')
            ->whereNull('rating')
            ->firstOrFail();

        $ticket->update([
            'rating' => $request->rating,
            'feedback' => $request->feedback,
            'rated_at' => now()
        ]);

        return back()->with('success', 'Terima kasih atas feedback Anda!');
    }

    /**
     * Download attachment
     */
    public function downloadAttachment($ticketNumber, $messageId, $index)
    {
        $ticket = SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $message = SupportMessage::where('ticket_id', $ticket->id)
            ->where('id', $messageId)
            ->firstOrFail();

        if (!$message->attachments || !isset($message->attachments[$index])) {
            abort(404);
        }

        $attachment = $message->attachments[$index];
        $path = Storage::disk('public')->path($attachment['path']);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $attachment['name']);
    }

    /**
     * Search tickets
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3'
        ]);

        $tickets = SupportTicket::where('user_id', Auth::id())
            ->where(function ($q) use ($request) {
                $q->where('ticket_number', 'like', '%' . $request->query . '%')
                  ->orWhere('subject', 'like', '%' . $request->query . '%')
                  ->orWhereHas('messages', function ($messageQuery) use ($request) {
                      $messageQuery->where('message', 'like', '%' . $request->query . '%');
                  });
            })
            ->with(['category', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('user.support.search', compact('tickets'));
    }

    /**
     * Get FAQ based on category
     */
    public function faq($category = null)
    {
        $faqs = [
            'payment' => [
                [
                    'question' => 'Bagaimana cara melakukan pembayaran?',
                    'answer' => 'Anda dapat melakukan pembayaran melalui berbagai metode: QRIS, Virtual Account, E-Wallet (GoPay, OVO, DANA, ShopeePay), dan Transfer Bank.'
                ],
                [
                    'question' => 'Pembayaran saya gagal, apa yang harus saya lakukan?',
                    'answer' => 'Pastikan saldo Anda mencukupi dan ikuti instruksi pembayaran dengan benar. Jika masih gagal, hubungi support kami dengan menyertakan bukti pembayaran.'
                ],
                [
                    'question' => 'Berapa lama proses verifikasi pembayaran?',
                    'answer' => 'Pembayaran biasanya terverifikasi otomatis dalam 1-5 menit. Jika lebih dari 10 menit, silakan hubungi support.'
                ]
            ],
            'delivery' => [
                [
                    'question' => 'Berapa lama proses pengiriman item?',
                    'answer' => 'Proses pengiriman item biasanya instant (1-5 menit) setelah pembayaran terverifikasi. Maksimal 1x24 jam.'
                ],
                [
                    'question' => 'Item belum masuk ke akun saya, apa yang harus dilakukan?',
                    'answer' => 'Pastikan ID dan Server yang dimasukkan sudah benar. Cek juga di in-game mail. Jika belum masuk, hubungi support dengan screenshot bukti.'
                ],
                [
                    'question' => 'Apakah bisa kirim ke ID yang salah?',
                    'answer' => 'Mohon maaf, item yang sudah terkirim ke ID yang salah tidak dapat dikembalikan. Pastikan selalu cek ID dengan teliti.'
                ]
            ],
            'refund' => [
                [
                    'question' => 'Kapan saya bisa request refund?',
                    'answer' => 'Refund dapat diajukan jika: pembayaran sudah masuk tapi item belum terkirim dalam 1x24 jam, atau ada kesalahan sistem dari pihak kami.'
                ],
                [
                    'question' => 'Berapa lama proses refund?',
                    'answer' => 'Proses refund membutuhkan waktu 3-7 hari kerja setelah disetujui oleh tim kami.'
                ],
                [
                    'question' => 'Kemana refund akan dikembalikan?',
                    'answer' => 'Refund akan dikembalikan ke metode pembayaran yang sama atau ke saldo akun Anda, sesuai kebijakan.'
                ]
            ]
        ];

        if ($category && isset($faqs[$category])) {
            return view('user.support.faq', ['faqs' => $faqs[$category], 'category' => $category]);
        }

        return view('user.support.faq', ['faqs' => $faqs, 'category' => null]);
    }

    /**
     * Get average response time for user's tickets
     */
    private function getAverageResponseTime()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->whereHas('messages', function ($query) {
                $query->where('is_staff', true);
            })
            ->get();

        if ($tickets->isEmpty()) {
            return 'N/A';
        }

        $totalResponseTime = 0;
        $count = 0;

        foreach ($tickets as $ticket) {
            $firstUserMessage = $ticket->messages()
                ->where('is_staff', false)
                ->orderBy('created_at')
                ->first();
                
            $firstStaffResponse = $ticket->messages()
                ->where('is_staff', true)
                ->where('created_at', '>', $firstUserMessage->created_at)
                ->orderBy('created_at')
                ->first();

            if ($firstUserMessage && $firstStaffResponse) {
                $responseTime = $firstStaffResponse->created_at->diffInMinutes($firstUserMessage->created_at);
                $totalResponseTime += $responseTime;
                $count++;
            }
        }

        if ($count === 0) {
            return 'N/A';
        }

        $avgMinutes = round($totalResponseTime / $count);
        
        if ($avgMinutes < 60) {
            return $avgMinutes . ' menit';
        } elseif ($avgMinutes < 1440) {
            return round($avgMinutes / 60) . ' jam';
        } else {
            return round($avgMinutes / 1440) . ' hari';
        }
    }

    /**
     * Notify admin about new ticket
     */
    private function notifyAdminNewTicket($ticket)
    {
        // Send email notification to admin
        // You can implement email notification here
        
        // Log activity
        activity()
            ->performedOn($ticket)
            ->causedBy(Auth::user())
            ->withProperties(['ticket_number' => $ticket->ticket_number])
            ->log('New support ticket created');
    }

    /**
     * Notify admin about new reply
     */
    private function notifyAdminNewReply($ticket)
    {
        // Send email notification to assigned staff
        // You can implement email notification here
        
        // Log activity
        activity()
            ->performedOn($ticket)
            ->causedBy(Auth::user())
            ->withProperties(['ticket_number' => $ticket->ticket_number])
            ->log('New reply on support ticket');
    }

    /**
     * Send ticket confirmation to user
     */
    private function sendTicketConfirmation($ticket)
    {
        // Send email confirmation to user
        // You can implement email notification here
        
        // For now, just log it
        \Log::info('Ticket confirmation sent', [
            'ticket_number' => $ticket->ticket_number,
            'user_id' => $ticket->user_id
        ]);
    }
}