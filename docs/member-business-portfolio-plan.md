# Member Business Portfolio Module

## Product goal

Give every business owner a shareable single-page portfolio that can be maintained comfortably from a phone without technical knowledge. The stable public URL remains `/business/{slug}`.

## Roles and permissions

- Members may manage only companies linked to them through `user_companies_map`.
- Public visitors may view active business pages and submit enquiries without signing in.
- Admin users retain oversight through the existing business and enquiry administration.
- Every editor endpoint performs a server-side ownership check; hiding UI controls is not considered authorization.

## Member workflow

1. Open **My business** and tap **Manage page**.
2. Save page basics, contact destinations, WhatsApp settings and header image as a draft.
3. Add products/services, gallery photos and YouTube links.
4. Preview the current public page in a separate tab.
5. Tap **Publish changes**. Publishing creates an immutable JSON snapshot so later draft edits do not alter the live page.

The editor is single-column on phones, uses large touch targets and native mobile keyboards, explains image processing in plain language, preserves validation input and keeps Preview/Publish controls close at hand.

## Content model

- `business_portfolios`: page copy, hero, contact/notification settings, WhatsApp and the published snapshot.
- `business_portfolio_items`: products and services, optional image, price text and external link.
- `business_portfolio_media`: optimized gallery images and validated YouTube video IDs.
- Existing `companies_master`, `companies_details`, categories, social links and legacy gallery data remain supported.

## Image pipeline

- Accept JPEG, PNG and WebP uploads up to 10 MB.
- Decode the actual raster, reject invalid or very large-dimension images, correct JPEG orientation and strip metadata.
- Resize and recompress server-side to JPEG using generated UUID filenames.
- Verify that every stored portfolio image is at most 204,800 bytes.
- Store under `public/uploads/portfolio/{company}/{section}` and delete replaced/removed managed files.

## Public page

- Header image, logo, tagline and categories.
- About, products/services, gallery and privacy-enhanced YouTube embeds.
- Contact/location and social links.
- Floating WhatsApp action with a sanitized number and URL-encoded message.
- Enquiry form shown only when enabled.
- Empty optional sections remain hidden; legacy business pages continue to render.

## Enquiry and alerts

1. Validate CSRF, honeypot, rate limit, contact details and message.
2. Create the enquiry and recipient mapping in one database transaction.
3. After persistence, send email and SMS independently to configured portfolio destinations or owner defaults.
4. Log alert failures without rolling back or hiding the successfully saved enquiry.

## Security and limits

- Cross-company edit/delete requests return 403/404.
- YouTube accepts only known YouTube hosts and strict 11-character IDs; raw iframe HTML is never accepted.
- External links require HTTP/HTTPS.
- Limits: 30 offerings, 24 gallery images and 10 videos per business.
- Public text is escaped by Blade; uploads use random names and managed paths.

## Verification and rollout

- Feature coverage includes ownership, draft/publish snapshots, public rendering, YouTube normalization and image size enforcement.
- Existing member authentication, admin authentication and public lead behavior are regression tested.
- On the legacy database, deploy the code and run only the new scoped migration before clearing caches.
- Confirm writable permissions for `public/uploads/portfolio`, then submit a test enquiry and verify both configured delivery channels on production.

## Next iteration

- Inline editing and reorder controls for existing offerings/media.
- Dedicated lead inbox with New/Contacted/Won/Lost status and follow-up notes.
- Admin moderation, notification delivery audit/retry and portfolio analytics.
- Signed draft preview and optional trusted-member self-publishing policy.
