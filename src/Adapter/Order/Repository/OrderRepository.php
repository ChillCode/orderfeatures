<?php

// phpcs:disable Generic.Files.LineLength.TooLong

/**
 * @author    ChillCode <https://github.com/chillcode>
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\OrderFeatures\Adapter\Order\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Order\ValueObject\OrderId;
use PrestaShop\PrestaShop\Core\Repository\AbstractMultiShopObjectModelRepository;

/**
 * Class OrderRepository.
 */
class OrderRepository extends AbstractMultiShopObjectModelRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $dbPrefix;

    /**
     * OrderRepository constructor.
     *
     * @param Connection $connection
     * @param string $dbPrefix
     */
    public function __construct(
        Connection $connection,
        $dbPrefix
    ) {
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;
    }

    /**
     * List id_order references.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws DBALException
     */
    public function listOrderTables()
    {
        $orderTablesReference = "SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME = 'id_order'";

        return $this->connection->fetchAllAssociative($orderTablesReference);
    }

    /**
     * TODO: Delete QuickPay, PayPal and other modules that reference id_order column.
     *
     * Before calling this function, check all references to id_order on third-party modules.
     *
     * Incorrect references to id_order can lead PrestaShop to crash if the referenced id_order is not found.
     *
     * Delete order data.
     *
     * @param int $orderIdValue order ID
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function delete(OrderId $orderId): void
    {
        $this->connection->beginTransaction();

        try {
            $orderIdValue = $orderId->getValue();

            if (!$this->connection->fetchOne('SELECT id_order FROM `' . $this->dbPrefix . 'orders` WHERE id_order = ' . $orderIdValue)) {
                throw new OrderNotFoundException($orderId, sprintf('Order with ID %d not found.', $orderIdValue));
            }

            $this->connection->executeQuery('DELETE `or`,`ord` FROM `' . $this->dbPrefix . 'order_return` AS `or` LEFT JOIN `' . $this->dbPrefix . 'order_return_detail` AS `ord` ON `or`.id_order_return = `ord`.id_order_return WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE `os`,`osd` FROM `' . $this->dbPrefix . 'order_slip`   AS `os` LEFT JOIN `' . $this->dbPrefix . 'order_slip_detail`   AS `osd` ON `os`.id_order_slip = `osd`.id_order_slip WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_detail_tax` WHERE id_order_detail IN (SELECT id_order_detail FROM `' . $this->dbPrefix . 'order_detail` WHERE id_order = ' . $orderIdValue . ')');
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_detail` WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_payment` WHERE order_reference IN (SELECT reference FROM `' . $this->dbPrefix . 'orders` WHERE id_order = ' . $orderIdValue . ')');
            $this->connection->executeQuery('DELETE `c` FROM `' . $this->dbPrefix . 'cart` AS `c` LEFT JOIN `' . $this->dbPrefix . 'orders` AS `o` ON `c`.id_cart = `o`.id_cart WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'orders` WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_carrier` WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_cart_rule` WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_history` WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_invoice_tax` WHERE id_order_invoice IN (SELECT id_order_invoice FROM `' . $this->dbPrefix . 'order_invoice` WHERE id_order = ' . $orderIdValue . ')');
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_invoice` WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_invoice_payment` WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'customer_thread` WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'log` WHERE object_id = ' . $orderIdValue . " AND object_type = 'Order'");
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'message` WHERE id_order = ' . $orderIdValue);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'stock_mvt` WHERE id_order = ' . $orderIdValue);

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw ($e);
        }
    }
}
