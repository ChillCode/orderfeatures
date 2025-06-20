<?php

declare(strict_types=1);

namespace PrestaShop\Module\OrderFeatures\Core\Domain\Order\Exception;

use Exception;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;

/**
 * Thrown on failure to delete all selected orders without errors
 */
class BulkDeleteOrderException extends OrderException
{
    /**
     * @var int[]
     */
    private $orderIds;

    /**
     * @param int[] $orderIds
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct(array $orderIds, $message = '', $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->orderIds = $orderIds;
    }

    /**
     * @return int[]
     */
    public function getOrderIds(): array
    {
        return $this->orderIds;
    }
}
