-- Net-Works: reset all member and business data
--
-- DESTRUCTIVE AND IRREVERSIBLE. Take a database backup before running this file.
-- This preserves super-admin accounts, lookup/configuration tables, chapters,
-- membership plans, events, CMS pages, homepage content, and application settings.
--
-- Run from a MySQL client after selecting the correct live database:
--   mysql -u YOUR_USER -p YOUR_DATABASE < database/scripts/reset_member_business_data.sql
--
-- The safety value below must remain exactly RESET-NETWORK-MEMBERS-AND-BUSINESSES.

SET @reset_confirmation = 'RESET-NETWORK-MEMBERS-AND-BUSINESSES';

DELIMITER $$

DROP PROCEDURE IF EXISTS reset_network_member_business_data$$
CREATE PROCEDURE reset_network_member_business_data()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET FOREIGN_KEY_CHECKS = 1;
        RESIGNAL;
    END;

    IF COALESCE(@reset_confirmation, '') <> 'RESET-NETWORK-MEMBERS-AND-BUSINESSES' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Reset cancelled: confirmation value is missing or incorrect.';
    END IF;

    START TRANSACTION;
    SET FOREIGN_KEY_CHECKS = 0;

    -- Remove admin access granted to ordinary members. Standalone super-admins remain.
    DELETE FROM admins WHERE user_master_id IS NOT NULL;

    -- Event registrations. Event definitions, tickets and form configuration remain.
    DELETE FROM event_attendees;
    DELETE FROM event_order_items;
    DELETE FROM event_orders;

    -- Membership accounting and membership registrations.
    DELETE FROM membership_accounting_audits;
    DELETE FROM membership_receipts;
    DELETE FROM membership_payment_allocations;
    DELETE FROM membership_invoice_items;
    DELETE FROM membership_payments;
    DELETE FROM membership_invoices;
    DELETE FROM member_memberships;

    -- Member activity, authentication and communications.
    DELETE FROM chapter_members;
    DELETE FROM business_analytics_events;
    DELETE FROM company_click_master;
    DELETE FROM delete_account_requests;
    DELETE FROM user_registration_otps;
    DELETE FROM password_reset_tokens;
    DELETE FROM notifications;
    DELETE FROM user_devices;
    DELETE FROM user_activities;
    DELETE FROM bulk_import_jobs;

    -- Enquiries, referrals and reviews created by or addressed to members/businesses.
    DELETE FROM enquiry_to_user;
    DELETE FROM enquiry_master;
    DELETE FROM enquiries;
    DELETE FROM reviews;

    -- Business portfolio and public-page content.
    DELETE FROM business_portfolio_media;
    DELETE FROM business_portfolio_items;
    DELETE FROM business_portfolios;
    DELETE FROM categories_to_companies;
    DELETE FROM company_sociallink;
    DELETE FROM company_images;

    -- Remove member-to-business ownership before deleting the root records.
    DELETE FROM user_companies_map;
    DELETE FROM user_details;
    DELETE FROM companies_details;
    DELETE FROM companies_master;
    DELETE FROM user_master;

    SET FOREIGN_KEY_CHECKS = 1;
    COMMIT;
END$$

DELIMITER ;

CALL reset_network_member_business_data();
DROP PROCEDURE reset_network_member_business_data;

-- Verification: all values returned below should be zero.
SELECT
    (SELECT COUNT(*) FROM user_master) AS users_remaining,
    (SELECT COUNT(*) FROM user_details) AS user_details_remaining,
    (SELECT COUNT(*) FROM companies_master) AS businesses_remaining,
    (SELECT COUNT(*) FROM companies_details) AS business_details_remaining,
    (SELECT COUNT(*) FROM user_companies_map) AS ownership_links_remaining;
