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