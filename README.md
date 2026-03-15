# Laravel Approval Engine

A flexible **batch-based approval workflow engine** for Laravel applications.

Instead of sending approval emails per record, this engine groups records into **approval batches** and supports **multi-stage workflows**.

---

## Features

✔ Batch approval emails (20 records → 1 email)
✔ Multi-stage workflow approvals
✔ Token-based approval links
✔ Smart reminder engine
✔ Config-driven workflow modules
✔ Email / Slack / Teams notifications
✔ Plugin-based module architecture

---

## Example Workflow

```
Requisition Approved
        ↓
Batch created
        ↓
Email sent to HOS
        ↓
HOS Approves
        ↓
Move to COO
        ↓
COO Approves
        ↓
Completed
```

---

## Installation

Install via composer:

```
composer require apurba-labs/laravel-approval-engine
```

Publish config and migrations:

```
php artisan vendor:publish --tag=approval-config
```

Run migrations:

```
php artisan migrate
```

---

## Configuration

Edit config file:

```
config/approval-engine.php
```

Example module:

```
'requisition' => [

    'model' => App\Models\Requisition::class,

    'approved_column' => 'approved_at',

    'relations' => [
        'depot',
        'distributor',
        'shop'
    ],

    'display_columns' => [
        'reference_id' => 'Reference',
        'depot.name' => 'Depot',
        'distributor.name' => 'Distributor'
    ]

]
```

---

## Running Batch Processor

Run the batch processor via cron:

```
php artisan approval:send-batch
```

Example cron job:

```
* * * * * php artisan approval:send-batch
```

---

## Approval Links

Emails contain secure token-based approval links:

```
/approvals/batch/{token}
```

Approvers can:

• Approve All
• Reject
• View Details

---

## Creating Workflow Modules

Generate a new module:

```
php artisan make:workflow-module SalesOrder
```

---

## Roadmap

### v1.0

✔ Batch processing
✔ Email notifications
✔ Multi-stage approvals

### v1.1

✔ Reminder engine
✔ Slack notifications

### v2.0

✔ Visual workflow builder
✔ Approval dashboard

---

## License

MIT License
