<?php

// ensure we load our base file (PHPStorm Bug when using remote interpreter )
require_once('BaseTest.php');

use Scottlaurent\Accounting\Services\Accounting as AccountingService;
use Scottlaurent\Accounting\Models\JournalTransaction;
use \Scottlaurent\Accounting\Exceptions\{InvalidJournalMethod, InvalidJournalEntryValue, DebitsAndCreditsDoNotEqual};
use PHPUnit\Framework\TestCase; // Assuming PHPUnit

class DoubleEntryTest extends BaseTest
{
    // Test names are more descriptive
    public function test_only_debit_or_credit_methods_allowed()
    {
        $this->expectException(InvalidJournalMethod::class);
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'banana', 100);
    }

    public function test_transaction_value_cannot_be_zero()
    {
        $this->expectException(InvalidJournalEntryValue::class);
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', 0);
    }

    public function test_transaction_value_cannot_be_negative()
    {
        $this->expectException(InvalidJournalEntryValue::class);
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', -100);
    }

    public function test_debits_and_credits_must_balance_within_transaction_group()
    {
        $this->expectException(DebitsAndCreditsDoNotEqual::class);
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', 99.01);
        $transaction_group->addDollarTransaction($this->company_ar_journal, 'credit', 99.00);
        $transaction_group->commit();
    }

    public function test_post_transaction_journal_balances_match_expectations()
    {
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', 100);
        $transaction_group->addDollarTransaction($this->company_ar_journal, 'credit', 100);
        $transaction_group->commit();

        $delta = 0.01; // Adjust delta for floating-point precision
        $this->assertEqualsWithDelta(
            $this->company_cash_journal->getCurrentBalanceInDollars(),
            (-1) * $this->company_ar_journal->getCurrentBalanceInDollars(),
            $delta
        );
    }

    // Consider using mocks for journals and ledgers in some tests
    public function test_transaction_groups_contain_expected_number_of_transactions()
    {
        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', 100);
        $transaction_group->addDollarTransaction($this->company_ar_journal, 'credit', 100);
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', 75);
        $transaction_group->addDollarTransaction($this->company_ar_journal, 'credit', 75);
        $transaction_group_uuid = $transaction_group->commit();

        $this->assertEquals(
            JournalTransaction::where('transaction_group', $transaction_group_uuid)->count(),
            4
        );
    }

   public function test_post_transaction_ledger_balances_match_expectations()
{
    $dollar_value = mt_rand(1000000, 9999999) * 1.987654321;

    $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
    $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', $dollar_value);
    $transaction_group->addDollarTransaction($this->company_income_journal, 'credit', $dollar_value);
    $transaction_group->commit();

    // Option 1: Round dollar_value for comparison
    $expected_balance = round($dollar_value, 2); // Adjust decimal places as needed
    $this->assertEquals($this->company_assets_ledger->getCurrentBalanceInDollars($this->currency), $expected_balance);
    $this->assertEquals($this->company_income_ledger->getCurrentBalanceInDollars($this->currency), $expected_balance);

    // Option 2: Use assertEqualsWithDelta (if available)
    // $delta = 0.01; // Adjust delta based on acceptable tolerance
    // $this->assertEqualsWithDelta(
    //     $this->company_assets_ledger->getCurrentBalanceInDollars($this->currency),
    //     $this->company_income_ledger->getCurrentBalanceInDollars($this->currency),
    //     $delta
    // );
}

    public function test_post_transaction_ledger_balances_match_after_complex_activity()
{
    $num_iterations = 100; // Adjust as needed

    for ($x = 1; $x <= $num_iterations; $x++) {
        $dollar_value_a = mt_rand(1, 99999999) * 2.25;
        $dollar_value_b = mt_rand(1, 99999999) * 3.50;

        $transaction_group = AccountingService::newDoubleEntryTransactionGroup();
        $transaction_group->addDollarTransaction($this->company_cash_journal, 'debit', $dollar_value_a);
        $transaction_group->addDollarTransaction($this->company_ar_journal, 'debit', $dollar_value_b);
        $transaction_group->addDollarTransaction($this->company_income_journal, 'credit', $dollar_value_a + $dollar_value_b);
        $transaction_group->commit();
    }

    // Option 1: Round expected balance for comparison
    $total_credits = array_sum([$dollar_value_a, $dollar_value_b]) * $num_iterations;
    $expected_balance = round($total_credits, 2); // Adjust decimal places as needed
    $this->assertEquals($this->company_assets_ledger->getCurrentBalanceInDollars($this->currency), $expected_balance);

    // Option 2: Use assertEqualsWithDelta (if available)
    // $delta = 0.01 * $num_iterations; // Adjust delta based on acceptable tolerance
    // $this->assertEqualsWithDelta(
    //     $this->company_assets_ledger->getCurrentBalanceInDollars($this->currency),
    //     $this->company_income_ledger->getCurrentBalanceInDollars($this->currency),
    //     $delta
    // );
}
}
