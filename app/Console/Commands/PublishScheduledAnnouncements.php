<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Illuminate\Console\Command;

class PublishScheduledAnnouncements extends Command
{
    protected $signature   = 'cms:publish-scheduled';
    protected $description = 'Publish any announcements whose scheduled_at has passed.';

    public function handle(): int
    {
        $count = Announcement::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get()
            ->each(function (Announcement $ann) {
                $ann->update([
                    'status'       => 'published',
                    'is_published' => true,
                    'published_at' => $ann->scheduled_at,
                ]);
                $this->line("  Published: [{$ann->id}] {$ann->title}");
            })
            ->count();

        $this->info("cms:publish-scheduled — {$count} announcement(s) published.");
        return self::SUCCESS;
    }
}
