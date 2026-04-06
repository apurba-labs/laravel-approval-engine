<?php

namespace ApurbaLabs\ApprovalEngine\Tests\Feature\V14;

use ApurbaLabs\ApprovalEngine\Tests\TestCase;

use ApurbaLabs\ApprovalEngine\Models\WorkflowRule;
use ApurbaLabs\ApprovalEngine\Models\WorkflowInstance;
use ApurbaLabs\ApprovalEngine\Engine\Resolvers\WorkflowRuleResolver;

class WorkflowRuleResolverTest extends TestCase
{

    /** @test */
    public function it_resolves_rule_with_assign_type_role()
    {
        WorkflowRule::create([
            'module' => 'expense',
            'field' => 'amount',
            'operator' => '>',
            'value' => 1000,
            'role' => 'finance_manager',
            'assign_type' => 'role',
            'assign_value' => 'finance_manager',
            'priority' => 1,
            'is_active' => true,
        ]);

        $workflow = WorkflowInstance::create([
            'module' => 'expense',
            'current_stage_order'=>1,
            'payload' => ['amount' => 5000],
            'status' => 'pending',
        ]);

        $resolver = app(WorkflowRuleResolver::class);

        $rule = $resolver->findMatchingRule($workflow);

        $this->assertNotNull($rule);
        $this->assertEquals('finance_manager', $rule->assign_value);
    }

    /** @test */
    public function it_resolves_rule_with_assign_type_user()
    {
        WorkflowRule::create([
            'module' => 'expense',
            'field' => 'amount',
            'operator' => '>',
            'value' => 1000,
            'role' => 'ignored',
            'assign_type' => 'user',
            'assign_value' => 1,
            'priority' => 1,
            'is_active' => true,
        ]);

        $workflow = WorkflowInstance::create([
            'module' => 'expense',
            'current_stage_order'=>1,
            'payload' => ['amount' => 5000],
            'status' => 'pending',
        ]);

        $resolver = app(WorkflowRuleResolver::class);

        $rule = $resolver->findMatchingRule($workflow);

        $this->assertEquals(1, $rule->assign_value);
    }
}