<?php

declare(strict_types=1);

namespace Tests\Feature\Phase6;

use App\DataTransferObjects\CancellationQuote;
use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Mail\BookingReceivedMail;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Question;
use App\Models\Review;
use App\Models\Setting;
use App\Notifications\AdminAlert;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesBookableTour;
use Tests\TestCase;

class MailTest extends TestCase
{
    use CreatesBookableTour, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['queue.default' => 'sync']); // run the queued listeners immediately
        $this->setUpBookingConfig();

        Setting::updateOrCreate(['key' => 'admin_notification_email'], ['group' => 'email', 'type' => 'string', 'value' => 'ops@example.com']);
    }

    private function admin(): \Closure
    {
        return fn ($notification, $channels, $notifiable) => ($notifiable->routes['mail'] ?? null) === 'ops@example.com';
    }

    public function test_new_booking_emails_the_customer_and_alerts_staff(): void
    {
        Mail::fake();
        Notification::fake();

        $tour = $this->makeTour();
        $booking = app(BookingService::class)->create($tour, $this->quoteInput($this->openDate($tour)), $this->customer(), 'k1');

        Mail::assertSent(BookingReceivedMail::class, fn ($mail) => $mail->hasTo('ali@example.com') && $mail->booking->is($booking));
        Notification::assertSentOnDemand(AdminAlert::class, $this->admin());
    }

    public function test_confirming_emails_the_customer_with_the_voucher_attached(): void
    {
        $tour = $this->makeTour();
        $service = app(BookingService::class);
        $booking = $service->create($tour, $this->quoteInput($this->openDate($tour)), $this->customer(), 'k1');

        Mail::fake();

        $service->confirm($booking);

        Mail::assertSent(BookingConfirmedMail::class, fn ($mail) => $mail->hasTo('ali@example.com') && count($mail->attachments()) === 1);
    }

    public function test_cancelling_emails_the_customer_and_alerts_staff(): void
    {
        $tour = $this->makeTour();
        $service = app(BookingService::class);
        $booking = $service->create($tour, $this->quoteInput($this->openDate($tour)), $this->customer(), 'k1');

        Mail::fake();
        Notification::fake();

        $service->cancel($booking);

        Mail::assertSent(BookingCancelledMail::class, fn ($mail) => $mail->hasTo('ali@example.com') && $mail->quote->isFree);
        Notification::assertSentOnDemand(AdminAlert::class, $this->admin());
    }

    public function test_every_email_template_renders(): void
    {
        $booking = Booking::factory()->confirmed()->create(['customer_name' => 'Ali Khan']);
        $booking->load(['tour', 'campType', 'vehicle', 'addons', 'payments']);

        $mails = [
            new BookingReceivedMail($booking),
            new BookingConfirmedMail($booking),
            new BookingCancelledMail($booking, new CancellationQuote(isFree: false, feePercent: 50.0, feeAmount: 5000.0, hoursBeforeStart: 3.0)),
        ];

        foreach ($mails as $mail) {
            $html = $mail->render();

            $this->assertStringContainsString($booking->reference, $html);
            $this->assertStringContainsString('Ali', $html);
        }
    }

    public function test_staff_are_alerted_about_enquiries_and_pending_items_only(): void
    {
        Notification::fake();

        Review::factory()->approved()->create();
        Notification::assertNothingSent();

        Enquiry::factory()->create();
        Review::factory()->create();
        Question::factory()->create();

        Notification::assertSentOnDemandTimes(AdminAlert::class, 3);
    }

    public function test_alerts_fall_back_to_the_mail_from_address(): void
    {
        Setting::query()->where('key', 'admin_notification_email')->delete();
        Setting::flushCache();
        config(['mail.from.address' => 'from@example.com']);

        Notification::fake();

        Enquiry::factory()->create();

        Notification::assertSentOnDemand(AdminAlert::class, fn ($n, $channels, $notifiable) => ($notifiable->routes['mail'] ?? null) === 'from@example.com');
    }
}