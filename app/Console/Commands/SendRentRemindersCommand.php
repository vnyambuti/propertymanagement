<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RentReminderService;

class SendRentRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:rent-due
                            {--days=3 : Days before due date to send reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule rent payment reminders for upcoming due dates';

    /**
     * The rent reminder service.
     *
     * @var \App\Services\RentReminderService
     */
    protected $reminderService;

    /**
     * Create a new command instance.
     *
     * @param RentReminderService $reminderService
     * @return void
     */
    public function __construct(RentReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $daysBeforeDue = $this->option('days');

        $this->info("Scheduling rent reminders for payments due in {$daysBeforeDue} days...");

        $count = $this->reminderService->scheduleUpcomingRentReminders($daysBeforeDue);

        if ($count > 0) {
            $this->info("Successfully queued {$count} rent reminders.");
        } else {
            $this->info("No upcoming payments found for reminder scheduling.");
        }

        return 0;
    }
}
