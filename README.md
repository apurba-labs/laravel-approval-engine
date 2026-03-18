# Laravel Approval Engine

A modular, extensible multi-stage approval workflow engine for Laravel. It handles complex batching logic, scheduled email notifications, and token-based approval links with zero overhead.

---

## ✨ Features

- **Multi-Stage Workflows**: Define infinite approval levels (e.g., HOD → COO → CEO).
- **Batch Processing**: Groups records into smart batches to prevent email fatigue.
- **Token-Based Approvals**: Secure, one-click approval links directly from the inbox.
- **Plug & Play Modules**: Seamlessly attach workflows to any Eloquent model (Requisitions, Invoices, etc.).
- **Action-Based Architecture**: Clean, testable logic separated from your controllers.
- **Agnostic Design**: Works with your existing Roles/Permissions system.

---

## Requirements

- PHP 8.1+
- Laravel 10+
- Composer

---
## 🚀 Installation

Install the package via composer:

```bash
composer require apurba-labs/laravel-approval-engine
```

---

## Basic Workflow Example

```
Requisition Approved
        ↓
Batch created
        ↓
Email sent to HOSD
        ↓
HOSD Approves
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

php artisan make:workflow-module PurchaseOrder
```
This will generate a module structure ready for workflow integration.

---
# CLI Tools
## Show Workflow Structure
```
php artisan approval:workflow requisition
```
Example Output
Workflow: requisition

Stage 1 → HOS
Stage 2 → COO

## Check Pending Batches
```
php artisan approval:status
```
Example Output:

Module        Stage      Status
--------------------------------
requisition   1          pending
---
# Testing
Run test using:
```
php artisan test
```
or
```
./vendor/bin/phpunit
```
Test Include:
The engine is backed by a comprehensive test suite covering 12 Feature Tests and 21 Assertions:
Batch Creation: Validates unique token generation and WorkflowBatch persistence.
Workflow Services: Tests the StageResolver and BatchWindowResolver for multi-stage transitions.
Module Discovery: Confirms the engine's ability to auto-load modules from app/Workflow/Modules.
Command Execution: Verifies the approval:send-batch logic against Daily, Weekly, and Monthly schedules.
Event-Driven Logic: Asserts that BatchApproved events correctly trigger the BatchApprovalMail.
Audit Integrity: Ensures BatchItems are accurately mapped to records for a full audit trail.
Database Compatibility: Fully tested on both MySQL and SQLite (In-Memory).

---
## 🚀 Example Demo Project

A working demo is available in the [example/laravel-demo](../../tree/main/example/laravel-demo) folder.

This project demonstrates:
* **Requisition Approval Workflow**: A real-world implementation of the engine.
* **Batch Processing**: See how 10+ records are grouped into a single approval task.
* **Approval Links**: Test the token-based GET requests in a live Laravel 12 environment.

To run the demo:
1. Navigate to `example/laravel-demo`
2. Run `composer install`
3. Run `php artisan approval:demo` to generate test data and an approval link.
---

## License

MIT License
