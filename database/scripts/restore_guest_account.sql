-- Net-Works: restore the shared guest account used by the mobile app
--
-- The mobile app signs in as guest@keylines.net / 123456 on launch so visitors
-- land on the business list without registering. If this account is missing the
-- app falls back to the Register / Login screen.
--
-- Safe to run more than once: rows are only inserted when missing.
--   mysql -u YOUR_USER -p YOUR_DATABASE < database/scripts/restore_guest_account.sql

INSERT INTO user_master (
    um_utm_id, um_user_name, um_mobile_no, um_email_id, um_password,
    um_is_change_password, um_status, um_profile_type, um_created_at, um_updated_at
)
SELECT
    3, 'GU001988', '0000000000', 'guest@keylines.net',
    -- bcrypt of '123456' (Constants.guestPassword in the mobile app)
    '$2y$10$MRBjRPrXRRMrrvfpT07vuuSsqCLWO41G.n8B3xLyuuq1qgD2Qd/k2',
    0, 2, 'O', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM user_master WHERE um_email_id = 'guest@keylines.net');

-- The profile API reads the display name from user_details
INSERT INTO user_details (ud_um_id, ud_first_name, ud_created_at, ud_updated_at)
SELECT um.um_id, 'Guest User', NOW(), NOW()
FROM user_master um
WHERE um.um_email_id = 'guest@keylines.net'
  AND NOT EXISTS (SELECT 1 FROM user_details ud WHERE ud.ud_um_id = um.um_id);

SELECT um_id, um_utm_id, um_user_name, um_email_id, um_status
FROM user_master
WHERE um_email_id = 'guest@keylines.net';
