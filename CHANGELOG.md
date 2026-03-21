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