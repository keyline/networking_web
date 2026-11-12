<?php

namespace App\Services;

use App\Models\Companies\CompaniesMaster;
use App\Models\User\UserMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MemberDataDeletionService
{
    public function delete(UserMaster $user): void
    {
        $userId = (int) $user->um_id;
        $email = $user->um_email_id;
        $mobile = $user->um_mobile_no;
        $companyIds = DB::table('user_companies_map')->where('ucm_um_id', $userId)
            ->pluck('ucm_cmp_id')->map(fn ($id) => (int) $id)->all();
        $files = $this->collectFiles($userId, $companyIds);

        DB::transaction(function () use ($userId, $email, $mobile, $companyIds) {
            $eventOrderIds = DB::table('event_orders')->where('user_id', $userId)->pluck('id');
            DB::table('event_attendees')->whereIn('event_order_id', $eventOrderIds)->delete();
            DB::table('event_order_items')->whereIn('event_order_id', $eventOrderIds)->delete();
            DB::table('event_orders')->whereIn('id', $eventOrderIds)->delete();

            $membershipIds = DB::table('member_memberships')->where('user_id', $userId)->pluck('id');
            $invoiceIds = DB::table('membership_invoices')->where('user_id', $userId)->orWhereIn('membership_id', $membershipIds)->pluck('id');
            $paymentIds = DB::table('membership_payments')->where('user_id', $userId)->orWhereIn('membership_id', $membershipIds)->pluck('id');
            DB::table('membership_receipts')->whereIn('payment_id', $paymentIds)->delete();
            DB::table('membership_payment_allocations')->whereIn('payment_id', $paymentIds)->orWhereIn('invoice_id', $invoiceIds)->delete();
            DB::table('membership_invoice_items')->whereIn('invoice_id', $invoiceIds)->delete();
            DB::table('membership_accounting_audits')->where('actor_id', $userId)
                ->orWhere(fn ($query) => $query->where('auditable_type', 'App\\Models\\Accounting\\MembershipInvoice')->whereIn('auditable_id', $invoiceIds))
                ->orWhere(fn ($query) => $query->where('auditable_type', 'App\\Models\\Accounting\\MembershipPayment')->whereIn('auditable_id', $paymentIds))
                ->delete();
            DB::table('membership_payments')->whereIn('id', $paymentIds)->delete();
            DB::table('membership_invoices')->whereIn('id', $invoiceIds)->delete();
            DB::table('member_memberships')->whereIn('id', $membershipIds)->delete();

            DB::table('chapter_members')->where('user_id', $userId)->orWhereIn('company_id', $companyIds)->delete();
            DB::table('business_analytics_events')->where('bae_um_id', $userId)->orWhereIn('bae_cmp_id', $companyIds)->delete();
            DB::table('company_click_master')->where('user_id', $userId)->orWhereIn('ccm_cmp_id', $companyIds)->delete();
            DB::table('reviews')->where('rev_um_id', $userId)->orWhereIn('rev_cmp_id', $companyIds)->delete();
            DB::table('enquiries')->whereIn('company_id', $companyIds)->delete();

            $enquiryIds = DB::table('enquiry_to_user')->where('etu_um_id', $userId)
                ->orWhereIn('etu_cmp_id', $companyIds)->pluck('etu_enm_id');
            DB::table('enquiry_to_user')->where('etu_um_id', $userId)->orWhereIn('etu_cmp_id', $companyIds)->delete();
            DB::table('enquiry_master')->whereIn('enm_id', $enquiryIds)
                ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('enquiry_to_user')->whereColumn('etu_enm_id', 'enquiry_master.enm_id'))
                ->delete();

            DB::table('business_portfolio_media')->whereIn('company_id', $companyIds)->delete();
            DB::table('business_portfolio_items')->whereIn('company_id', $companyIds)->delete();
            DB::table('business_portfolios')->whereIn('company_id', $companyIds)->delete();
            DB::table('categories_to_companies')->whereIn('ctc_cmp_id', $companyIds)->delete();
            DB::table('company_sociallink')->whereIn('cs_cmp_id', $companyIds)->delete();
            DB::table('company_images')->whereIn('ci_cmp_id', $companyIds)->delete();
            DB::table('user_devices')->where('user_id', $userId)->orWhereIn('company_id', $companyIds)->delete();
            DB::table('user_activities')->where('user_email', $email)->orWhereIn('company_id', $companyIds)->delete();

            DB::table('admins')->where('user_master_id', $userId)->delete();
            DB::table('delete_account_requests')->where('email', $email)->orWhere('phone', $mobile)->delete();
            DB::table('user_registration_otps')->where('uro_email', $email)->orWhere('uro_mobileno', $mobile)->delete();
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            DB::table('user_companies_map')->where('ucm_um_id', $userId)->orWhereIn('ucm_cmp_id', $companyIds)->delete();
            DB::table('user_details')->where('ud_um_id', $userId)->delete();
            DB::table('companies_details')->whereIn('cmpd_cmp_id', $companyIds)->delete();
            DB::table('companies_master')->whereIn('cmp_id', $companyIds)->delete();
            DB::table('user_master')->where('um_id', $userId)->delete();
        });

        $this->deleteFiles($files, $companyIds);
    }

    /**
     * Activity that blocks deleting a business, keyed by company id:
     * [cmp_id => ['references' => n, 'reviews' => n, 'meetings' => n, 'chapters' => n]].
     * A fixed number of grouped queries for the whole list.
     */
    public function businessActivity(array $companyIds): array
    {
        $companyIds = array_values(array_unique(array_map('intval', $companyIds)));
        if (! $companyIds) {
            return [];
        }
        $owners = DB::table('user_companies_map')->whereIn('ucm_cmp_id', $companyIds)->get(['ucm_cmp_id', 'ucm_um_id']);
        $ownerIds = $owners->pluck('ucm_um_id')->unique()->values()->all();
        $refs = DB::table('enquiry_to_user')->whereIn('etu_cmp_id', $companyIds)->selectRaw('etu_cmp_id as id, COUNT(*) as n')->groupBy('etu_cmp_id')->pluck('n', 'id');
        $legacy = DB::table('enquiries')->whereIn('company_id', $companyIds)->selectRaw('company_id as id, COUNT(*) as n')->groupBy('company_id')->pluck('n', 'id');
        $reviews = DB::table('reviews')->whereIn('rev_cmp_id', $companyIds)->selectRaw('rev_cmp_id as id, COUNT(*) as n')->groupBy('rev_cmp_id')->pluck('n', 'id');
        $chapters = DB::table('chapter_members')->whereIn('company_id', $companyIds)->whereIn('status', ['invited', 'active'])->selectRaw('company_id as id, COUNT(*) as n')->groupBy('company_id')->pluck('n', 'id');
        $meetingsByUser = collect();
        if ($ownerIds) {
            foreach (['reported_by_um_id', 'counterpart_um_id'] as $column) {
                DB::table('member_meetings')->whereIn($column, $ownerIds)->selectRaw("{$column} as id, COUNT(*) as n")->groupBy($column)->get()
                    ->each(function ($row) use ($meetingsByUser) { $meetingsByUser[$row->id] = ($meetingsByUser[$row->id] ?? 0) + (int) $row->n; });
            }
        }
        $result = [];
        foreach ($companyIds as $id) {
            $meetings = $owners->where('ucm_cmp_id', $id)->sum(fn ($o) => $meetingsByUser[$o->ucm_um_id] ?? 0);
            $result[$id] = [
                'references' => (int) ($refs[$id] ?? 0) + (int) ($legacy[$id] ?? 0),
                'reviews' => (int) ($reviews[$id] ?? 0),
                'meetings' => (int) $meetings,
                'chapters' => (int) ($chapters[$id] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Delete one business that has no activity. Owner accounts are kept; an owner left
     * without any business becomes a Guest (user type 3).
     *
     * @return array<int> ids of owners converted to guest
     */
    public function deleteLinkedBusiness(CompaniesMaster $business): array
    {
        $companyId = (int) $business->cmp_id;
        $activity = $this->businessActivity([$companyId])[$companyId];
        if (array_sum($activity) > 0) {
            throw new \RuntimeException('This business has references, reviews, meetings or chapter memberships and cannot be deleted.');
        }
        $files = $this->collectFiles(0, [$companyId]);
        $guests = [];

        DB::transaction(function () use ($companyId, &$guests) {
            $ownerIds = DB::table('user_companies_map')->where('ucm_cmp_id', $companyId)->pluck('ucm_um_id')->all();
            DB::table('user_companies_map')->where('ucm_cmp_id', $companyId)->delete();
            $this->deleteBusinessRows([$companyId]);
            foreach ($ownerIds as $ownerId) {
                if (! DB::table('user_companies_map')->where('ucm_um_id', $ownerId)->exists()) {
                    DB::table('user_master')->where('um_id', $ownerId)->update(['um_utm_id' => 3]);
                    $guests[] = (int) $ownerId;
                }
            }
        });

        $this->deleteFiles($files, [$companyId]);
        \App\Services\BusinessSearchIndex::touch([$companyId]);

        return $guests;
    }

    public function deleteOrphanBusiness(CompaniesMaster $business): void
    {
        $companyId = (int) $business->cmp_id;
        if (DB::table('user_companies_map')->where('ucm_cmp_id', $companyId)->exists()) {
            throw new \RuntimeException('A linked business cannot be deleted as an orphan.');
        }
        $companyIds = [$companyId];
        $files = $this->collectFiles(0, $companyIds);

        DB::transaction(fn () => $this->deleteBusinessRows($companyIds));

        $this->deleteFiles($files, $companyIds);
    }

    /** Remove every row that belongs to the given businesses (call inside a transaction). */
    private function deleteBusinessRows(array $companyIds): void
    {
        DB::table('chapter_members')->whereIn('company_id', $companyIds)->delete();
        DB::table('business_analytics_events')->whereIn('bae_cmp_id', $companyIds)->delete();
        DB::table('company_click_master')->whereIn('ccm_cmp_id', $companyIds)->delete();
        DB::table('reviews')->whereIn('rev_cmp_id', $companyIds)->delete();
        DB::table('enquiries')->whereIn('company_id', $companyIds)->delete();
        $enquiryIds = DB::table('enquiry_to_user')->whereIn('etu_cmp_id', $companyIds)->pluck('etu_enm_id');
        DB::table('enquiry_to_user')->whereIn('etu_cmp_id', $companyIds)->delete();
        DB::table('enquiry_master')->whereIn('enm_id', $enquiryIds)
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('enquiry_to_user')->whereColumn('etu_enm_id', 'enquiry_master.enm_id'))
            ->delete();
        DB::table('business_portfolio_media')->whereIn('company_id', $companyIds)->delete();
        DB::table('business_portfolio_items')->whereIn('company_id', $companyIds)->delete();
        DB::table('business_portfolios')->whereIn('company_id', $companyIds)->delete();
        DB::table('categories_to_companies')->whereIn('ctc_cmp_id', $companyIds)->delete();
        DB::table('company_sociallink')->whereIn('cs_cmp_id', $companyIds)->delete();
        DB::table('company_images')->whereIn('ci_cmp_id', $companyIds)->delete();
        DB::table('user_devices')->whereIn('company_id', $companyIds)->delete();
        DB::table('user_activities')->whereIn('company_id', $companyIds)->delete();
        DB::table('companies_details')->whereIn('cmpd_cmp_id', $companyIds)->delete();
        DB::table('companies_master')->whereIn('cmp_id', $companyIds)->delete();
    }

    private function collectFiles(int $userId, array $companyIds): array
    {
        $files = [];
        $user = DB::table('user_details')->where('ud_um_id', $userId)->first();
        if ($user) {
            $files[] = ['public', 'uploads/user', $user->ud_profile_image ?? null];
            $files[] = ['public', 'uploads/trade_licenses', $user->ud_td_lic_file ?? null];
            $files[] = ['public', 'uploads/gst_certificates', $user->ud_gst_cert ?? null];
        }
        foreach (DB::table('companies_details')->whereIn('cmpd_cmp_id', $companyIds)->get() as $company) {
            $files[] = ['public', 'uploads/company', $company->cmpd_logo ?? null];
            $files[] = ['public', 'uploads/trade_licenses', $company->cmpd_doc_trade_license ?? null];
            $files[] = ['public', 'uploads/pan_images', $company->cmpd_doc_pan_img ?? null];
            $files[] = ['public', 'uploads/gst_certificates', $company->cmpd_doc_gst_certificate ?? null];
        }
        foreach (DB::table('company_images')->whereIn('ci_cmp_id', $companyIds)->pluck('ci_image_name') as $image) {
            $files[] = ['storage', 'public/upload/gallery', $image];
            $files[] = ['public', 'uploads/company', $image];
        }
        return $files;
    }

    private function deleteFiles(array $files, array $companyIds): void
    {
        foreach ($files as [$disk, $directory, $filename]) {
            if (!$filename || basename($filename) !== $filename) {
                continue;
            }
            $relative = $directory.'/'.$filename;
            $disk === 'storage' ? Storage::delete($relative) : File::delete(public_path($relative));
        }
        foreach ($companyIds as $companyId) {
            File::deleteDirectory(public_path('uploads/portfolio/'.(int) $companyId));
        }
    }
}
