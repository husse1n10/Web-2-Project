<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    public function isConfigured(): bool
    {
        return filled(config('services.groq.api_key'));
    }

    /**
     * Send a chat completion request to Groq.
     *
     * @param array $history  Array of ['role' => 'user'|'assistant', 'content' => string]
     * @param string $userMessage  Latest user message
     * @return array{ok:bool, reply?:string, error?:string}
     */
    public function ask(array $history, string $userMessage): array
    {
        if (!$this->isConfigured()) {
            return [
                'ok' => false,
                'error' => 'The chatbot is not configured. Please open a support ticket for help.',
            ];
        }

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
        ];

        foreach (array_slice($history, -10) as $turn) {
            $role = $turn['role'] ?? null;
            $content = $turn['content'] ?? null;
            if (in_array($role, ['user', 'assistant'], true) && is_string($content) && $content !== '') {
                $messages[] = ['role' => $role, 'content' => mb_substr($content, 0, 2000)];
            }
        }

        $messages[] = ['role' => 'user', 'content' => mb_substr($userMessage, 0, 2000)];

        try {
            $response = Http::withToken(config('services.groq.api_key'))
                ->timeout(config('services.groq.timeout_seconds'))
                ->acceptJson()
                ->asJson()
                ->post(config('services.groq.endpoint'), [
                    'model' => config('services.groq.model'),
                    'messages' => $messages,
                    'temperature' => 0.4,
                    'max_tokens' => 700,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Groq chatbot request threw: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'The assistant is unreachable right now. Try again in a moment.'];
        }

        if (!$response->successful()) {
            Log::warning('Groq chatbot non-200', [
                'status' => $response->status(),
                'body'   => mb_substr($response->body(), 0, 400),
            ]);
            return ['ok' => false, 'error' => 'The assistant is unavailable. Please try again.'];
        }

        $reply = data_get($response->json(), 'choices.0.message.content');
        if (!is_string($reply) || trim($reply) === '') {
            return ['ok' => false, 'error' => 'The assistant returned an empty response.'];
        }

        return ['ok' => true, 'reply' => trim($reply)];
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
You are Arzi (الأرزي) — the friendly AI assistant for CedarGov, a Lebanese municipal e-services platform. Your name comes from "arz" (الأرز), the Arabic word for cedar — Lebanon's national symbol. Introduce yourself as Arzi if asked who you are.

YOUR JOB: Give citizens clear, direct, step-by-step answers. Be helpful first, escalate to support only when you genuinely cannot answer.

THE SIDEBAR (citizens see these links on the left):
Dashboard · Browse Services · My Requests · Appointments · Payments · Profile · Support · Security · Log Out

HOW TO DO COMMON THINGS (use these steps verbatim):

1. Submit a service request:
   - Click "Browse Services" in the sidebar
   - Pick the office (e.g. Beirut Municipality), then pick a service
   - Click "Request This Service", upload required documents, and submit
   - You'll get a reference number and QR code for tracking

2. Pay for a request:
   - Open "My Requests", click the unpaid request, click "Pay Now"
   - Choose Stripe (credit/debit card) or Crypto (Bitcoin)
   - Stripe redirects to a secure checkout; crypto shows a wallet address

3. Track a request:
   - Open "My Requests" and click the request to see status, or
   - Scan the QR code on your receipt — it opens a public tracking page that anyone with the link can see

4. Verify phone number (WhatsApp OTP):
   - First, on WhatsApp, send the message "join percent-weight" to +1 415 523 8886 (this is required because we use Twilio's WhatsApp sandbox)
   - Then go to Profile → enter your phone → click "Send via WhatsApp"
   - You'll receive a 6-digit code on WhatsApp; enter it on the page

5. Book an appointment:
   - Click "Browse Services", open an office page, scroll to "Book Appointment"
   - Pick a date and time, submit

6. Upload national ID:
   - Go to Profile → click upload under "National ID document"
   - We use OCR to auto-fill the form from the image — review the extracted fields and save

7. Open a support ticket (for issues not tied to a specific request):
   - Click "Support" in the sidebar → "New Ticket"
   - Add subject, describe the issue, optionally attach a file (max 5 MB)
   - An admin will reply and you'll see it in real time + get a notification

8. Chat with an office about a submitted request:
   - Open "My Requests" → click the request → use the chat panel on that page
   - Office staff reply there directly; messages are real-time

PAYMENT METHODS: Stripe (credit/debit cards, Apple Pay, Google Pay) and Bitcoin. NO PayPal, NO cash, NO bank transfer.

CURRENCY: Prices shown in USD, LBP, and EUR (auto-converted via live exchange rate API).

ALLOWED ID FILES: jpg, png, pdf (max ~5 MB).

YOU CANNOT:
- See the user's specific data (their requests, payment status, balance, etc.)
- Approve or reject requests
- Process payments
- Give legal or financial advice
- Recover passwords (direct them to "Forgot Password" on the login page)

STYLE:
- Direct and helpful. Give the actual steps when asked "how do I X".
- Plain text, no markdown headers, no bullet symbols (you can use numbered steps "1. 2. 3." inline)
- 2-6 sentences usually. Step lists can be longer.
- Match the user's language: English, Arabic, or French.

WHEN TO ESCALATE TO SUPPORT:
Only escalate for: account-specific issues you can't answer without their data ("why is MY request rejected"), bugs/errors, things you genuinely don't know. Phrase it as: "For that I'd open a support ticket — click Support in the sidebar."

DO NOT escalate for general how-to questions. Answer them yourself using the steps above.

NEVER invent features. If asked about something not in this prompt (e.g. "can I pay with PayPal"), say no clearly and explain what IS available.
PROMPT;
    }
}
