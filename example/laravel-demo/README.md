
# 🚀 Laravel Approval Engine v1.3

A **modular, rule-based workflow approval engine** with **smart notification batching** — built for real-world SaaS applications.

---

## ✨ Why this exists

Most approval systems are:

❌ Hardcoded
❌ Not scalable
❌ Spam users with notifications

👉 This engine solves all of that.

---

## 🔥 Key Features

### ⚙️ Workflow Engine

* Module-based architecture
* Dynamic workflow execution
* Extensible design

### 🧠 Rule-Based Routing

* Define rules like:

  ```
  IF total_amount > 5000 → COO
  ```
* Priority-based rule evaluation
* Fully configurable

### 📦 Smart Notification Batching (Core Feature)

* Group approvals by role + time window
* Daily / Weekly / Monthly batching
* Prevent notification overload

### 🔔 Notification System

* Instant notifications
* Batch notifications
* Pluggable delivery system (Email / future Slack)

### 🧱 Workflow Instance Lifecycle

```
WorkflowInstance → WorkflowLog → WorkflowNotification → BatchProcessor
```

### 🧩 Module-Based Design

* Supports multiple business modules:

  * Requisition
  * Purchase
  * Any custom module

---

## 🏗️ Architecture

```
WorkflowEngine
   ↓
WorkflowInstance
   ↓
WorkflowLog (tracking)
   ↓
WorkflowNotification
   ↓
BatchProcessor / NotificationService
```

---

## ⚡ Quick Start

### 1️⃣ Install

```bash
git clone https://github.com/apurba-labs/laravel-approval-engine
cd approval-engine/example/laravel-demo

composer install
cp .env.example .env
php artisan key:generate

or
composer require apurba-labs/laravel-approval-engine
```

---

### 2️⃣ Publish & Migrate

```bash
php artisan vendor:publish
php artisan migrate
php artisan db:seed
```

---

### 3️⃣ Setup Demo Data (Recommended)

```bash
php artisan approval:setup-demo
```

👉 This will:

* Create default workflow stages
* Add sample rules
* Make the system ready instantly

---

### 4️⃣ Start a Workflow

```php
use ApurbaLabs\ApprovalEngine\ApprovalEngine;

ApprovalEngine::start('purchase', [
    'total_amount' => 7500,
    'user_id' => 1
]);
```

---

## 🧠 How It Works

### 1. Stage (Required)

Every module must have at least one stage:

```text
purchase → HOD
```

---

### 2. Rule (Optional)

Rules override stage behavior:

```text
IF total_amount > 5000 → COO
```

---

### 3. Execution Flow

```
Start → Resolve Rule → Assign Role → Notify → Batch (if needed)
```

---

## 📊 Example Use Case

```text
Purchase Request:

Amount = 3000 → HOD approval  
Amount = 7500 → COO approval (via rule)
```

---

## 🧪 Testing

Run:

```bash
php artisan test
```

Covers:

* Workflow lifecycle
* Rule resolution
* Notification system
* Batch processing

---

## 🎯 What Makes This Unique?

✔ Rule-driven workflows (not hardcoded)
✔ Smart batching (rare in open-source)
✔ Modular architecture
✔ SaaS-ready foundation

---

## 🖥️ Demo UI (Next.js)

This engine comes with a modern dashboard:

* Dashboard (KPI + workflows)
* Workflow table (approve/reject)
* Metrics (charts)
* Batch view

---

## 🚀 Roadmap

### ✅ v1.3 (Current)

* Workflow instance architecture
* Rule engine
* Notification system
* Smart batching
* Demo dashboard

### 🔜 v1.4 (Next)

* Dynamic form builder
* Rule builder UI
* Advanced recipient resolver
* Filament admin plugin
* SaaS multi-tenancy

---

## 🤝 Contributing

Contributions are welcome!

* Fork the repo
* Create feature branch
* Submit PR

---

## 📢 Author

Built with ❤️ by **Apurba Singh**

---

## ⭐ Support

If you find this useful:

👉 Star the repo
👉 Share with community
👉 Give feedback

---

## 💬 Final Thought

> “Instead of sending 100 notifications…
> send 1 smart batch.”

---

🔥 This is just the beginning of a powerful workflow platform.
