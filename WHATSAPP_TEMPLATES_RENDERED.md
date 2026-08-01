# WhatsApp Templates — Rendered With Real Data

_Rendered from job `KP-S1-2608-0001` in the live test install._

### `order_created_customer` → customer
**Order confirmation to customer**

```
Namaste HTTP Customer 🙏
Your order is confirmed at Krishna Print Test.

Job No: *KP-S1-2608-0001*
1. Standard Visiting Card × 500
Total: ₹188,800.00
Advance Paid: ₹1,000.00
Balance Due: ₹187,800.00
Expected Delivery: 02-08-2026

Track your order anytime:
http://127.0.0.1:8099/track/49d359518220bbe20bc256820f42c6b8

Thank you!
Shop 1 | 9111111111
```

### `order_created_designer` → designer
**New work assigned to designer**

```
New work assigned to you 🎨
Job: *KP-S1-2608-0001*
Item: Standard Visiting Card (Qty 500)
Customer: HTTP Customer
Due: 02-08-2026
Priority: Urgent

Open your job board:
http://127.0.0.1:8099/admin/my-jobs
```

### `order_created_manager` → branch_manager
**New order alert to branch manager**

```
New order at Shop 1 📋
Job: *KP-S1-2608-0001*
Customer: HTTP Customer (9888877776)
Total: ₹188,800.00 | Advance: ₹1,000.00 | Balance: ₹187,800.00
Taken by: Counter C
Due: 02-08-2026
```

### `proof_ready_customer` → customer
**Design proof ready**

```
Namaste HTTP Customer 🙏
Your design for Job *KP-S1-2608-0001* (Standard Visiting Card) is ready to view.

Please check it and tap APPROVE or REQUEST CHANGES:
http://127.0.0.1:8099/proof/SAMPLE

Please check spelling, phone numbers and logo carefully before approving.
Krishna Print Test
```

### `proof_reminder_customer` → customer
**Proof pending reminder**

```
Gentle reminder 🙏 HTTP Customer
Your design proof for Job *KP-S1-2608-0001* is waiting for your approval.
Printing starts only after you approve.

http://127.0.0.1:8099/proof/SAMPLE

Krishna Print Test | 9111111111
```

### `change_requested_designer` → designer
**Change requested to designer**

```
Change requested ✏️
Job: *KP-S1-2608-0001* (Standard Visiting Card) — Revision #2
Customer: HTTP Customer

Feedback:
Make the name bigger and use blue colour

Open your job board:
http://127.0.0.1:8099/admin/my-jobs
```

### `proof_approved_designer` → designer
**Proof approved to designer**

```
Design approved ✅
Job: *KP-S1-2608-0001* (Standard Visiting Card)
Customer HTTP Customer has given final approval. Great work!
```

### `proof_approved_production` → production
**New job for production**

```
New job ready to print 🖨️
Job: *KP-S1-2608-0001*
Item: Standard Visiting Card (Qty 500)
Due: 02-08-2026
Priority: Urgent
Branch: Shop 1
```

### `printing_started_customer` → customer
**Printing started**

```
Update from Krishna Print Test 🖨️
Your Job *KP-S1-2608-0001* is now being printed.
Expected delivery: 02-08-2026

Track: http://127.0.0.1:8099/track/49d359518220bbe20bc256820f42c6b8
```

### `ready_for_delivery_customer` → customer
**Order ready**

```
Good news HTTP Customer! 🎉
Your order *KP-S1-2608-0001* is READY.
Balance due: ₹187,800.00

Collect from: Shop 1, Main Rd
Phone: 9111111111
```

### `delivered_customer` → customer
**Order delivered**

```
Thank you HTTP Customer 🙏
Your order *KP-S1-2608-0001* has been delivered.
Balance due: ₹187,800.00

We would love to serve you again!
Krishna Print Test | 9111111111
```

### `payment_received_customer` → customer
**Payment receipt**

```
Payment received ✅
Job: *KP-S1-2608-0001*
Amount: ₹1000.00
Total Paid: ₹1,000.00
Balance: ₹187,800.00

Receipt: http://127.0.0.1:8099/receipt/49d359518220bbe20bc256820f42c6b8/1
Thank you!
Krishna Print Test
```

### `balance_reminder_customer` → customer
**Balance reminder**

```
Namaste HTTP Customer 🙏
A gentle reminder from Krishna Print Test.
Job *KP-S1-2608-0001* has a pending balance of ₹187,800.00.

You can pay at the counter or on UPI.
Shop 1 | 9111111111
```

### `order_cancelled_customer` → customer
**Order cancelled**

```
Namaste HTTP Customer,
Your order *KP-S1-2608-0001* at Krishna Print Test has been cancelled.
If this is unexpected, please call 9111111111.
```

### `daily_summary_admin` → admin
**Daily summary to admin**

```
📊 Krishna Print Test — Daily Summary 01-08-2026
1. Standard Visiting Card × 500
```

### `overdue_alert_manager` → branch_manager
**Overdue job alert**

```
⚠️ Overdue job at Shop 1
Job: *KP-S1-2608-0001* (Standard Visiting Card)
Customer: HTTP Customer
Was due: 02-08-2026
Current status: Urgent
```

