<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportTicketRequest;
use App\Models\SupportTicket;

class ApiV1SupportTicketController extends Controller
{
    /**
     * Store a new support ticket.
     */
    public function store(StoreSupportTicketRequest $request)
    {
        $success = false;
        $message = 'Something went wrong!';
        $data = [];
        $statusCode = 422;

        try {
            $user = auth()->user();

            $ticket = new SupportTicket();
            $ticket->subject = $request->subject;
            $ticket->category = $request->category;
            $ticket->description = $request->description;
            $ticket->email = $request->email ?: ($user ? $user->email : null);
            $ticket->contact = $request->contact ?: ($user ? $user->contact : null);

            if ($user) {
                $ticket->creator_id = $user->id;
                $ticket->creator_type = $user->getMorphClass();
            }

            $ticket->save();

            $data = [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
            ];

            $success = true;
            $message = 'Support ticket created successfully.';
            $statusCode = 200;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $statusCode = 500;
        }

        return apiResponce($statusCode, $success, $message, $data);
    }
}
