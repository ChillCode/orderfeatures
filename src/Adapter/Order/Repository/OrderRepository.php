<?php

// phpcs:disable Generic.Files.LineLength.TooLong

/**
 * @author    ChillCode <https://github.com/chillcode>
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\OrderFeatures\Adapter\Order\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Exception;

/**
 * Class OrderRepository.
 */
class OrderRepository
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
     * Delete order data.
     *
     * @param OrderId $orderId Order ID.
     * @throws ConnectionException
     * @throws Exception
     */
    public function deleteOrder(int $orderId): void
    {
        $this->connection->beginTransaction();

        try {
            $this->connection->executeQuery('DELETE `or`,`ord` FROM `' . $this->dbPrefix . 'order_return` AS `or` LEFT JOIN `' . $this->dbPrefix . 'order_return_detail` AS `ord` ON or.id_order_return = ord.id_order_return WHERE id_order = ' . $orderId);
            $this->connection->executeQuery('DELETE `os`,`osd` FROM `' . $this->dbPrefix . 'order_slip`   AS `os` LEFT JOIN `' . $this->dbPrefix . 'order_slip_detail`   AS `osd` ON os.id_order_slip = osd.id_order_slip WHERE id_order = ' . $orderId);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_history`     WHERE id_order = ' . $orderId);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_detail_tax`  WHERE id_order_detail IN (SELECT id_order_detail FROM ' . $this->dbPrefix . 'order_detail WHERE id_order = ' . $orderId . ')');
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_detail`      WHERE id_order = ' . $orderId);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_payment`     WHERE order_reference IN (SELECT reference FROM ' . $this->dbPrefix . 'orders WHERE id_order = ' . $orderId . ')');
            $this->connection->executeQuery('DELETE `cp`,`o`   FROM `' . $this->dbPrefix . 'cart_product` AS `cp` LEFT JOIN `' . $this->dbPrefix . 'orders` AS `o` ON cp.id_cart = o.id_cart WHERE id_order = ' . $orderId);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_carrier`         WHERE id_order = ' . $orderId);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_cart_rule`       WHERE id_order = ' . $orderId);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_invoice_tax`     WHERE id_order_invoice IN (SELECT id_order_invoice FROM ' . $this->dbPrefix . 'order_invoice WHERE id_order = ' . $orderId . ')');
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_invoice`         WHERE id_order = ' . $orderId);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'order_invoice_payment` WHERE id_order = ' . $orderId);
            $this->connection->executeQuery('DELETE FROM `' . $this->dbPrefix . 'log`                   WHERE object_id = ' . $orderId . " AND object_type = 'Order'");
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw ($e);
        }

        $this->connection->commit();
    }
}
