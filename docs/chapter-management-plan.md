# Chapter Management Plan

## Purpose

Chapters are local operating groups inside Net-Works. Organization membership remains the source of billing eligibility; chapter membership records where a person participates, which business they represent, their role, status, and history.

## Operating model

### People and roles

- Super administrators configure all chapters and policies.
- Chapter leaders manage only their assigned chapter.
- Leadership terms are dated and auditable.
- Members represent an existing business and retain their organization-level membership account.
- Visitors and applicants progress through invitation, attendance, follow-up, application, and conversion.

### Core workflows

1. Create a forming chapter with its code, venue, regular meeting time, capacity, and category policy.
2. Assign founding members and leadership roles.
3. Activate the chapter after configuration and leadership checks.
4. Generate recurring meetings, invite visitors, and record attendance.
5. Record referrals, introductions, one-to-one meetings, testimonials, and realized business.
6. Process applications, category conflicts, leaves, exits, and transfers without deleting history.
7. Show operational follow-ups and transparent chapter reports.

## Delivery phases

### Phase 1 — implemented foundation

- Chapter directory with search, status filters, and summary counts.
- Chapter create, view, edit, lifecycle status, venue, and recurring schedule.
- Member assignment using existing registered users and businesses.
- Leadership roles, dated join/leave lifecycle, notes, and retained history.
- Chapter roster and compact profile/summary screen.
- Admin navigation, validation, relational constraints, and regression tests.

### Phase 2 — meetings and visitors

- `chapter_meetings` with recurrence generation and optional event linkage.
- `chapter_attendance` with present, late, absent, substitute, excused, and remote states.
- QR/manual check-in, exception review, and meeting lock.
- Visitor invitation, registration, attendance, host follow-up, and conversion.
- Chapter announcements and member-facing “My Chapter” landing page.

### Phase 3 — referral operations

- Consent-aware referral records and recipient pipeline.
- Introductions, one-to-one meetings, testimonials, and closed-business contributions.
- Enquiry linkage without duplicating the existing enquiry system.
- Follow-up reminders and confidential, chapter-scoped access.

### Phase 4 — governance and intelligence

- Applications, configurable category conflict warnings/enforcement, and override reasons.
- Transfer workflow with source/destination approval and complete history.
- Time-bound leadership assignments and chapter-scoped permissions.
- Attendance, visitor conversion, referral outcome, retention, growth, and engagement reports.
- Mobile APIs, notifications, imports/exports, and cross-chapter collaboration.

## Data and security rules

- Never duplicate organization membership billing inside chapter records.
- Never physically delete attendance, referral, transfer, or historical membership records.
- Restrict leaders to their own chapter through policies/scopes, not only hidden menu items.
- Treat prospect details as confidential and expose aggregate reporting by default.
- Display metric definitions; do not create an opaque member score.
- Use existing `user_master`, company, membership, event, enquiry, email, and accounting data.

## Success measures

- Active members and retention.
- Attendance rate and absence trend.
- Visitors and visitor-to-member conversion.
- Referrals given/received, acceptance, and outcomes.
- Member-reported realized business.
- Application turnaround and unresolved category conflicts.
- Members with at least one meaningful contribution in the selected period.
