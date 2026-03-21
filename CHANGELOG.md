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
