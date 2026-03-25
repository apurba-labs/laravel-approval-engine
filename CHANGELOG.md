# Changelog

All notable changes to this project will be documented in this file.

## - 2026-03-19

### Added
- **Core Engine**: Dynamic module discovery from `app/Workflow/Modules`.
- **Batching**: Automatic grouping of records based on Role and Frequency.
- **Scheduling**: Support for Daily, Weekly, and Monthly approval windows.
- **Actions**: `ApproveBatchAction`, `MoveToNextStageAction`, and `CompleteWorkflowAction`.
- **Notifications**: Professional HTML Email templates with data tables.
- **Security**: Token-based secure approval links (GET).
- **Audit**: `WorkflowBatchItem` tracking for every record in a batch.
- **Testing**: 12 Feature tests with 21 assertions (MySQL & SQLite supported).

---

🚀 Laravel Approval Engine v1.2

✨ Features:
- Dynamic workflow start with payload support
- Modular architecture with WorkflowModule system
- Configurable StageResolver for dynamic stage injection
- Event-driven lifecycle (WorkflowStarted, Completed, etc.)
- Support for multi-entity ownership (user/admin/customer)
- Clean separation between manual workflows and scheduled batch processing

🧠 Foundation for:
- V1.3: Database-driven workflow_rules engine
- V1.4: Notifications, escalation, retry system

💡 Designed for scalability, SaaS readiness, and enterprise workflows.

--- 

# 🚀 v1.3 – Workflow Instance + Rule Engine + Smart Batching

This release introduces a major architectural upgrade, transforming the system into a production-ready workflow automation engine.

## ✨ Key Features

### 🧠 Workflow Instance Architecture
- Introduced `WorkflowInstance` as the core lifecycle entity
- Tracks workflow state, stage, and payload

### ⚙️ Rule Engine
- Dynamic role resolution using `workflow_rules`
- Example:
  - `total_amount > 5000 → COO`

### 🔔 Notification System
- Instant notifications (real-time)
- Batch notifications (daily/weekly/monthly)

### 📦 Smart Batching (Core Feature)
- Groups approvals by role + time window
- Prevents notification overload
- Enterprise-ready batching strategy

### 🔌 Module-Based Design
- Fully extensible workflow modules
- Polymorphic recipient resolution

### 🧪 Testing
- Full lifecycle coverage
- Batch processing validation
- Notification delivery tests

---

## 🏗️ Architecture

WorkflowInstance → WorkflowLog → WorkflowNotification → BatchProcessor → NotificationService

---

## 🔥 What makes this unique?

Unlike traditional approval systems, this engine supports:
- Rule-driven workflows
- Smart batching (rare in OSS)
- Modular design
- SaaS-ready architecture

---

## 🚀 Next (v1.4)

- Workflow rules UI
- Advanced recipient resolver
- Filament plugin
- SaaS multi-tenancy