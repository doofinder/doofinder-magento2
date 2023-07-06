<?php
declare(strict_types=1);


namespace Doofinder\Feed\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Indexation extends AbstractHelper
{

    public const DOOFINDER_INDEX_PROCESS_STATUS_STARTED = 'STARTED';

    public const DOOFINDER_INDEX_PROCESS_STATUS_SUCCESS = 'SUCCESS';

    public const DOOFINDER_INDEX_PROCESS_STATUS_FAILURE = 'FAILURE';

    public const DOOFINDER_GRID_SEVERITY_MINOR = 'minor';

    public const DOOFINDER_GRID_SEVERITY_NOTICE = 'notice';

    public const DOOFINDER_GRID_SEVERITY_MAJOR = 'major';

    public const DOOFINDER_GRID_SEVERITY_CRITICAL = 'critical';

    /**
     * Sanitize and prevent undefined index errors
     *
     * @param array $processTaskStatus
     * @return array
     */
    public function sanitizeProcessTaskStatus(array $processTaskStatus): array
    {
        $status = $processTaskStatus['status'] ?? 'Unknown';
        return [
            'status'        => $status,
            'result'        => $processTaskStatus['result'] ?? '',
            'finished_at'   => $processTaskStatus['finished_at'] ?? '',
            'error'         => $processTaskStatus['error'] ?? false,
            'error_message' => isset($processTaskStatus['error_message']) ? $processTaskStatus['error_message'] : '',
            'severity'      => $this->getSeverity($status)
        ];
    }

    /**
     * Get admin grid severity class by status
     *
     * @param string $status
     * @return string
     */
    public function getSeverity(string $status): string
    {
        $severity = [
            self::DOOFINDER_INDEX_PROCESS_STATUS_STARTED => self::DOOFINDER_GRID_SEVERITY_MINOR,
            self::DOOFINDER_INDEX_PROCESS_STATUS_SUCCESS => self::DOOFINDER_GRID_SEVERITY_NOTICE,
            self::DOOFINDER_INDEX_PROCESS_STATUS_FAILURE => self::DOOFINDER_GRID_SEVERITY_MAJOR,
        ];

        return $severity[$status];
    }
}
