<?php
namespace Aheadworks\Sarp2\Engine\Payment\Engine;

/**
 * Interface LoggerInterface
 * @package Aheadworks\Sarp2\Engine\Payment\Engine
 */
interface LoggerInterface
{
    /**#@+
     * Source action types
     */
    const SOURCE_ACTION_SCHEDULE = 'schedule';
    const SOURCE_ACTION_PROCESSING = 'processing';
    /**#@-*/

    /**#@+
     * Log entry types
     */
    const ENTRY_PROFILE_SET_STATUS = 'profile_set_status';
    const ENTRY_PAYMENTS_SCHEDULED = 'payments_scheduled';
    const ENTRY_PAYMENTS_SCHEDULE_FAILED = 'payments_schedule_failed';
    const ENTRY_OUTSTANDING_PAYMENTS_DETECTED = 'outstanding_payments_detected';
    const ENTRY_PAYMENTS_STATUS_AND_TYPE_MASS_CHANGE = 'payments_status_and_type_mass_change';
    const ENTRY_PAYMENTS_TYPE_MASS_CHANGE = 'payments_type_mass_change';
    const ENTRY_PAYMENTS_STATUS_CHANGE = 'payments_status_change';
    const ENTRY_PAYMENT_STATUS_CHANGE = 'payment_status_change';
    const ENTRY_PAYMENT_UPDATE = 'payment_update';
    const ENTRY_PAYMENT_UPDATE_FAILED = 'payment_update_failed';
    const ENTRY_BUNDLED_PAYMENTS_DETECTED = 'bundled_payments_detected';
    const ENTRY_PAYMENT_ADDED_TO_CLEANER = 'payment_added_to_cleaner';
    const ENTRY_PAYMENT_REMOVED_FROM_CLEANER = 'payment_removed_from_cleaner';
    const ENTRY_CLEANUP = 'cleanup';
    const ENTRY_CLEANUP_FAILED = 'cleanup_failed';
    const ENTRY_ACTUAL_PAYMENT_CREATED = 'actual_payment_created';
    const ENTRY_ACTUAL_PAYMENT_CREATION_FAILED = 'actual_payment_creation_failed';
    const ENTRY_PAYMENT_REATTEMPT_CREATED = 'payment_reattempt_created';
    const ENTRY_PAYMENT_REATTEMPT_CREATION_FAILED = 'payment_reattempt_creation_failed';
    const ENTRY_PAYMENT_REATTEMPT_RESCHEDULED = 'payment_reattempt_rescheduled';
    const ENTRY_PAYMENT_SUCCESSFUL = 'payment_successful';
    const ENTRY_PAYMENT_FAILED = 'payment_failed';
    const ENTRY_INCREMENT_STATE = 'increment_state';
    const ENTRY_UNEXPECTED_EXCEPTION = 'unexpected_exception';
    /**#@-*/

    /**
     * Add log record
     *
     * @param string $str
     * @return void
     */
    public function log($str);

    /**
     * Trace schedule source action
     *
     * @param string $entryType
     * @param array $data
     * @param array $addData
     * @return void
     */
    public function traceSchedule($entryType, $data = [], $addData = []);

    /**
     * Trace processing source action
     *
     * @param string $entryType
     * @param array $data
     * @param array $addData
     * @return void
     */
    public function traceProcessing($entryType, $data = [], $addData = []);
}
