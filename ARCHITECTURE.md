# 🏗 Architecture Guide

This document explains the high-level design and data flow of the **Laravel Approval Engine**.

## 1. Design Philosophy
The engine follows the **SOLID** principles and uses an **Action-based architecture** to decouple business logic from Laravel's framework layers (Controllers/Commands).

## 2. Core Components

### 🛰 The Engine (`src/Engine`)
The `WorkflowEngine` is the central traffic controller. 
- **Discovery**: Automatically scans `app/Workflow/Modules` to find active workflows.
- **Resolution**: Maps string identifiers (e.g., 'requisition') to their respective Module classes.

### 🧩 Workflow Modules (`src/Modules`)
Modules act as a "bridge" between the Engine and your Eloquent Models.
- They define which columns to display in emails.
- They provide the base query for fetching approved records.
- They define the relationships (e.g., `user`) that need to be eager-loaded.

### ⚡ Actions (`src/Actions`)
Each business operation is an atomic "Action" class:
- `FetchApprovedRecordsAction`: Handles the complex `whereBetween` and `status` logic.
- `ApproveBatchAction`: Manages the database transaction when a manager clicks "Approve".
- `MoveToNextStageAction`: Logic for transitioning records from Stage 1 to Stage 2.
- `CompleteWorkflowAction`: Finalizes the process and updates source models.

### 📅 Support & Resolvers (`src/Support`)
- `BatchWindowResolver`: Calculates the `start` and `end` datetime for Daily, Weekly, or Monthly batches.
- `StageResolver`: Determines the next Role/Stage in the sequence based on the `workflow_stages` table.

## 3. Data Flow (The "Golden Path")

1. **Detection**: `SendWorkflowBatchCommand` runs via Cron. It asks the `BatchWindowResolver` for the "time window".
2. **Batching**: `BatchProcessor` creates a `WorkflowBatch` and snapshots the record IDs into `WorkflowBatchItems`.
3. **Notification**: `BatchApproved` event is fired → `SendBatchApprovalNotification` listener sends the `BatchApprovalMail`.
4. **Approval**: User clicks the secure token link → `ApprovalController` calls `ApproveBatchAction`.
5. **Progression**: The engine checks if more stages exist. If yes, it triggers `MoveToNextStageAction`. If no, it triggers `CompleteWorkflowAction`.

## 4. Database Schema
- `workflow_settings`: Defines *who* gets notified and *when* (Frequency/Timezone).
- `workflow_stages`: Defines the sequence of approval (Stage 1: HOD, Stage 2: COO).
- `workflow_batches`: The parent record for an approval request (contains the secure Token).
- `workflow_batch_items`: Audit trail linking specific Model IDs to a Batch.
- `workflow_approvals`: Permanent log of *who* approved *which* batch and *when*.

---

## 🛠 Extending the Engine
To add a new notification channel (e.g., Slack):
1. Create a new Listener: `src/Listeners/SendSlackNotification.php`.
2. Register it in the `ServiceProvider` under the `BatchApproved` event.
3. The engine will now send both an Email and a Slack message automatically.
