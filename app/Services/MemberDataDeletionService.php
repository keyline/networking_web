<?php

namespace App\Services;

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
