Version 1.4.bet all refactor development step
IMPORTANT ARCHITECTURE NOTE for IAM integration
workflow_rules => conditional routing / who gets assigned
workflow_stages => ordered stage definitions / workflow structure

WorkflowRule = dynamic approver selection logic
WorkflowStage = sequential workflow progression

Rules determine approver dynamically
Stages define sequence/order only

STEP 1: Update migration file for redesign workflow stages for flexible recipient assignment
redesign workflow stages for flexible recipient assignment
2026_04_09_000001_add_flexible_assignment_to_workflow_stages_table
assign_type, assign_value, scope_field, name, description
STEP 2:
Update WorkflowRuleResolver to attach resolved assignment metadata
STEP 3:
Update WorkflowRecipientResolver to prefer resolved assignment metadata

STEP 5:
    Test:
        Static stage assignment
        Rule override assignment
        Permission assignment
        Legacy role assignment

STEP 6: 
DEPRECATE role Column (Later) after all test
we need to create XXXXXXXX_remove_role_column_from_workflow_stages.php

Need alignment with IAM: old role-based batching + newer IAM/assignment model also need fixed some mismatched relationships
Stable Architecture after IAM integration look like:
StartWorkflowAction
  -> create WorkflowInstance
  -> dispatch WorkflowStarted

HandleWorkflowStarted
  -> resolve stage
  -> resolve recipient
  -> create WorkflowApproval
  -> create WorkflowNotification (with assignment snapshot)

SendWorkflowBatchCommand
  -> group pending notifications by module + recipient_signature + window
  -> create batch
  -> link notifications.batch_id
  -> send batch email

ApproveBatchAction
  -> validate approver against batch recipient context
  -> fetch linked notifications
  -> mark approvals for each workflow instance
  -> advance each workflow instance

  So what is my Next Refactor Plan today befor UI (04-10-2026):

    Phase A — Normalize Assignment Snapshot
        Add notification fields
        Populate them on notification creation
        Generate recipient signature consistently

    Phase B — Batch Alignment
        Refactor batch grouping to use recipient_signature
        Link notifications to batch on creation

    Phase C — Batch Approval Correctness
        Refactor batch approval to process linked notifications/items
        Remove assumptions of single workflow per batch

    Phase C — Batch Approval Correctness
        Refactor batch approval to process linked notifications/items
        Remove assumptions of single workflow per batch


role                = legacy / display / backward compatibility
recipient           = intended logical recipient target
resolved_recipient  = actual resolved actor/entity
recipient_signature = deterministic grouping/auth key

v1.4 Beta we will keep role+recipient recipient and after full test we will remove this like ver


** v2 Cleanup Candidate**
- workflow_stages table 
Role column
$table->dropColumn('role');

- workflow_notifications table
Potentially remove/deprecate: role and recipient (if redundant)
after code fully uses:
assign_type
assign_value
recipient_signature
resolved_recipient
