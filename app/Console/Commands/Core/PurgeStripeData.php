<?php

namespace App\Console\Commands\Core;

use App\Services\Stripe\StripeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class PurgeStripeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purge:stripe-data
                            {--force : Force the operation to run without confirmation}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge all Stripe customer data (DANGEROUS - use with caution)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if running in production without force flag
        if (App::environment('production') && !$this->option('force')) {
            $this->error('This command is disabled in production environment.');
            $this->error('Use --force flag if you really need to run this in production.');
            return Command::FAILURE;
        }

        // Check if using live Stripe key without force flag
        $stripeSecret = config('services.stripe.secret') ?? env('STRIPE_SECRET');
        if ($stripeSecret && str_starts_with($stripeSecret, 'sk_live_') && !$this->option('force')) {
            $this->error('This command is configured with a LIVE Stripe key (sk_live_*).');
            $this->error('Running this command would delete REAL customer data from your live Stripe account.');
            $this->error('Use --force flag if you really need to run this with a live key.');
            return Command::FAILURE;
        }

        $isDryRun = $this->option('dry-run');
        $isForced = $this->option('force');

        // Show warning and get confirmation if not forced
        if (!$isForced && !$isDryRun) {
            $this->warn('⚠️  WARNING: This will DELETE ALL Stripe customers permanently!');
            $this->warn('This action cannot be undone.');

            if (!$this->confirm('Are you absolutely sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }

            // Double confirmation for extra safety
            if (!$this->confirm('Last chance: Do you really want to delete ALL Stripe customers?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $stripe = new StripeService();
        $deletedCount = 0;
        $totalCount = 0;
        $hasMore = true;
        $startingAfter = null;

        $this->info($isDryRun ? '🔍 DRY RUN: Scanning Stripe customers...' : '🗑️  Deleting Stripe customers...');

        // Create progress bar
        $progressBar = null;

        try {
            // Iterate through all pages of customers
            while ($hasMore) {
                $params = ['limit' => 100];
                if ($startingAfter) {
                    $params['starting_after'] = $startingAfter;
                }

                $customers = $stripe->client->customers->all($params);
                $hasMore = $customers->has_more;

                if (count($customers->data) === 0) {
                    break;
                }

                // Initialize progress bar on first iteration
                if ($progressBar === null && !$isDryRun) {
                    $progressBar = $this->output->createProgressBar();
                    $progressBar->start();
                }

                foreach ($customers->data as $customer) {
                    $totalCount++;

                    if ($isDryRun) {
                        $this->line("Would delete customer: {$customer->id} (Email: {$customer->email})");
                    } else {
                        try {
                            $stripe->client->customers->delete($customer->id);
                            $deletedCount++;
                            $this->info("✅ Customer deleted: {$customer->id}");

                            if ($progressBar) {
                                $progressBar->advance();
                            }
                        } catch (\Exception $e) {
                            $this->error("❌ Failed to delete customer {$customer->id}: {$e->getMessage()}");
                        }
                    }

                    // Set starting_after for next page
                    $startingAfter = $customer->id;
                }

                // Add small delay to avoid rate limiting
                if ($hasMore) {
                    usleep(100000); // 100ms delay
                }
            }

            if ($progressBar) {
                $progressBar->finish();
                $this->newLine();
            }

        } catch (\Exception $e) {
            $this->error("Error occurred: {$e->getMessage()}");
            return Command::FAILURE;
        }

        // Display summary
        $this->newLine();
        $this->info('📊 Summary:');

        if ($isDryRun) {
            $this->info("Total customers found: {$totalCount}");
            $this->info("Customers that would be deleted: {$totalCount}");
            $this->warn('This was a DRY RUN - no customers were actually deleted.');
            $this->info('Run without --dry-run flag to perform actual deletions.');
        } else {
            $this->info("Total customers processed: {$totalCount}");
            $this->info("Successfully deleted: {$deletedCount}");
            $this->info("Failed deletions: " . ($totalCount - $deletedCount));

            if ($deletedCount > 0) {
                $this->warn("⚠️  {$deletedCount} Stripe customers have been permanently deleted.");
            }
        }

        return Command::SUCCESS;
    }
}
